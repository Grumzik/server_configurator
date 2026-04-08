<?php

namespace Drupal\server_configurator\Data;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;

class PlatformDataProvider {

  public function __construct(
    protected RouteMatchInterface $routeMatch,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Backward-compatible method name used in older code/views.
   */
  public function getPlatformData(): array {
    return $this->getCurrentPlatformData();
  }

  /**
   * Current platform for the server-page configurator.
   */
  public function getCurrentPlatformData(): array {
    $platform = $this->getPlatformNodeFromServerPage();
    if (!$platform) {
      return [];
    }

    return $this->normalizePlatformNode($platform);
  }

  /**
   * Map of all active platform nodes for the main configurator.
   *
   * @return array<string, array>
   */
  public function getPlatformMap(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'platform')
      ->condition('status', 1)
      ->sort('title', 'ASC')
      ->execute();

    if (empty($ids)) {
      return [];
    }

    $nodes = $storage->loadMultiple($ids);
    $map = [];

    foreach ($nodes as $node) {
      if (!$node instanceof NodeInterface) {
        continue;
      }

      $map[(string) $node->id()] = $this->normalizePlatformNode($node);
    }

    return $map;
  }

  protected function getPlatformNodeFromServerPage(): ?NodeInterface {
    $node = $this->routeMatch->getParameter('node');

    if (!$node instanceof NodeInterface || $node->bundle() !== 'server') {
      return NULL;
    }

    if (!$node->hasField('field_platform') || $node->get('field_platform')->isEmpty()) {
      return NULL;
    }

    $platform = $node->get('field_platform')->entity;
    return $platform instanceof NodeInterface ? $platform : NULL;
  }

  protected function normalizePlatformNode(NodeInterface $platform): array {
    return [
      'id' => (int) $platform->id(),
      'title' => $platform->label(),
      'form_factor' => $this->getTermLabel($platform, 'field_form_factor'),
      'cpu_generation' => $this->getTermLabel($platform, 'field_cpu_generation'),
      'cpu_generation_id' => $this->getEntityId($platform, 'field_cpu_generation'),
      'tdp' => $this->getNumeric($platform, 'field_ccu_tdp'),
      'storage_form_factor' => $this->getFloat($platform, 'field_storage_form_factor'),
      'storage_bays' => $this->getNumeric($platform, 'field_storage_bays'),
      'psu_type' => $this->getTermLabel($platform, 'field_psu_type'),
      'lan' => $this->getText($platform, 'field_lan_ports_speed'),
      'lan_manager' => $this->getText($platform, 'field_lan_manager'),
    ];
  }

  protected function getEntityId(NodeInterface $node, string $field): ?int {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return NULL;
    }

    return (int) $node->get($field)->target_id;
  }

  protected function getNumeric(NodeInterface $node, string $field): ?int {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return NULL;
    }

    return (int) $node->get($field)->value;
  }

  protected function getFloat(NodeInterface $node, string $field): ?float {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return NULL;
    }

    return (float) $node->get($field)->value;
  }

  protected function getTermLabel(NodeInterface $node, string $field): ?string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return NULL;
    }

    return $node->get($field)->entity?->label();
  }

  protected function getText(NodeInterface $node, string $field): ?string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return NULL;
    }

    return $node->get($field)->value;
  }

}
