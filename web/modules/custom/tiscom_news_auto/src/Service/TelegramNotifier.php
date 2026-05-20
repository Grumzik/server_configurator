<?php

namespace Drupal\tiscom_news_auto\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Sends Telegram moderation notifications.
 */
class TelegramNotifier {

  public function __construct(
    protected ClientInterface $httpClient,
    protected ConfigFactoryInterface $configFactory,
    protected UrlGeneratorInterface $urlGenerator,
    protected LoggerInterface $logger,
  ) {}

  /**
   * Sends notification for a created news draft.
   */
  public function notifyDraft(NodeInterface $node, array $article, array $prepared): void {
    $config = $this->configFactory->get('tiscom_news_auto.settings');
    $token = (string) $config->get('telegram_bot_token');
    $chatId = (string) $config->get('telegram_chat_id');
    if ($token === '' || $chatId === '') {
      return;
    }

    $editUrl = Url::fromRoute('entity.node.edit_form', ['node' => $node->id()], ['absolute' => TRUE])->toString();
    $viewUrl = $node->toUrl('canonical', ['absolute' => TRUE])->toString();

    $text = "📰 Подготовлена новость для сайта Тиском\n\n";
    $text .= "<b>" . htmlspecialchars($node->label(), ENT_QUOTES) . "</b>\n\n";
    if (!empty($prepared['anons'])) {
      $text .= htmlspecialchars($prepared['anons'], ENT_QUOTES) . "\n\n";
    }
    $text .= "Источник: TechSpot\n";
    $text .= htmlspecialchars($article['url'] ?? '', ENT_QUOTES);

    $keyboard = [
      'inline_keyboard' => [
        [
          ['text' => 'Посмотреть', 'url' => $editUrl],
          ['text' => 'Открыть на сайте', 'url' => $viewUrl],
        ],
        [
          ['text' => '✅ Опубликовать', 'callback_data' => $this->callbackData('publish', (int) $node->id())],
          ['text' => '❌ Отклонить', 'callback_data' => $this->callbackData('reject', (int) $node->id())],
        ],
      ],
    ];

    $this->call('sendMessage', [
      'chat_id' => $chatId,
      'text' => $text,
      'parse_mode' => 'HTML',
      'disable_web_page_preview' => FALSE,
      'reply_markup' => $keyboard,
    ]);
  }

  /**
   * Answers a Telegram callback query.
   */
  public function answerCallback(string $callbackId, string $message, bool $alert = FALSE): void {
    $this->call('answerCallbackQuery', [
      'callback_query_id' => $callbackId,
      'text' => $message,
      'show_alert' => $alert,
    ]);
  }

  /**
   * Edits a message after moderation action.
   */
  public function editMessage(int|string $chatId, int $messageId, string $text): void {
    $this->call('editMessageText', [
      'chat_id' => $chatId,
      'message_id' => $messageId,
      'text' => $text,
      'parse_mode' => 'HTML',
    ]);
  }

  /**
   * Builds callback data with HMAC signature.
   */
  public function callbackData(string $action, int $nodeId): string {
    $secret = (string) $this->configFactory->get('tiscom_news_auto.settings')->get('telegram_webhook_secret');
    $payload = $action . ':' . $nodeId;
    $signature = substr(hash_hmac('sha256', $payload, $secret), 0, 16);
    return $payload . ':' . $signature;
  }

  /**
   * Validates callback data.
   */
  public function parseCallbackData(string $data): ?array {
    $parts = explode(':', $data);
    if (count($parts) !== 3) {
      return NULL;
    }
    [$action, $nodeId, $signature] = $parts;
    if (!in_array($action, ['publish', 'reject'], TRUE) || !ctype_digit($nodeId)) {
      return NULL;
    }
    $expected = $this->callbackData($action, (int) $nodeId);
    if (!hash_equals($expected, $data)) {
      return NULL;
    }
    return ['action' => $action, 'node_id' => (int) $nodeId];
  }

  protected function call(string $method, array $params): ?array {
    $token = (string) $this->configFactory->get('tiscom_news_auto.settings')->get('telegram_bot_token');
    if ($token === '') {
      return NULL;
    }

    try {
      $response = $this->httpClient->request('POST', 'https://api.telegram.org/bot' . $token . '/' . $method, [
        'timeout' => 30,
        'json' => $params,
      ]);
      return json_decode((string) $response->getBody(), TRUE) ?: NULL;
    }
    catch (\Throwable $e) {
      $this->logger->warning('Telegram @method failed: @message', ['@method' => $method, '@message' => $e->getMessage()]);
      return NULL;
    }
  }

}
