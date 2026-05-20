<?php

namespace Drupal\tiscom_news_auto\Service;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Fetches and extracts hardware news from TechSpot.
 */
class TechspotSource {

  public const SOURCE_ID = 'techspot_hardware';

  public function __construct(
    protected ClientInterface $httpClient,
    protected LoggerInterface $logger,
  ) {}

  /**
   * Fetches candidate articles.
   *
   * @return array<int, array<string, mixed>>
   *   Article items.
   */
  public function fetch(string $categoryUrl, int $limit, int $daysBack): array {
    $html = $this->get($categoryUrl);
    if ($html === '') {
      return [];
    }

    $links = $this->extractArticleLinks($html, $categoryUrl);
    $candidates = [];
    $max = max($limit * 4, 12);

    foreach (array_slice($links, 0, $max) as $url => $title) {
      $article = $this->fetchArticle($url, $title);
      if (!$article) {
        continue;
      }
      if (!empty($article['published_timestamp']) && $article['published_timestamp'] < strtotime('-' . $daysBack . ' days')) {
        continue;
      }
      $article['score'] = $this->score($article);
      $candidates[] = $article;
    }

    usort($candidates, static fn(array $a, array $b): int => ($b['score'] <=> $a['score']));

    return array_slice($candidates, 0, $limit);
  }

  /**
   * Fetches a URL.
   */
  protected function get(string $url): string {
    try {
      $response = $this->httpClient->request('GET', $url, [
        'timeout' => 20,
        'headers' => [
          'User-Agent' => 'TiscomNewsAuto/1.0 (+https://tiscom.ru)',
          'Accept' => 'text/html,application/xhtml+xml',
        ],
      ]);
      return (string) $response->getBody();
    }
    catch (\Throwable $e) {
      $this->logger->warning('Unable to fetch @url: @message', ['@url' => $url, '@message' => $e->getMessage()]);
      return '';
    }
  }

  /**
   * Extract article links from category page.
   */
  protected function extractArticleLinks(string $html, string $baseUrl): array {
    $links = [];
    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new \DOMXPath($dom);

    foreach ($xpath->query('//a[@href]') as $a) {
      /** @var \DOMElement $a */
      $href = trim($a->getAttribute('href'));
      if (!preg_match('#/news/\d+-[^"\s]+\.html#', $href)) {
        continue;
      }
      $url = $this->absoluteUrl($href, $baseUrl);
      $title = trim(preg_replace('/\s+/', ' ', $a->textContent));
      if ($title === '' || mb_strlen($title) < 20) {
        $title = $url;
      }
      $links[$url] = $title;
    }

    return $links;
  }

  /**
   * Fetch and parse an article page.
   */
  protected function fetchArticle(string $url, string $fallbackTitle): ?array {
    $html = $this->get($url);
    if ($html === '') {
      return NULL;
    }

    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new \DOMXPath($dom);

    $title = $this->firstText($xpath, '//h1') ?: $this->meta($xpath, 'og:title') ?: $fallbackTitle;
    $description = $this->meta($xpath, 'description') ?: $this->meta($xpath, 'og:description');
    $image = $this->meta($xpath, 'og:image');
    $published = $this->meta($xpath, 'article:published_time') ?: $this->firstText($xpath, '//*[contains(@class, "date") or contains(@class, "byline")]');
    $publishedTimestamp = $published ? strtotime($published) : NULL;

    $paragraphs = [];
    foreach ($xpath->query('//article//p | //div[contains(@class, "articleBody")]//p | //div[contains(@class, "content")]//p') as $p) {
      $text = trim(preg_replace('/\s+/', ' ', $p->textContent));
      if ($text !== '' && mb_strlen($text) > 40) {
        $paragraphs[] = $text;
      }
    }
    if (!$paragraphs) {
      foreach ($xpath->query('//p') as $p) {
        $text = trim(preg_replace('/\s+/', ' ', $p->textContent));
        if ($text !== '' && mb_strlen($text) > 80) {
          $paragraphs[] = $text;
        }
      }
    }

    return [
      'source_id' => self::SOURCE_ID,
      'url' => $url,
      'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5),
      'description' => html_entity_decode((string) $description, ENT_QUOTES | ENT_HTML5),
      'image_url' => $image,
      'published' => $published,
      'published_timestamp' => $publishedTimestamp,
      'text' => implode("\n\n", array_slice($paragraphs, 0, 12)),
    ];
  }

  protected function firstText(\DOMXPath $xpath, string $query): string {
    $nodes = $xpath->query($query);
    if ($nodes && $nodes->length) {
      return trim(preg_replace('/\s+/', ' ', $nodes->item(0)->textContent));
    }
    return '';
  }

  protected function meta(\DOMXPath $xpath, string $name): string {
    $queries = [
      '//meta[@name="' . $name . '"]/@content',
      '//meta[@property="' . $name . '"]/@content',
    ];
    foreach ($queries as $query) {
      $nodes = $xpath->query($query);
      if ($nodes && $nodes->length) {
        return trim($nodes->item(0)->nodeValue);
      }
    }
    return '';
  }

  protected function absoluteUrl(string $href, string $baseUrl): string {
    if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
      return $href;
    }
    $parts = parse_url($baseUrl);
    $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'www.techspot.com');
    if (str_starts_with($href, '/')) {
      return $origin . $href;
    }
    return rtrim($origin, '/') . '/' . ltrim($href, '/');
  }

  /**
   * Simple B2B/server relevance score.
   */
  protected function score(array $article): int {
    $haystack = mb_strtolower(($article['title'] ?? '') . ' ' . ($article['description'] ?? '') . ' ' . ($article['text'] ?? ''));
    $weights = [
      'server' => 12,
      'servers' => 12,
      'data center' => 12,
      'datacenter' => 12,
      'epyc' => 12,
      'xeon' => 12,
      'amd' => 8,
      'intel' => 8,
      'nvidia' => 8,
      'arm' => 7,
      'cpu' => 7,
      'processor' => 7,
      'pcie' => 7,
      'nvme' => 6,
      'memory' => 6,
      'ddr5' => 6,
      'motherboard' => 5,
      'ai' => 5,
      'gpu' => 5,
      'storage' => 5,
    ];
    $score = 0;
    foreach ($weights as $word => $weight) {
      if (str_contains($haystack, $word)) {
        $score += $weight;
      }
    }
    return $score;
  }

}
