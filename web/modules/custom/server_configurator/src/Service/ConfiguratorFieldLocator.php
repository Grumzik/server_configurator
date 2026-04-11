<?php

namespace Drupal\server_configurator\Service;

use Drupal\Core\Form\FormStateInterface;

class ConfiguratorFieldLocator {

  /**
   * Recursively find a form element by key and return it by reference.
   */
  public function &findElement(array &$array, string $target_key) {
    foreach ($array as $key => &$value) {
      if ($key === $target_key) {
        return $value;
      }

      if (is_array($value)) {
        $found = &$this->findElement($value, $target_key);
        if ($found !== NULL) {
          return $found;
        }
      }
    }

    $null = NULL;
    return $null;
  }

  /**
   * Recursively find a path to a form element by key.
   */
  public function findPath(array $array, string $target_key, array $path = []): ?array {
    foreach ($array as $key => $value) {
      $current_path = array_merge($path, [$key]);
      if ($key === $target_key) {
        return $current_path;
      }
      if (is_array($value)) {
        $found = $this->findPath($value, $target_key, $current_path);
        if ($found !== NULL) {
          return $found;
        }
      }
    }
    return NULL;
  }

  /**
   * Get array item by path and return it by reference.
   */
  public function &getByPath(array &$array, array $path) {
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

  public function getProcessorViewPath(array $form): ?array {
    return $this->findPath($form, 'processor_for_server');
  }

  public function getProcessorsWrapperPath(array $form): ?array {
    $processor_path = $this->getProcessorViewPath($form);
    if (!$processor_path || count($processor_path) < 2) {
      return NULL;
    }
    array_pop($processor_path);
    return $processor_path;
  }

  public function &getProcessorView(array &$form) {
    $path = $this->getProcessorViewPath($form);
    if ($path !== NULL) {
      return $this->getByPath($form, $path);
    }
    $null = NULL;
    return $null;
  }

  public function &getProcessorsWrapper(array &$form) {
    $path = $this->getProcessorsWrapperPath($form);
    if ($path !== NULL) {
      return $this->getByPath($form, $path);
    }
    $null = NULL;
    return $null;
  }

  public function getSubmittedValue(FormStateInterface $form_state, string $key) {
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

  public function getTriggerKey(FormStateInterface $form_state): ?string {
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

  public function addWrapper(array &$element, string $wrapper_id): void {
    $element['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['#suffix'] = '</div>';
  }

  public function setViewArguments(array &$element, array $arguments, string $empty_option): void {
    $element['#selection_settings']['view']['arguments'] = $arguments;
    $element['#empty_option'] = $empty_option;
  }

  public function resetSelect(array &$element): void {
    $element['#default_value'] = NULL;
  }

  public function hasValue($value): bool {
    return !empty($value) || $value === '0' || $value === 0;
  }

}
