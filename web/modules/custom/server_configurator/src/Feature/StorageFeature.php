<?php

namespace Drupal\server_configurator\Feature;

use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\DTO\ConfiguratorDefinition;

/**
 * Placeholder backend feature for storage-specific form alterations.
 */
class StorageFeature {

  public function alter(array &$form, FormStateInterface $form_state, ConfiguratorDefinition $definition): void {
    // Intentionally empty for now.
    // Storage runtime logic currently lives mostly in frontend JS.
  }

}
