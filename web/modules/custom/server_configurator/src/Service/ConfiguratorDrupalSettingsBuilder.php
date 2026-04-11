<?php

namespace Drupal\server_configurator\Service;

use Drupal\server_configurator\Context\PageContextFetcher;
use Drupal\server_configurator\Data\PlatformDataProvider;
use Drupal\server_configurator\Data\ServerDataProvider;

class ConfiguratorDrupalSettingsBuilder {

  public function __construct(
    protected PageContextFetcher   $pageContext,
    protected ServerDataProvider   $serverData,
    protected PlatformDataProvider $platformData,
  ) {}

  /**
   * Build frontend settings for current server page configurator.
   */
  public function buildForCurrentServerPage(): array {
    return [
      'mode' => $this->pageContext->getMode(),
      'server' => $this->serverData->getServerData(),
      'platform' => $this->platformData->getCurrentPlatformData(),
      'platformMap' => $this->platformData->getPlatformMap(),
    ];
  }

  /**
   * Build frontend settings for Main Configurator form.
   */
  public function buildForMainConfiguratorForm(): array {
    return [
      'mode' => 'main_configurator',
      'server' => [],
      'platform' => [],
      'platformMap' => $this->platformData->getPlatformMap(),
    ];
  }

}
