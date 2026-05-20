<?php

namespace Drupal\server_configurator\Presentation\Feature\Dependencies;

use Drupal\server_configurator\Presentation\Builder\ConfiguratorOptionsBuilder;

/**
 * Calculates options for dependent configurator elements.
 */
final class DependenciesOptionsProvider {

  public function __construct(
    private readonly ConfiguratorOptionsBuilder $optionsBuilder,
  ) {}

  /**
   * Builds dependency options from selected form values.
   */
  public function buildOptions(array $selectedValues): DependenciesOptionsResult {
    $cpuGenerationArguments = $this->buildCpuGenerationArguments($selectedValues['cpu_vendor'] ?? NULL);

    if (empty($selectedValues['cpu_generation'])) {
      return DependenciesOptionsResult::empty($cpuGenerationArguments);
    }

    if (empty($selectedValues['form_factor'])) {
      return DependenciesOptionsResult::empty($cpuGenerationArguments);
    }

    $result = $this->optionsBuilder->buildPlatformAndServerOptions([
      'cpu_generation' => $selectedValues['cpu_generation'],
      'form_factor' => $selectedValues['form_factor'],
      'storage_form_factor' => $this->normalizeStorageFormFactor($selectedValues['storage_form_factor'] ?? NULL),
    ]);

    return new DependenciesOptionsResult(
      cpuGenerationArguments: $cpuGenerationArguments,
      platformIds: $result['platform_ids'] ?? [],
      platformOptions: $result['platform_options'] ?? [],
      serverOptions: $result['server_options'] ?? [],
      serverPlatformMap: $result['server_platform_map'] ?? [],
    );
  }

  private function buildCpuGenerationArguments(mixed $vendorValue): array {
    return $this->hasValue($vendorValue) ? [$vendorValue] : [-1];
  }

  private function normalizeStorageFormFactor(mixed $storageFormFactorValue): ?string {
    // Current business rule:
    // - 3.5 means filter platforms by field_storage_form_factor = 3.5.
    // - any other empty/unknown value does not add this filter.
    return ((string) $storageFormFactorValue === '3.5') ? '3.5' : NULL;
  }

  private function hasValue(mixed $value): bool {
    return $value !== NULL && $value !== '' && $value !== [];
  }

}
