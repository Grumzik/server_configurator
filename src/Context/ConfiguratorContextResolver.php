<?php

namespace Drupal\server_configurator\Context;

use Drupal\server_configurator\Config\ConfiguratorDefinitions;

//Определяет какой  конфигуратор сейчас работает и его режим работы  -
// Возвращает объект ConfiguratorContext
class ConfiguratorContextResolver {

  public function __construct(
    protected PageContextFetcher $pageContext,
  ) {}


  public function resolveFromPage(): ConfiguratorContext {
    if ($this->pageContext->isServerPage()) {
      return new ConfiguratorContext(
        ConfiguratorDefinitions::SERVER_PAGE,
        TRUE,
        ConfiguratorDefinitions::featureMap()[ConfiguratorDefinitions::SERVER_PAGE]
      );
    }

    if ($this->pageContext->isMainConfiguratorPage()) {
      return new ConfiguratorContext(
        ConfiguratorDefinitions::MAIN_CONFIGURATOR,
        TRUE,
        ConfiguratorDefinitions::featureMap()[ConfiguratorDefinitions::MAIN_CONFIGURATOR]
      );
    }

    return new ConfiguratorContext(
      ConfiguratorDefinitions::UNKNOWN,
      FALSE,
      ConfiguratorDefinitions::featureMap()[ConfiguratorDefinitions::UNKNOWN]
    );
  }

  public function resolveFromWebformId(?string $webform_id): ConfiguratorContext {
    if (!$webform_id) {
      return new ConfiguratorContext(
        ConfiguratorDefinitions::UNKNOWN,
        FALSE,
        ConfiguratorDefinitions::featureMap()[ConfiguratorDefinitions::UNKNOWN]
      );
    }

    foreach (ConfiguratorDefinitions::webformIds() as $mode => $webform_ids) {
      if (in_array($webform_id, $webform_ids, TRUE)) {
        return new ConfiguratorContext(
          $mode,
          TRUE,
          ConfiguratorDefinitions::featureMap()[$mode] ?? []
        );
      }
    }

    return new ConfiguratorContext(
      ConfiguratorDefinitions::UNKNOWN,
      FALSE,
      ConfiguratorDefinitions::featureMap()[ConfiguratorDefinitions::UNKNOWN]
    );
  }

}
