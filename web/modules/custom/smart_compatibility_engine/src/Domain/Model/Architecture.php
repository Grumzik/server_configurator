<?php

namespace Drupal\smart_compatibility_engine\Domain\Model;


final class Architecture {

  public function __construct(
    private readonly string $formFactor,   // 2U, 1U
    private readonly int $nodes            // 1, 2, 4
  ) {}

  public function formFactor(): string {
    return $this->formFactor;
  }

  public function nodes(): int {
    return $this->nodes;
  }
}
