<?php

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;

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

function server_configurator_apply_platform_dependency(array &$platform_element, $cpu_generation_value, $form_factor_units_value): void {
  if (server_configurator_has_value($cpu_generation_value) && server_configurator_has_value($form_factor_units_value)) {
    server_configurator_set_view_arguments(
      $platform_element,
      [$cpu_generation_value, $form_factor_units_value],
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

function server_configurator_cpu_generation_ajax_callback(array &$form, FormStateInterface $form_state) {
  $platform_element = &server_configurator_find_element($form, 'platform_entity_selection');
  if ($platform_element !== NULL) {
    return $platform_element;
  }
  return $form;
}

function server_configurator_form_factor_ajax_callback(array &$form, FormStateInterface $form_state) {
  $platform_element = &server_configurator_find_element($form, 'platform_entity_selection');
  if ($platform_element !== NULL) {
    return $platform_element;
  }
  return $form;
}
