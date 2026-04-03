<?php

namespace Drupal\smart_compatibility_engine\Domain\Model;

final class Cpu {

  public function __construct(
  private readonly string $vendor,
  private readonly string $generation,
  private readonly int $tdp
  ) {}

  public function vendor(): string {
  return $this->vendor;
  }

  public function generation(): string {
  return $this->generation;
  }

  public function tdp(): int {
  return $this->tdp;
  }
}
