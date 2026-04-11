<?php

namespace Drupal\server_configurator\Context;

use Drupal\server_configurator\Config\ConfiguratorDefinitions;

final class ConfiguratorContext {

  public function __construct(
    protected string $mode,
    protected bool $isConfigurator,
    protected array $features = [],
  ) {}

  public function getMode(): string {
    return $this->mode;
  }

  public function isConfigurator(): bool {
    return $this->isConfigurator;
  }

  public function isServerPage(): bool {
    return $this->mode === ConfiguratorDefinitions::SERVER_PAGE;
  }

  public function isMainConfigurator(): bool {
    return $this->mode === ConfiguratorDefinitions::MAIN_CONFIGURATOR;
  }

  public function shouldAttachFrontend(): bool {
    return $this->isConfigurator;
  }

  public function hasFeature(string $feature): bool {
    return !empty($this->features[$feature]);
  }

  public function getFeatures(): array {
    return $this->features;
  }

}
