<?php

namespace Drupal\smart_compatibility_engine\Domain\Model;

final class Configuration {

  public function __construct(
    private readonly Platform $platform,
    private readonly ?Cpu $cpu = null
  ) {}

  public function platform(): Platform {
    return $this->platform;
  }

  public function cpu(): ?Cpu {
    return $this->cpu;
  }
}
