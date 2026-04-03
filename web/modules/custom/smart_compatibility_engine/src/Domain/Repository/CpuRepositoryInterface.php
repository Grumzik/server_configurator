<?php
namespace Drupal\smart_compatibility_engine\Domain\Repository;

use Drupal\smart_compatibility_engine\Domain\Model\Cpu;
interface CpuRepository {
  public function get(int $id): Cpu;
}
