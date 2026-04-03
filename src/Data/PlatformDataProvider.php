<?php
//Data layer — ТОЛЬКО получение данных
namespace Drupal\server_configurator\Data;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;

class PlatformDataProvider {

  public function __construct(
    protected RouteMatchInterface $routeMatch,
  ) {}

  public function getPlatformData(): array {
    $platform = $this->getPlatformNode();
    if (!$platform) {
      return [];
    }

    return [
      'form_factor' => $this->getTermLabel($platform, 'field_form_factor'),
      'cpu_generation' => $this->getTermLabel($platform, 'field_cpu_generation'),
      'tdp' => $this->getNumeric($platform, 'field_ccu_tdp'),
      'storage_form_factor' => $this->getFloat($platform, 'field_storage_form_factor'),
      'storage_bays' => $this->getNumeric($platform, 'field_storage_bays'),
      'psu_type' => $this->getTermLabel($platform, 'field_psu_type'),
      'lan' => $this->getText($platform, 'field_lan_ports_speed'),
      'lan_manager' => $this->getText($platform, 'field_lan_manager'),
      ];
  }

  protected function getPlatformNode(): ?NodeInterface {
    $node = $this->routeMatch->getParameter('node');

    if (!$node || $node->bundle() !== 'server') {
    return null;
    }

    if ($node->get('field_platform')->isEmpty()) {
    return null;
    }

    return $node->get('field_platform')->entity;
  }

  protected function getNumeric(NodeInterface $node, string $field): ?int {
    return (!$node->get($field)->isEmpty())
    ? (int) $node->get($field)->value
    : null;
  }

  protected function getFloat(NodeInterface $node, string $field): ?float {
    return (!$node->get($field)->isEmpty())
      ? (float) $node->get($field)->value
      : null;
  }

  protected function getTermLabel(NodeInterface $node, string $field): ?string {
    return (!$node->get($field)->isEmpty())
    ? $node->get($field)->entity?->label()
    : null;
  }

  protected function getText(NodeInterface $node, string $field): ?string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return null;
    }

    return $node->get($field)->value;
  }


}
