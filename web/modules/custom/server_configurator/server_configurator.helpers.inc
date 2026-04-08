<?php

use Drupal\Core\Form\FormStateInterface;

function &server_configurator_find_element(array &$array, string $target_key) {
  foreach ($array as $key => &$value) {
    if ($key === $target_key) {
      return $value;
    }
    if (is_array($value)) {
      $found = &server_configurator_find_element($value, $target_key);
      if ($found !== NULL) {
        return $found;
      }
    }
  }
  $null = NULL;
  return $null;
}

function server_configurator_find_path(array $array, string $target_key, array $path = []): ?array {
  foreach ($array as $key => $value) {
    $current_path = array_merge($path, [$key]);
    if ($key === $target_key) {
      return $current_path;
    }
    if (is_array($value)) {
      $found = server_configurator_find_path($value, $target_key, $current_path);
      if ($found !== NULL) {
        return $found;
      }
    }
  }
  return NULL;
}

function &server_configurator_get_by_path(array &$array, array $path) {
  $ref = &$array;
  foreach ($path as $segment) {
    if (!is_array($ref) || !array_key_exists($segment, $ref)) {
      $null = NULL;
      return $null;
    }
    $ref = &$ref[$segment];
  }
  return $ref;
}

function server_configurator_get_processor_view_path(array $form): ?array {
  return server_configurator_find_path($form, 'processor_for_server');
}

function server_configurator_get_processors_wrapper_path(array $form): ?array {
  $processor_path = server_configurator_get_processor_view_path($form);
  if (!$processor_path || count($processor_path) < 2) {
    return NULL;
  }
  array_pop($processor_path);
  return $processor_path;
}

function &server_configurator_get_processor_view(array &$form) {
  $path = server_configurator_get_processor_view_path($form);
  if ($path !== NULL) {
    return server_configurator_get_by_path($form, $path);
  }
  $null = NULL;
  return $null;
}

function &server_configurator_get_processors_wrapper(array &$form) {
  $path = server_configurator_get_processors_wrapper_path($form);
  if ($path !== NULL) {
    return server_configurator_get_by_path($form, $path);
  }
  $null = NULL;
  return $null;
}

function server_configurator_get_submitted_value(FormStateInterface $form_state, string $key) {
  $value = $form_state->getValue($key);
  if (!empty($value) || $value === '0' || $value === 0) {
    return $value;
  }
  $user_input = $form_state->getUserInput();
  if (isset($user_input[$key])) {
    return $user_input[$key];
  }
  return NULL;
}

function server_configurator_get_trigger_key(FormStateInterface $form_state): ?string {
  $trigger = $form_state->getTriggeringElement();
  if (!$trigger || !is_array($trigger)) {
    return NULL;
  }
  if (!empty($trigger['#webform_key'])) {
    return $trigger['#webform_key'];
  }
  if (!empty($trigger['#name'])) {
    return $trigger['#name'];
  }
  return NULL;
}

function server_configurator_add_wrapper(array &$element, string $wrapper_id): void {
  $element['#prefix'] = '<div id="' . $wrapper_id . '">';
  $element['#suffix'] = '</div>';
}

function server_configurator_set_view_arguments(array &$element, array $arguments, string $empty_option): void {
  $element['#selection_settings']['view']['arguments'] = $arguments;
  $element['#empty_option'] = $empty_option;
}

function server_configurator_reset_select(array &$element): void {
  $element['#default_value'] = NULL;
}

function server_configurator_has_value($value): bool {
  return !empty($value) || $value === '0' || $value === 0;
}
