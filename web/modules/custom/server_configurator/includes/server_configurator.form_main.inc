<?php

use Drupal\Core\Form\FormStateInterface;

/**
 * @file
 * Main form orchestration for Main Server Configurator.
 */

/**
 * Alter Main Configurator form.
 */
function server_configurator_alter_main_configurator_form(array &$form, FormStateInterface $form_state): void {
//  server_configurator_attach_frontend_to_main_form($form);

  $cpu_vendor_element = &server_configurator_find_element($form, 'cpu_vendor');
  $cpu_generation_element = &server_configurator_find_element($form, 'cpu_generation_entity_selection');
  $platform_element = &server_configurator_find_element($form, 'platform_entity_selection');
  $form_factor_element = &server_configurator_find_element($form, 'form_factor_units');
  $show_processors_button = &server_configurator_find_element($form, 'show_processors_button');
  $reset_button = &server_configurator_find_element($form, 'my_reset');
  $show_processors_element = &server_configurator_find_element($form, 'show_processors');
  $processor_wrapper = &server_configurator_get_processors_wrapper($form);
  $processor_view = &server_configurator_get_processor_view($form);

  if (
    $cpu_vendor_element === NULL ||
    $cpu_generation_element === NULL ||
    $platform_element === NULL ||
    $form_factor_element === NULL
  ) {
    return;
  }

  server_configurator_attach_main_form_dependency_ajax(
    $cpu_vendor_element,
    $cpu_generation_element,
    $platform_element,
    $form_factor_element
  );

  if ($show_processors_button !== NULL && $reset_button !== NULL) {
    server_configurator_attach_main_form_processor_ajax(
      $show_processors_button,
      $reset_button
    );
  }

  server_configurator_apply_main_form_dependencies(
    $form_state,
    $cpu_generation_element,
    $platform_element
  );

  server_configurator_prepare_main_form_processor_view(
    $form,
    $form_state,
    $processor_wrapper,
    $processor_view,
    $show_processors_element
  );

  $form_state->setRebuild(TRUE);
}
