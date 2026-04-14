<?php

namespace Drupal\server_configurator\Feature;

use Drupal\server_configurator\DTO\ConfiguratorDefinition;

/**
 * Placeholder summary feature.
 */
class SummaryFeature {

  public function getSummaryFields(ConfiguratorDefinition $definition): array {
    return $definition->getSummaryFields();
  }

}
