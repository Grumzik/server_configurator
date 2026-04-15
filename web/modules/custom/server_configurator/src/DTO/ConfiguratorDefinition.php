<?php

namespace Drupal\server_configurator\DTO;

/**
 * DTO: description of one configurator.
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

  public function hasDependencies(): bool {
    return !empty($this->dependencies);
  }

  public function getDependencyByType(string $type): ?array {
    foreach ($this->dependencies as $dependency) {
      if (($dependency['type'] ?? NULL) === $type) {
        return $dependency;
      }
    }

    return NULL;
  }

  public function getDependenciesBySource(string $sourceAlias): array {
    $matched = [];

    foreach ($this->dependencies as $dependency) {
      $sources = (array) ($dependency['source'] ?? []);
      if (in_array($sourceAlias, $sources, TRUE)) {
        $matched[] = $dependency;
      }
    }

    return $matched;
  }

  public function getDependencySource(string $type): string|array|null {
    return $this->getDependencyByType($type)['source'] ?? NULL;
  }

  public function getDependencyTarget(string $type): ?string {
    return $this->getDependencyByType($type)['target'] ?? NULL;
  }

  public function getDependencyWrapperId(array $dependency, ?string $default = NULL): ?string {
    return $dependency['wrapper_id'] ?? $default;
  }

  public function getDependencyAjaxCallback(array $dependency, ?string $default = NULL): ?string {
    return $dependency['ajax_callback'] ?? $default;
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
