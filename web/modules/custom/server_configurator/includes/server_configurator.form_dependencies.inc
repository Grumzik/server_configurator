<?php

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;

/**
 * @file
 * Dependent field logic for Main Configurator form.
 */

/**
 * Apply dependency: cpu_vendor -> cpu_generation_entity_selection.
 */
function server_configurator_apply_cpu_generation_dependency(array &$cpu_generation_element, $vendor_value): void {
  if (server_configurator_has_value($vendor_value)) {
    server_configurator_set_view_arguments(
      $cpu_generation_element,
      [$vendor_value],
      '- Выберите поколение процессора -'
    );
  }
  else {
    server_configurator_set_view_arguments(
      $cpu_generation_element,
      [-1],
      '- Сначала выберите производителя -'
    );
  }
}

/**
 * Apply dependency: cpu_generation + form_factor -> platform.
 */
function server_configurator_apply_platform_dependency(array &$platform_element, $cpu_generation_value, $form_factor_value): void {
  if (server_configurator_has_value($cpu_generation_value) && server_configurator_has_value($form_factor_value)) {
    server_configurator_set_view_arguments(
      $platform_element,
      [$cpu_generation_value, $form_factor_value],
      '- Выберите платформу -'
    );
  }
  elseif (server_configurator_has_value($cpu_generation_value)) {
    server_configurator_set_view_arguments(
      $platform_element,
      [-1, -1],
      '- Сначала выберите форм-фактор -'
    );
  }
  else {
    server_configurator_set_view_arguments(
      $platform_element,
      [-1, -1],
      '- Сначала выберите поколение процессора -'
    );
  }
}

/**
 * Attach wrappers and AJAX to dependency elements.
 */
function server_configurator_attach_main_form_dependency_ajax(
  array &$cpu_vendor_element,
  array &$cpu_generation_element,
  array &$platform_element,
  array &$form_factor_element
): void {
  server_configurator_add_wrapper($cpu_generation_element, 'cpu-generation-wrapper');
  server_configurator_add_wrapper($platform_element, 'platform-wrapper');

  $cpu_vendor_element['#ajax'] = [
    'callback' => 'server_configurator_cpu_vendor_ajax_callback',
    'wrapper' => 'cpu-generation-wrapper',
    'event' => 'change',
  ];

  $cpu_generation_element['#ajax'] = [
    'callback' => 'server_configurator_cpu_generation_ajax_callback',
    'wrapper' => 'platform-wrapper',
    'event' => 'change',
  ];

  $form_factor_element['#ajax'] = [
    'callback' => 'server_configurator_form_factor_ajax_callback',
    'wrapper' => 'platform-wrapper',
    'event' => 'change',
  ];
}

/**
 * Apply dependency logic during form rebuild.
 */
function server_configurator_apply_main_form_dependencies(
  FormStateInterface $form_state,
  array &$cpu_generation_element,
  array &$platform_element
): void {
  $trigger_key = server_configurator_get_trigger_key($form_state);

  $vendor_value = server_configurator_get_submitted_value($form_state, 'cpu_vendor');
  $cpu_generation_value = server_configurator_get_submitted_value($form_state, 'cpu_generation_entity_selection');
  $form_factor_value = server_configurator_get_submitted_value($form_state, 'form_factor_units');

  server_configurator_apply_cpu_generation_dependency($cpu_generation_element, $vendor_value);

  if ($trigger_key === 'cpu_vendor') {
    server_configurator_reset_select($cpu_generation_element);
    $cpu_generation_value = NULL;
  }

  if (in_array($trigger_key, ['cpu_generation_entity_selection', 'form_factor_units', 'cpu_vendor'], TRUE)) {
    server_configurator_reset_select($platform_element);
  }

  server_configurator_apply_platform_dependency(
    $platform_element,
    $cpu_generation_value,
    $form_factor_value
  );
}

/**
 * AJAX callback for cpu_vendor.
 */
function server_configurator_cpu_vendor_ajax_callback(array &$form, FormStateInterface $form_state) {
  $response = new AjaxResponse();

  $cpu_generation_element = &server_configurator_find_element($form, 'cpu_generation_entity_selection');
  $platform_element = &server_configurator_find_element($form, 'platform_entity_selection');

  if ($cpu_generation_element !== NULL) {
    $response->addCommand(new ReplaceCommand('#cpu-generation-wrapper', $cpu_generation_element));
  }

  if ($platform_element !== NULL) {
    $response->addCommand(new ReplaceCommand('#platform-wrapper', $platform_element));
  }

  return $response;
}

/**
 * AJAX callback for cpu_generation.
 */
function server_configurator_cpu_generation_ajax_callback(array &$form, FormStateInterface $form_state) {
  $platform_element = &server_configurator_find_element($form, 'platform_entity_selection');

  if ($platform_element !== NULL) {
    return $platform_element;
  }

  return $form;
}

/**
 * AJAX callback for form_factor.
 */
function server_configurator_form_factor_ajax_callback(array &$form, FormStateInterface $form_state) {
  $platform_element = &server_configurator_find_element($form, 'platform_entity_selection');

  if ($platform_element !== NULL) {
    return $platform_element;
  }

  return $form;
}
