<?php

namespace Drupal\server_configurator\Presentation\Feature\Dependencies;

/**
 * Result of dependency options calculation.
 */
final class DependenciesOptionsResult {

  public function __construct(
    public readonly array $cpuGenerationArguments,
    public readonly array $platformIds,
    public readonly array $platformOptions,
    public readonly array $serverOptions,
    public readonly array $serverPlatformMap,
  ) {}

  public static function empty(array $cpuGenerationArguments = [-1]): self {
    return new self(
      cpuGenerationArguments: $cpuGenerationArguments,
      platformIds: [],
      platformOptions: [],
      serverOptions: [],
      serverPlatformMap: [],
    );
  }

  public function hasPlatforms(): bool {
    return !empty($this->platformOptions);
  }

  public function hasServers(): bool {
    return !empty($this->serverOptions);
  }

}
