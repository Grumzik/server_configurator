<?php

namespace Drupal\tiscom_news_auto\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use Psr\Log\LoggerInterface;

/**
 * Coordinates source fetching, rewriting, node creation and notification.
 */
class NewsImporter {

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected StateInterface $state,
    protected TechspotSource $techspotSource,
    protected OpenAiClient $openAiClient,
    protected NewsCreator $newsCreator,
    protected TelegramNotifier $telegramNotifier,
    protected LoggerInterface $logger,
  ) {}

  /**
   * Runs import on cron if enabled and interval elapsed.
   */
  public function runScheduled(): array {
    $config = $this->configFactory->get('tiscom_news_auto.settings');
    if (!$config->get('enabled')) {
      return ['created' => 0, 'skipped' => 0];
    }

    $now = \Drupal::time()->getRequestTime();
    $lastRun = (int) $this->state->get('tiscom_news_auto.last_run', 0);
    $interval = (int) ($config->get('cron_interval') ?: 604800);
    if ($lastRun && ($now - $lastRun) < $interval) {
      return ['created' => 0, 'skipped' => 0];
    }

    $result = $this->runNow();
    $this->state->set('tiscom_news_auto.last_run', $now);
    return $result;
  }

  /**
   * Runs import immediately.
   */
  public function runNow(): array {
    $config = $this->configFactory->get('tiscom_news_auto.settings');
    $count = (int) ($config->get('count') ?: 5);
    $daysBack = (int) ($config->get('days_back') ?: 7);
    $url = (string) ($config->get('techspot_url') ?: 'https://www.techspot.com/category/hardware/');

    $articles = $this->techspotSource->fetch($url, $count, $daysBack);
    $created = 0;
    $skipped = 0;

    foreach ($articles as $article) {
      if (empty($article['url']) || $this->newsCreator->isDuplicate($article['url'])) {
        $skipped++;
        continue;
      }

      $prepared = $this->openAiClient->rewrite($article);
      $node = $this->newsCreator->createDraft($article, $prepared);
      if ($node) {
        $created++;
        $this->telegramNotifier->notifyDraft($node, $article, $prepared);
      }
    }

    $this->logger->notice('News auto import completed. Created: @created, skipped: @skipped.', [
      '@created' => $created,
      '@skipped' => $skipped,
    ]);

    return ['created' => $created, 'skipped' => $skipped];
  }

}
