<?php

/**
 * @file
 * Bootstrap helpers for Server Configurator forms.
 */

/**
 * Attach frontend library and drupalSettings to Main Configurator form.
 */
function server_configurator_attach_frontend_to_main_form(array &$form): void {
  $form['#attached']['library'][] = 'server_configurator/configurator';

  $platform_data = \Drupal::service('server_configurator.platform_data');

  $form['#attached']['drupalSettings']['serverConfigurator'] = [
    'mode' => 'main_configurator',
    'server' => [],
    'platform' => [],
    'platformMap' => $platform_data->getPlatformMap(),
  ];
}
