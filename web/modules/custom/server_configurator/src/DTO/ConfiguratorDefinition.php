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
 * - summary field definitions
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
    protected array $summary = [],
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

  /**
   * @return array<int, array{field:string,label:string,format?:string}>
   */
  public function getSummaryFields(): array {
    $normalized = [];

    foreach ($this->summaryFields as $item) {
      if (is_string($item)) {
        $normalized[] = [
          'field' => $item,
          'label' => $item,
        ];
        continue;
      }

      if (is_array($item) && !empty($item['field']) && !empty($item['label'])) {
        $normalized[] = [
          'field' => $item['field'],
          'label' => $item['label'],
          'format' => $item['format'] ?? '',
        ];
      }
    }

    return $normalized;
  }


  public function getSummaryConfig(): array {
    $default = [
      'server' => [
        'enabled' => TRUE,
        'title' => 'Сервер',
      ],
      'platform' => [
        'enabled' => TRUE,
        'title' => 'Параметры платформы',
      ],
      'cpu' => [
        'enabled' => TRUE,
        'title' => 'Выбранные процессоры',
      ],
      'form' => [
        'enabled' => TRUE,
        'title' => 'Выбранные параметры',
        'fields' => $this->getSummaryFields(),
      ],
    ];

    $summary = $this->summary ?: [];

    return [
      'server' => array_replace($default['server'], $summary['server'] ?? []),
      'platform' => array_replace($default['platform'], $summary['platform'] ?? []),
      'cpu' => array_replace($default['cpu'], $summary['cpu'] ?? []),
      'form' => array_replace($default['form'], [
        'fields' => $default['form']['fields'],
      ], $summary['form'] ?? []),
    ];
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

  public function toFrontendConfig(): array {
    return [
      'name' => $this->getName(),
      'label' => $this->getLabel(),
      'webform_id' => $this->getWebformId(),
      'features' => $this->getFeatures(),
      'fields' => $this->getFields(),
      'summary_fields' => $this->getSummaryFields(),
      'summary' => $this->getSummaryConfig(),
      'processors' => $this->getProcessorsConfig(),
    ];
  }

}
