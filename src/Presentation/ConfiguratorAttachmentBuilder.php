<?php
//Presentation (Drupal-side)
namespace Drupal\server_configurator\Presentation;

use Drupal\server_configurator\Context\PageContext;
use Drupal\server_configurator\Data\ServerDataProvider;
use Drupal\server_configurator\Data\PlatformDataProvider;
class ConfiguratorAttachmentBuilder {

  public function __construct(
    protected PageContext $context,
    protected ServerDataProvider $serverData,
    protected PlatformDataProvider $platformData,
  ) {}

  public function build(array &$attachments): void {
    if (!$this->context->isServerPage()) {
      return;
    }

    $attachments['#attached']['library'][] = 'server_configurator/configurator';

    $attachments['#attached']['drupalSettings']['serverConfigurator'] = [
      'platform' => $this->platformData->getPlatformData(),
      'server'   => $this->serverData->getServerData(),
    ];
  }
}




