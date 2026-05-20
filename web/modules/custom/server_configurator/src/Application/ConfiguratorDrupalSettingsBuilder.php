<?php

namespace Drupal\server_configurator\Application;

use Drupal\server_configurator\Application\Context\ConfiguratorContext;
use Drupal\server_configurator\Application\Context\PageContextFetcher;
use Drupal\server_configurator\Infrastructure\DataProvider\PlatformDataProvider;
use Drupal\server_configurator\Infrastructure\DataProvider\ServerDataProvider;

class ConfiguratorDrupalSettingsBuilder {

  public function __construct(
    protected PageContextFetcher $pageContext,
    protected ServerDataProvider $serverData,
    protected PlatformDataProvider $platformData,
  ) {}

  public function buildForCurrentServerPage(): array {
    return [
      'mode' => $this->pageContext->getMode(),
      'server' => $this->serverData->getServerData(),
      'platform' => $this->platformData->getCurrentPlatformData(),
      'platformMap' => $this->platformData->getPlatformMap(),
      'config' => [
        'features' => [],
        'dependencies' => [],
        'processors' => [],
        'summary' => [],
      ],
    ];
  }

  public function buildForConfigurator(ConfiguratorContext $context): array {
    $definition = $context->getDefinition();

    return [
      'mode' => $context->getMode(),
      'server' => $context->isServerConfigurator() ? $this->serverData->getServerData() : [],
      'platform' => $context->isServerConfigurator() ? $this->platformData->getCurrentPlatformData() : [],
      'platformMap' => $this->platformData->getPlatformMap(),
      'config' => $definition ? $definition->toFrontendConfig() : [
        'features' => [],
        'dependencies' => [],
        'processors' => [],
        'summary' => [],
      ],
    ];
  }

}
