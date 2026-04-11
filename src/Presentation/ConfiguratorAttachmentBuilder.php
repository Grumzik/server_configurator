<?php

namespace Drupal\server_configurator\Presentation;

use Drupal\server_configurator\Context\PageContextFetcher;
use Drupal\server_configurator\Data\PlatformDataProvider;
use Drupal\server_configurator\Data\ServerDataProvider;

class ConfiguratorAttachmentBuilder {

  public function __construct(
    protected PageContextFetcher   $context,
    protected ServerDataProvider   $serverData,
    protected PlatformDataProvider $platformData,
  ) {}

  public function build(array &$attachments): void {
    if (!$this->context->shouldAttach()) {
      return;
    }

    $attachments['#attached']['library'][] = 'server_configurator/configurator';

    $attachments['#attached']['drupalSettings']['serverConfigurator'] = [
      'mode' => $this->context->getMode(),
      'server' => $this->serverData->getServerData(),
      'platform' => $this->platformData->getCurrentPlatformData(),
      'platformMap' => $this->platformData->getPlatformMap(),
    ];
  }

}
