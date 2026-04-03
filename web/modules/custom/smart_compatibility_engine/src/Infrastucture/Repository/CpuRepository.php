<?php

namespace Drupal\smart_compatibility_engine\Infrastructure\Drupal\Repository;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\smart_compatibility_engine\Domain\Model\Cpu;
use Drupal\smart_compatibility_engine\Domain\Repository\CpuRepository;
use Drupal\node\NodeInterface;

final class CpuRepository implements CpuRepository {

  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager
  ) {}

  public function get(int $id): Cpu {
    $node = $this->loadNode($id);

    return new Cpu(
      id: $node->id(),
      tdp: (int) $node->get('field_tdp')->value,
      socket: $node->get('field_socket')->value,
      generation: $node->get('field_generation')->value
    );
  }

  private function loadNode(int $id): NodeInterface {
    $node = $this->entityTypeManager
      ->getStorage('node')
      ->load($id);

    if (!$node instanceof NodeInterface || $node->bundle() !== 'processor') {
      throw new \InvalidArgumentException("CPU node $id not found");
    }

    return $node;
  }
}
