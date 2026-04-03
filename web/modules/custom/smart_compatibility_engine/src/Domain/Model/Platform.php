<?php

namespace Drupal\smart_compatibility_engine\Domain\Model;

final class Platform {

  public function __construct(
    private readonly Architecture $architecture,
    private readonly int  $maxTdp
  ) {}

  public function architecture(): Architecture {
    return $this->architecture;
  }

  public function maxTdp(): int {
    return $this->maxTdp;
  }
}
