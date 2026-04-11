<?php

use Drupal\Core\Form\FormStateInterface;

/**
 * @file
 * Thin procedural wrappers for Drupal Form API callbacks.
 *
 * These wrappers are kept outside server_configurator.module so the module file
 * remains a thin hook entrypoint. Real logic lives in services/features.
 */

/**
 * AJAX callback wrapper for cpu_vendor.
 */
function server_configurator_cpu_vendor_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.main_dependencies')
    ->cpuVendorAjaxCallback($form, $form_state);
}

/**
 * AJAX callback wrapper for cpu_generation_entity_selection.
 */
function server_configurator_cpu_generation_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.main_dependencies')
    ->cpuGenerationAjaxCallback($form, $form_state);
}

/**
 * AJAX callback wrapper for form_factor_units.
 */
function server_configurator_form_factor_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.main_dependencies')
    ->formFactorAjaxCallback($form, $form_state);
}

/**
 * Submit handler wrapper for show processors.
 */
function server_configurator_show_processors_submit(array &$form, FormStateInterface $form_state): void {
  \Drupal::service('server_configurator.main_processors')
    ->showProcessorsSubmit($form, $form_state);
}

/**
 * Submit handler wrapper for reset processors.
 */
function server_configurator_reset_processors_submit(array &$form, FormStateInterface $form_state): void {
  \Drupal::service('server_configurator.main_processors')
    ->resetProcessorsSubmit($form, $form_state);
}

/**
 * AJAX callback wrapper for show processors.
 */
function server_configurator_show_processors_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.main_processors')
    ->showProcessorsAjaxCallback($form, $form_state);
}

/**
 * AJAX callback wrapper for reset processors.
 */
function server_configurator_reset_processors_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.main_processors')
    ->resetProcessorsAjaxCallback($form, $form_state);
}
