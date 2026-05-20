<?php

namespace Drupal\tiscom_news_auto\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\tiscom_news_auto\Service\NewsCreator;
use Drupal\tiscom_news_auto\Service\TelegramNotifier;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles Telegram callback webhook.
 */
class TelegramWebhookController extends ControllerBase {

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected TelegramNotifier $telegramNotifier,
    protected NewsCreator $newsCreator,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('tiscom_news_auto.telegram_notifier'),
      $container->get('tiscom_news_auto.news_creator')
    );
  }

  public function handle(string $secret, Request $request): JsonResponse {
    $expectedSecret = (string) $this->configFactory->get('tiscom_news_auto.settings')->get('telegram_webhook_secret');
    if ($expectedSecret === '' || !hash_equals($expectedSecret, $secret)) {
      return new JsonResponse(['ok' => FALSE, 'error' => 'Forbidden'], 403);
    }

    $update = json_decode($request->getContent(), TRUE) ?: [];
    if (empty($update['callback_query'])) {
      return new JsonResponse(['ok' => TRUE]);
    }

    $callback = $update['callback_query'];
    $callbackId = (string) ($callback['id'] ?? '');
    $data = (string) ($callback['data'] ?? '');
    $parsed = $this->telegramNotifier->parseCallbackData($data);

    if (!$parsed) {
      if ($callbackId) {
        $this->telegramNotifier->answerCallback($callbackId, 'Команда не распознана.', TRUE);
      }
      return new JsonResponse(['ok' => TRUE]);
    }

    $node = $this->entityTypeManager->getStorage('node')->load($parsed['node_id']);
    if (!$node) {
      $this->telegramNotifier->answerCallback($callbackId, 'Новость не найдена.', TRUE);
      return new JsonResponse(['ok' => TRUE]);
    }

    if ($parsed['action'] === 'publish') {
      $node->setPublished(TRUE);
      $node->save();
      $this->newsCreator->updateStatusByNodeId((int) $node->id(), 'published');
      $message = 'Новость опубликована: ' . $node->label();
      $this->telegramNotifier->answerCallback($callbackId, 'Опубликовано');
      $this->editTelegramMessage($callback, '✅ <b>Опубликовано</b>\n' . htmlspecialchars($node->label(), ENT_QUOTES));
    }
    else {
      $node->setUnpublished();
      $node->save();
      $this->newsCreator->updateStatusByNodeId((int) $node->id(), 'rejected');
      $message = 'Новость отклонена: ' . $node->label();
      $this->telegramNotifier->answerCallback($callbackId, 'Отклонено');
      $this->editTelegramMessage($callback, '❌ <b>Отклонено</b>\n' . htmlspecialchars($node->label(), ENT_QUOTES));
    }

    return new JsonResponse(['ok' => TRUE, 'message' => $message]);
  }

  protected function editTelegramMessage(array $callback, string $text): void {
    if (!empty($callback['message']['chat']['id']) && !empty($callback['message']['message_id'])) {
      $this->telegramNotifier->editMessage(
        $callback['message']['chat']['id'],
        (int) $callback['message']['message_id'],
        $text
      );
    }
  }

}
