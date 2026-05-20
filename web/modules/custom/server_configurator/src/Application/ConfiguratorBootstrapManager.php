<?php

namespace Drupal\server_configurator\Application;

use Drupal\server_configurator\Application\Context\ConfiguratorContext;
use Drupal\server_configurator\Application\Context\ConfiguratorContextResolver;

class ConfiguratorBootstrapManager {

  public function __construct(
    protected ConfiguratorContextResolver $contextResolver,
    protected ConfiguratorDrupalSettingsBuilder $settingsBuilder,
  ) {}

  public function attachToPage(array &$attachments, ?ConfiguratorContext $context = NULL): void {
    $context = $context ?: $this->contextResolver->resolveFromPage();

    if (!$context->shouldAttachFrontend()) {
      return;
    }

    $attachments['#attached']['library'][] = 'server_configurator/configurator';
    $settings = $this->settingsBuilder->buildForConfigurator($context);
    if ($settings) {
      $attachments['#attached']['drupalSettings']['serverConfigurator'] = $settings;
    }
  }

  public function attachToForm(array &$form, ?ConfiguratorContext $context = NULL): void {
    $context = $context ?: $this->contextResolver->resolveFromForm($form);

    if (!$context->shouldAttachFrontend()) {
      return;
    }

    $form['#attached']['library'][] = 'server_configurator/configurator';
    $settings = $this->settingsBuilder->buildForConfigurator($context);
    if ($settings) {
      $form['#attached']['drupalSettings']['serverConfigurator'] = $settings;
    }
  }

}
