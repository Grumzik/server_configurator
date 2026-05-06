<?php

namespace Drupal\server_configurator\Feature;

use Drupal\server_configurator\DTO\ConfiguratorDefinition;

class SummaryFeature {

  /**
   * @return array<int, array{field:string,label:string,format?:string}>
   */
  public function getSummaryFields(ConfiguratorDefinition $definition): array {
    return $definition->getSummaryFields();
  }

}
