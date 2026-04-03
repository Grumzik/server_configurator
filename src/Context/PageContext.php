<?php
//Application layer — решает: где мы и что делать
namespace Drupal\server_configurator\Context;

use Drupal\Core\Routing\RouteMatchInterface;

class PageContext {
  public function __construct(
   protected RouteMatchInterface $routeMatch,
  ) {}

  public function isServerPage(): bool {
    $node = $this->routeMatch->getParameter('node');
    return $node && $node->bundle() === 'server';
  }
}
