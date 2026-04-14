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
    ];
  }

  public function buildForConfigurator(ConfiguratorContext $context): array {
    if ($context->isServerConfigurator()) {
      return $this->buildForCurrentServerPage();
    }

    if ($context->isMainConfigurator()) {
      return [
        'mode' => $context->getMode(),
        'server' => [],
        'platform' => [],
        'platformMap' => $this->platformData->getPlatformMap(),
        'config' => [
          'summary_fields' => $context->getDefinition()?->getSummaryFields() ?? [],
        ],
      ];
    }

    return [];
  }

}
