<?php

namespace Drupal\server_configurator\Domain\Configurator;

/**
 * Declarative description of one server configurator.
 */
final class ConfiguratorDefinition {

  public function __construct(
    protected string $name,
    protected string $label,
    protected string $webformId,
    protected array $features = [],
    protected array $dependencies = [],
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

  public function getDependencies(): array {
    return $this->dependencies;
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
        'fields' => [],
      ],
    ];

    $summary = $this->summary ?: [];

    return [
      'server' => array_replace($default['server'], $summary['server'] ?? []),
      'platform' => array_replace($default['platform'], $summary['platform'] ?? []),
      'cpu' => array_replace($default['cpu'], $summary['cpu'] ?? []),
      'form' => array_replace($default['form'], $summary['form'] ?? []),
    ];
  }

  /**
   * @return array<int, array{field:string,label:string,format?:string}>
   */
  public function getSummaryFields(): array {
    $fields = $this->getSummaryConfig()['form']['fields'] ?? [];
    $normalized = [];

    foreach ($fields as $item) {
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

  public function toFrontendConfig(): array {
    return [
      'name' => $this->getName(),
      'label' => $this->getLabel(),
      'webform_id' => $this->getWebformId(),
      'features' => $this->getFeatures(),
      'dependencies' => $this->getDependencies(),
      'processors' => $this->getProcessorsConfig(),
      'summary' => $this->getSummaryConfig(),
    ];
  }

}
