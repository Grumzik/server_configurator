<?php

namespace Drupal\tiscom_news_auto\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Prepares rewritten Russian news content via OpenAI API.
 */
class OpenAiClient {

  public function __construct(
    protected ClientInterface $httpClient,
    protected ConfigFactoryInterface $configFactory,
    protected LoggerInterface $logger,
  ) {}

  /**
   * Rewrites a source article for tiscom.ru.
   */
  public function rewrite(array $article): array {
    $config = $this->configFactory->get('tiscom_news_auto.settings');
    $apiKey = (string) $config->get('openai_api_key');
    $model = (string) ($config->get('openai_model') ?: 'gpt-4.1-mini');

    if ($apiKey === '') {
      return $this->fallback($article);
    }

    $prompt = $this->buildPrompt($article);

    $schema = [
      'type' => 'object',
      'additionalProperties' => FALSE,
      'properties' => [
        'title' => ['type' => 'string'],
        'anons' => ['type' => 'string'],
        'body' => ['type' => 'string'],
        'image_alt' => ['type' => 'string'],
        'url_alias' => ['type' => 'string'],
        'metatag' => ['type' => 'string'],
      ],
      'required' => ['title', 'anons', 'body', 'image_alt', 'url_alias', 'metatag'],
    ];

    try {
      $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/responses', [
        'timeout' => 90,
        'headers' => [
          'Authorization' => 'Bearer ' . $apiKey,
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'model' => $model,
          'input' => $prompt,
          'text' => [
            'format' => [
              'type' => 'json_schema',
              'name' => 'tiscom_news_article',
              'schema' => $schema,
              'strict' => TRUE,
            ],
          ],
        ],
      ]);
      $payload = json_decode((string) $response->getBody(), TRUE);
      $text = $this->extractText($payload);
      $data = json_decode($text, TRUE);
      if (is_array($data) && !empty($data['title']) && !empty($data['body'])) {
        $data['body'] .= '<p><strong>Источник:</strong> <a href="' . htmlspecialchars($article['url'], ENT_QUOTES) . '">TechSpot</a>.</p>';
        return $data;
      }
    }
    catch (\Throwable $e) {
      $this->logger->error('OpenAI rewrite failed for @url: @message', ['@url' => $article['url'] ?? '', '@message' => $e->getMessage()]);
    }

    return $this->fallback($article);
  }

  protected function buildPrompt(array $article): string {
    return <<<PROMPT
Ты готовишь техническую новость для сайта российского производителя серверного оборудования tiscom.ru.

Задача:
- перепиши исходную новость на русском языке;
- не делай дословный перевод;
- стиль: техническая новость для B2B-аудитории, без рекламного преувеличения;
- аудитория: заказчики серверов, инженеры, ИТ-руководители;
- избегай англицизмов, если есть нормальный русский термин;
- сделай акцент на связи новости с серверами, инфраструктурой, ЦОД, процессорами, памятью, PCIe, накопителями, AI-нагрузками;
- основной текст верни в HTML: 4–7 абзацев <p>...</p>;
- не вставляй вымышленные факты;
- не упоминай, что текст подготовлен ИИ;
- ссылку на источник не добавляй, она будет добавлена программно.

Верни строго JSON с полями:
{
  "title": "заголовок",
  "anons": "короткий анонс 1–2 предложения",
  "body": "HTML основной текст",
  "image_alt": "alt для изображения",
  "url_alias": "/news/...",
  "metatag": "короткое meta description"
}

Исходная статья:
Источник: TechSpot
URL: {$article['url']}
Заголовок: {$article['title']}
Описание: {$article['description']}
Дата: {$article['published']}
Текст:
{$article['text']}
PROMPT;
  }

  protected function extractText(array $payload): string {
    if (!empty($payload['output_text'])) {
      return (string) $payload['output_text'];
    }
    if (!empty($payload['output']) && is_array($payload['output'])) {
      foreach ($payload['output'] as $output) {
        if (!empty($output['content']) && is_array($output['content'])) {
          foreach ($output['content'] as $content) {
            if (isset($content['text'])) {
              return (string) $content['text'];
            }
          }
        }
      }
    }
    return '';
  }

  protected function fallback(array $article): array {
    $safeTitle = trim($article['title'] ?? 'Новая hardware-новость');
    $anons = trim($article['description'] ?? 'Подготовлен черновик новости на основе материала TechSpot.');
    $body = '<p>' . htmlspecialchars($anons, ENT_QUOTES) . '</p>';
    if (!empty($article['text'])) {
      foreach (array_slice(preg_split('/\n\n+/', $article['text']), 0, 4) as $paragraph) {
        $body .= '<p>' . htmlspecialchars($paragraph, ENT_QUOTES) . '</p>';
      }
    }
    $body .= '<p><strong>Источник:</strong> <a href="' . htmlspecialchars($article['url'], ENT_QUOTES) . '">TechSpot</a>.</p>';

    return [
      'title' => $safeTitle,
      'anons' => $anons,
      'body' => $body,
      'image_alt' => $safeTitle,
      'url_alias' => '/news/' . preg_replace('/[^a-z0-9-]+/', '-', strtolower(parse_url($article['url'], PHP_URL_PATH) ?: 'hardware-news')),
      'metatag' => mb_substr($anons, 0, 160),
    ];
  }

}
