<?php

namespace Drupal\smart_compatibility_engine\Infrastructure\Drupal\Repository;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\smart_compatibility_engine\Domain\Model\Platform;
use Drupal\smart_compatibility_engine\Domain\Repository\PlatformRepository;
use Drupal\node\NodeInterface;

final class PlatformRepository implements PlatformRepository {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager
  ) {}

  public function get(int $id): Platform {
    $node = $this->loadNode($id);

    return new Platform(
      id: $node->id(),
      maxCpuTdp: (int) $node->get('field_max_cpu_tdp')->value,
      architecture: $node->get('field_architecture')->value
    );
  }

  private function loadNode(int $id): NodeInterface {
    $node = $this->entityTypeManager
      ->getStorage('node')
      ->load($id);

    if (!$node instanceof NodeInterface || $node->bundle() !== 'platform') {
      throw new \InvalidArgumentException("Platform node $id not found");
    }

    return $node;
  }
}
