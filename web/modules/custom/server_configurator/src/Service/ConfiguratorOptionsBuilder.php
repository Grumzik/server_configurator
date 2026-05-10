<?php

namespace Drupal\server_configurator\Service;

use Drupal\server_configurator\Repository\PlatformRepository;
use Drupal\server_configurator\Repository\ServerRepository;

/**
 * Builds options for the main configurator.
 */
final class ConfiguratorOptionsBuilder {

  public function __construct(
    private readonly PlatformRepository $platformRepository,
    private readonly ServerRepository $serverRepository,
  ) {}

  /**
   * Builds platform select options and server tableselect rows by selected filters.
   *
   * @return array{
   *   platform_ids:int[],
   *   platform_options:array<int,string>,
   *   server_options:array<int,array<string,string|int>>,
   *   server_platform_map:array<string,array<string,mixed>>
   * }
   */
  public function buildPlatformAndServerOptions(array $filters): array {
    $platformIds = $this->platformRepository->findCompatiblePlatformIds($filters);
    $servers = $this->serverRepository->findServersByPlatformIds($platformIds);

    return [
      'platform_ids' => $platformIds,
      'platform_options' => $this->platformRepository->buildOptionsByIds($platformIds),
      'server_options' => $this->serverRepository->buildTableOptions($servers),
      'server_platform_map' => $this->serverRepository->buildServerPlatformMap($servers),
    ];
  }

}
