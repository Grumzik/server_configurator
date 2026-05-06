<?php

namespace Drupal\server_configurator\Application;

use Drupal\server_configurator\Context\ConfiguratorContext;
use Drupal\server_configurator\Context\PageContextFetcher;
use Drupal\server_configurator\Data\PlatformDataProvider;
use Drupal\server_configurator\Data\ServerDataProvider;

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
        'fields' => [],
        'summary_fields' => [],
        'processors' => [],
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
        'fields' => [],
        'summary_fields' => [],
        'processors' => [],
      ],
    ];
  }

}
