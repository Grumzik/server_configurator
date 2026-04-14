<?php

namespace Drupal\server_configurator\Context;

use Drupal\server_configurator\Config\ConfiguratorDefinitions;

/**
 * Resolves the active configurator context from page or form.
 */
class ConfiguratorContextResolver {

  public function __construct(
    protected PageContextFetcher $pageContext,
  ) {}

  public function resolveFromPage(): ConfiguratorContext {
    if ($this->pageContext->isServerPage()) {
      return new ConfiguratorContext(TRUE, ConfiguratorDefinitions::get(ConfiguratorDefinitions::SERVER_CONFIGURATOR));
    }

    if ($this->pageContext->isMainConfiguratorPage()) {
      return new ConfiguratorContext(TRUE, ConfiguratorDefinitions::get(ConfiguratorDefinitions::MAIN_CONFIGURATOR));
    }

    return new ConfiguratorContext(FALSE, NULL);
  }

  public function resolveFromForm(array $form, string $form_id = ''): ConfiguratorContext {
    $webform_id = $this->extractWebformId($form);
    $definition = ConfiguratorDefinitions::getByWebformId($webform_id);

    if ($definition !== NULL) {
      return new ConfiguratorContext(TRUE, $definition);
    }

    return new ConfiguratorContext(FALSE, NULL);
  }

  protected function extractWebformId(array $form): ?string {
    $webform_id = $form['#webform_id'] ?? NULL;

    if (!$webform_id && isset($form['#webform']) && is_object($form['#webform']) && method_exists($form['#webform'], 'id')) {
      $webform_id = $form['#webform']->id();
    }

    return $webform_id ?: NULL;
  }

}
