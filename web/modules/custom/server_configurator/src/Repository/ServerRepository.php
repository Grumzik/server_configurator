<?php

namespace Drupal\server_configurator\Repository;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Repository for server nodes used by the main configurator.
 */
final class ServerRepository {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Finds server nodes based on compatible platform IDs.
   *
   * @param int[] $platformIds
   *   Platform node IDs.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Server nodes keyed by node ID.
   */
  public function findServersByPlatformIds(array $platformIds): array {
    if (empty($platformIds)) {
      return [];
    }

    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'server')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('field_platform', $platformIds, 'IN')
      ->sort('title', 'ASC');

    $serverIds = $query->execute();
    if (empty($serverIds)) {
      return [];
    }

    return $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($serverIds);
  }

  /**
   * Builds simple select options from server nodes.
   *
   * Kept for backward compatibility and debugging.
   *
   * @param \Drupal\node\NodeInterface[] $servers
   *   Server nodes.
   *
   * @return array<int, string>
   *   Select options: nid => title.
   */
  public function buildOptions(array $servers): array {
    $options = [];

    foreach ($servers as $server) {
      if ($server instanceof NodeInterface) {
        $options[(int) $server->id()] = $server->label();
      }
    }

    return $options;
  }

  /**
   * Builds tableselect rows from server nodes.
   *
   * @param \Drupal\node\NodeInterface[] $servers
   *   Server nodes.
   *
   * @return array<int, array<string, string|int>>
   *   Tableselect options keyed by server node ID.
   */
  public function buildTableOptions(array $servers): array {
    $options = [];

    foreach ($servers as $server) {
      if (!$server instanceof NodeInterface) {
        continue;
      }

      $platform = $this->getServerPlatform($server);
      $maxDrives = $platform instanceof NodeInterface ? $this->getPlatformMaxDrives($platform) : '—';

      $options[(int) $server->id()] = [
        'title' => $server->label(),
        'platform' => $platform instanceof NodeInterface ? $platform->label() : '—',
        'max_drives' => $maxDrives,
      ];
    }

    return $options;
  }

  /**
   * Builds a JS-friendly map: server ID -> server/platform data.
   *
   * This is used by frontend storage logic. When the user selects a server in
   * the tableselect, JS takes the platform ID from this map, then loads full
   * platform data from drupalSettings.serverConfigurator.platformMap.
   *
   * @param \Drupal\node\NodeInterface[] $servers
   *   Server nodes.
   *
   * @return array<string, array<string, mixed>>
   *   Map keyed by server node ID.
   */
  public function buildServerPlatformMap(array $servers): array {
    $map = [];

    foreach ($servers as $server) {
      if (!$server instanceof NodeInterface) {
        continue;
      }

      $platform = $this->getServerPlatform($server);
      $platformId = $platform instanceof NodeInterface ? (int) $platform->id() : NULL;
      $storageBays = $platform instanceof NodeInterface ? $this->getPlatformMaxDrivesValue($platform) : NULL;

      $map[(string) $server->id()] = [
        'id' => (int) $server->id(),
        'title' => $server->label(),
        'platform_id' => $platformId,
        'platform' => $platform instanceof NodeInterface ? $platform->label() : '',
        'storage_bays' => $storageBays,
      ];
    }

    return $map;
  }

  protected function getServerPlatform(NodeInterface $server): ?NodeInterface {
    if (!$server->hasField('field_platform') || $server->get('field_platform')->isEmpty()) {
      return NULL;
    }

    $platform = $server->get('field_platform')->entity;
    return $platform instanceof NodeInterface ? $platform : NULL;
  }

  protected function getPlatformMaxDrivesValue(NodeInterface $platform): ?int {
    foreach ($this->getPlatformMaxDrivesFieldCandidates() as $fieldName) {
      if (!$platform->hasField($fieldName) || $platform->get($fieldName)->isEmpty()) {
        continue;
      }

      $value = $platform->get($fieldName)->value;
      if ($value !== NULL && $value !== '') {
        return (int) $value;
      }
    }

    return NULL;
  }

  protected function getPlatformMaxDrives(NodeInterface $platform): string {
    foreach ($this->getPlatformMaxDrivesFieldCandidates() as $fieldName) {
      if (!$platform->hasField($fieldName) || $platform->get($fieldName)->isEmpty()) {
        continue;
      }

      $field = $platform->get($fieldName);
      $first = $field->first();
      if ($first === NULL) {
        continue;
      }

      $value = $first->getValue();

      if (isset($value['value']) && $value['value'] !== '') {
        return (string) $value['value'];
      }

      if (isset($value['target_id']) && $value['target_id'] !== '') {
        $entity = $first->entity ?? NULL;
        return $entity ? $entity->label() : (string) $value['target_id'];
      }
    }

    return '—';
  }

  /**
   * Possible machine names for the platform field with max drive/bay count.
   *
   * Put the real field name first when it is finalized.
   *
   * @return string[]
   */
  protected function getPlatformMaxDrivesFieldCandidates(): array {
    return [
      'field_max_drives',
      'field_storage_bays',
      'field_max_storage_devices',
      'field_max_drive_count',
      'field_storage_max_count',
      'field_drive_bays',
    ];
  }

}
