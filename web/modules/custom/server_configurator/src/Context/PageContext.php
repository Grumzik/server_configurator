<?php

namespace Drupal\server_configurator\Context;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\webform\WebformInterface;

class PageContext {

  public function __construct(
    protected RouteMatchInterface $routeMatch,
  ) {}

  public function isServerPage(): bool {
    $node = $this->routeMatch->getParameter('node');
    return $node instanceof NodeInterface && $node->bundle() === 'server';
  }

  public function isMainConfiguratorPage(): bool {
    $webform = $this->routeMatch->getParameter('webform');
    return $webform instanceof WebformInterface
      && $webform->id() === 'main_server_configurator';
  }

  public function shouldAttach(): bool {
    return $this->isServerPage() || $this->isMainConfiguratorPage();
  }

  public function getMode(): string {
    if ($this->isServerPage()) {
      return 'server_page';
    }

    if ($this->isMainConfiguratorPage()) {
      return 'main_configurator';
    }

    return 'unknown';
  }

}
