<?php

namespace Drupal\server_configurator\DTO;

/**
 * DTO: declarative description of one configurator.
 *
 * Stores stable configuration for one configurator instance:
 * - identity
 * - enabled features
 * - canonical field mapping
 * - dependencies configuration
 * - summary fields
 * - processors configuration (filters/defaults)
 */
final class ConfiguratorDefinition {

  public function __construct(
    protected string $name,
    protected string $label,
    protected string $webformId,
    protected array $features = [],
    protected array $fields = [],
    protected array $dependencies = [],
    protected array $summaryFields = [],
    protected array $processors = [],
  ) {}

  public function getName(): string {
    return $this->name;
  }

  public function getLabel(): string {
    return $this->label;
  }

  public function getWebformId(): string {
    return $this->webformId;
  }

  public function getFeatures(): array {
    return $this->features;
  }

  public function hasFeature(string $featureName): bool {
    return !empty($this->features[$featureName]);
  }

  public function getFields(): array {
    return $this->fields;
  }

  public function hasField(string $fieldKey): bool {
    return array_key_exists($fieldKey, $this->fields);
  }

  public function getField(string $fieldKey, mixed $default = NULL): mixed {
    return $this->fields[$fieldKey] ?? $default;
  }

  public function getDependencies(): array {
    return $this->dependencies;
  }

  public function getSummaryFields(): array {
    return $this->summaryFields;
  }

  public function getProcessorsConfig(): array {
    return $this->processors;
  }

  public function getProcessorsFilterField(string $key, mixed $default = NULL): mixed {
    return $this->processors['filters'][$key] ?? $default;
  }

  public function getProcessorsDefault(string $key, mixed $default = NULL): mixed {
    return $this->processors['defaults'][$key] ?? $default;
  }

}
