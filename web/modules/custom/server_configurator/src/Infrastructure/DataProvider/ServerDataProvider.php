<?php

namespace Drupal\server_configurator\Infrastructure\DataProvider;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;

class ServerDataProvider {

  public function __construct(
    protected RouteMatchInterface $routeMatch,
  ) {}

  /**
   * Возвращает текущую ноду Server или null.
   */
  protected function getServerNode(): ?NodeInterface {
    $node = $this->routeMatch->getParameter('node');

    if ($node instanceof NodeInterface && $node->bundle() === 'server') {
      return $node;
    }

    return null;
  }

  /**
   * Возвращает данные сервера в нормализованном виде.
   */
  public function getServerData(): array {
    $server = $this->getServerNode();

    if (!$server) {
      return [];
    }

    return [
      'id' => $server->id(),
      'title' => $server->label(),
      'bundle' => $server->bundle(),
      'sku' => $this->getText($server, 'field_sku'),
      'server_types' => $this->getText($server, 'field_server_types'),
    ];
  }

  /* ==========================
   * Helpers
   * ========================== */

  protected function getText(NodeInterface $node, string $field): ?string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return null;
    }

    return $node->get($field)->value;
  }
}
