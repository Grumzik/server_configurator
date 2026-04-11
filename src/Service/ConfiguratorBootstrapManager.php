<?php

namespace Drupal\server_configurator\Service;

use Drupal\server_configurator\Context\ConfiguratorContext;
use Drupal\server_configurator\Context\ConfiguratorContextResolver;

class ConfiguratorBootstrapManager {

  public function __construct(
    protected ConfiguratorContextResolver $contextResolver,
    protected ConfiguratorDrupalSettingsBuilder $settingsBuilder,
  ) {}

  /**
   * Attach frontend assets and settings to current page when needed.
   */
  public function attachToPage(array &$attachments, ?ConfiguratorContext $context = NULL): void {
    $context = $context ?: $this->contextResolver->resolveFromPage();

    if (!$context->shouldAttachFrontend()) {
      return;
    }

    $attachments['#attached']['library'][] = 'server_configurator/configurator';

    if ($context->isServerPage()) {
      $attachments['#attached']['drupalSettings']['serverConfigurator'] = $this->settingsBuilder->buildForCurrentServerPage();
      return;
    }

    if ($context->isMainConfigurator()) {
      $attachments['#attached']['drupalSettings']['serverConfigurator'] = $this->settingsBuilder->buildForMainConfiguratorForm();
    }
  }

  /**
   * Attach frontend assets and settings to Main Configurator form.
   */
  public function attachToMainForm(array &$form, ?ConfiguratorContext $context = NULL): void {
    $context = $context ?: $this->contextResolver->resolveFromWebformId($this->extractWebformId($form));

    if (!$context->shouldAttachFrontend()) {
      return;
    }

    $form['#attached']['library'][] = 'server_configurator/configurator';
    $form['#attached']['drupalSettings']['serverConfigurator'] = $this->settingsBuilder->buildForMainConfiguratorForm();
  }

  /**
   * Extract stable Webform ID from a built form.
   */
  protected function extractWebformId(array $form): ?string {
    $webform_id = $form['#webform_id'] ?? NULL;

    if (!$webform_id && isset($form['#webform']) && is_object($form['#webform']) && method_exists($form['#webform'], 'id')) {
      $webform_id = $form['#webform']->id();
    }

    return $webform_id ?: NULL;
  }

}
