<?php

namespace Drupal\tiscom_news_auto\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates Drupal news drafts and stores duplicate markers.
 */
class NewsCreator {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected Connection $database,
    protected ClientInterface $httpClient,
    protected FileSystemInterface $fileSystem,
    protected ConfigFactoryInterface $configFactory,
    protected LoggerInterface $logger,
  ) {}

  /**
   * Checks if a source URL was already imported.
   */
  public function isDuplicate(string $sourceUrl): bool {
    $hash = $this->hash($sourceUrl);
    $exists = $this->database->select('tiscom_news_auto_source_item', 'i')
      ->fields('i', ['id'])
      ->condition('news_source_hash', $hash)
      ->range(0, 1)
      ->execute()
      ->fetchField();

    return (bool) $exists;
  }

  /**
   * Creates a draft node from prepared article data.
   */
  public function createDraft(array $article, array $prepared): ?Node {
    $config = $this->configFactory->get('tiscom_news_auto.settings');
    $contentType = (string) ($config->get('content_type') ?: 'news');
    $anonsField = (string) ($config->get('anons_field') ?: 'field_news_anons');
    $imageField = (string) ($config->get('image_field') ?: 'field_image');

    try {
      $values = [
        'type' => $contentType,
        'title' => $prepared['title'],
        'status' => 0,
        $anonsField => $prepared['anons'],
        'body' => [
          'value' => $prepared['body'],
          'format' => 'full_html',
        ],
      ];

      if (!empty($article['image_url']) && $config->get('import_images')) {
        $file = $this->downloadImage($article['image_url'], $prepared['title']);
        if ($file) {
          $values[$imageField] = [
            'target_id' => $file->id(),
            'alt' => $prepared['image_alt'] ?: $prepared['title'],
            'title' => $prepared['title'],
          ];
        }
      }

      /** @var \Drupal\node\Entity\Node $node */
      $node = Node::create($values);
      $node->save();

      $this->recordSourceItem($article, (int) $node->id(), 'draft');

      return $node;
    }
    catch (\Throwable $e) {
      $this->logger->error('Unable to create news draft for @url: @message', ['@url' => $article['url'] ?? '', '@message' => $e->getMessage()]);
      $this->recordSourceItem($article, NULL, 'failed');
      return NULL;
    }
  }

  /**
   * Marks source item status by node ID.
   */
  public function updateStatusByNodeId(int $nodeId, string $status): void {
    $this->database->update('tiscom_news_auto_source_item')
      ->fields([
        'news_status' => $status,
        'news_changed' => \Drupal::time()->getRequestTime(),
      ])
      ->condition('news_node_id', $nodeId)
      ->execute();
  }

  protected function downloadImage(string $imageUrl, string $title): ?File {
    $config = $this->configFactory->get('tiscom_news_auto.settings');
    $directory = (string) ($config->get('image_directory') ?: 'public://news-auto/');

    try {
      $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      $response = $this->httpClient->request('GET', $imageUrl, [
        'timeout' => 30,
        'headers' => [
          'User-Agent' => 'TiscomNewsAuto/1.0 (+https://tiscom.ru)',
        ],
      ]);
      $data = (string) $response->getBody();
      if ($data === '') {
        return NULL;
      }

      $path = parse_url($imageUrl, PHP_URL_PATH) ?: '';
      $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
      if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], TRUE)) {
        $extension = 'jpg';
      }

      $filename = $this->sanitizeFilename($title) . '.' . $extension;
      $destination = rtrim($directory, '/') . '/' . $filename;
      $uri = $this->fileSystem->saveData($data, $destination, FileSystemInterface::EXISTS_RENAME);

      if (!$uri) {
        return NULL;
      }

      $file = File::create(['uri' => $uri]);
      $file->setPermanent();
      $file->save();

      return $file;
    }
    catch (\Throwable $e) {
      $this->logger->warning('Unable to download image @url: @message', ['@url' => $imageUrl, '@message' => $e->getMessage()]);
      return NULL;
    }
  }

  protected function recordSourceItem(array $article, ?int $nodeId, string $status): void {
    $now = \Drupal::time()->getRequestTime();
    $sourceUrl = (string) ($article['url'] ?? '');
    $hash = $this->hash($sourceUrl);

    $fields = [
      'news_source_id' => $article['source_id'] ?? 'unknown',
      'news_source_url' => $sourceUrl,
      'news_source_title' => mb_substr((string) ($article['title'] ?? ''), 0, 512),
      'news_source_hash' => $hash,
      'news_node_id' => $nodeId,
      'news_status' => $status,
      'news_changed' => $now,
    ];

    try {
      $existing = $this->database->select('tiscom_news_auto_source_item', 'i')
        ->fields('i', ['id'])
        ->condition('news_source_hash', $hash)
        ->execute()
        ->fetchField();

      if ($existing) {
        $this->database->update('tiscom_news_auto_source_item')
          ->fields($fields)
          ->condition('id', $existing)
          ->execute();
      }
      else {
        $fields['news_created'] = $now;
        $this->database->insert('tiscom_news_auto_source_item')
          ->fields($fields)
          ->execute();
      }
    }
    catch (\Throwable $e) {
      $this->logger->error('Unable to record source item @url: @message', ['@url' => $sourceUrl, '@message' => $e->getMessage()]);
    }
  }

  protected function hash(string $sourceUrl): string {
    return hash('sha256', trim(mb_strtolower($sourceUrl)));
  }

  protected function sanitizeFilename(string $title): string {
    $name = mb_strtolower($title);
    $name = preg_replace('/[^a-zа-яё0-9]+/ui', '-', $name);
    $name = trim($name, '-');
    if ($name === '') {
      $name = 'news-image';
    }
    return mb_substr($name, 0, 80);
  }

}
