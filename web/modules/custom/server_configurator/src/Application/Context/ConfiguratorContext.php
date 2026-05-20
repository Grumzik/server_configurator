<?php

namespace Drupal\server_configurator\Application\Context;

use Drupal\server_configurator\Domain\Configurator\ConfiguratorDefinitions;
use Drupal\server_configurator\Domain\Configurator\ConfiguratorDefinition;

final class ConfiguratorContext {

  public function __construct(
    protected bool $isConfigurator,
    protected ?ConfiguratorDefinition $definition = NULL,
  ) {}

  public function isConfigurator(): bool {
    return $this->isConfigurator;
  }

  public function getDefinition(): ?ConfiguratorDefinition {
    return $this->definition;
  }

  public function getMode(): string {
    return $this->definition?->getName() ?? ConfiguratorDefinitions::UNKNOWN;
  }

  public function getWebformId(): ?string {
    return $this->definition?->getWebformId();
  }

  public function isServerConfigurator(): bool {
    return $this->getMode() === ConfiguratorDefinitions::SERVER_CONFIGURATOR;
  }

  public function isMainConfigurator(): bool {
    return $this->getMode() === ConfiguratorDefinitions::MAIN_CONFIGURATOR;
  }

  public function shouldAttachFrontend(): bool {
    return $this->isConfigurator;
  }

  public function hasFeature(string $feature): bool {
    return $this->definition?->hasFeature($feature) ?? FALSE;
  }

  public function getFeatures(): array {
    return $this->definition?->getFeatures() ?? [];
  }

}
