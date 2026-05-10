<?php

namespace Drupal\server_configurator\Repository;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Repository for platform nodes used by the main configurator.
 */
final class PlatformRepository {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Finds IDs of platform nodes compatible with selected configurator filters.
   *
   * Expected filters:
   * - cpu_generation: taxonomy term ID.
   * - form_factor: taxonomy term ID.
   * - storage_form_factor: optional storage form factor value, e.g. "2.5".
   *
   * @return int[]
   *   List of compatible platform node IDs.
   */
  public function findCompatiblePlatformIds(array $filters): array {
    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'platform')
      ->condition('status', NodeInterface::PUBLISHED);

    if (!empty($filters['cpu_generation'])) {
      $query->condition('field_cpu_generation', $filters['cpu_generation']);
    }

    if (!empty($filters['form_factor'])) {
      $query->condition('field_form_factor', $filters['form_factor']);
    }

    if (!empty($filters['storage_form_factor'])) {
      $query->condition('field_storage_form_factor', $filters['storage_form_factor']);
    }

    return array_map('intval', array_values($query->execute()));
  }

  /**
   * Builds options for a select element from platform IDs.
   *
   * @param int[] $platformIds
   *   Platform node IDs.
   *
   * @return array<int, string>
   *   Select options: nid => title.
   */
  public function buildOptionsByIds(array $platformIds): array {
    if (empty($platformIds)) {
      return [];
    }

    $platforms = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($platformIds);

    $options = [];
    foreach ($platforms as $platform) {
      if ($platform instanceof NodeInterface) {
        $options[(int) $platform->id()] = $platform->label();
      }
    }

    return $options;
  }

}
