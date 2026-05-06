<?php

use Drupal\Core\Form\FormStateInterface;

/**
 * Thin procedural wrappers for Drupal Form API callbacks.
 */

function server_configurator_cpu_vendor_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.dependencies_feature')
    ->cpuVendorAjaxCallback($form, $form_state);
}

function server_configurator_cpu_generation_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.dependencies_feature')
    ->cpuGenerationAjaxCallback($form, $form_state);
}

function server_configurator_form_factor_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.dependencies_feature')
    ->formFactorAjaxCallback($form, $form_state);
}

function server_configurator_storage_form_factor_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.dependencies_feature')
    ->storageFormFactorAjaxCallback($form, $form_state);
}

function server_configurator_show_processors_submit(array &$form, FormStateInterface $form_state): void {
  \Drupal::service('server_configurator.processors_feature')
    ->showProcessorsSubmit($form, $form_state);
}

function server_configurator_reset_processors_submit(array &$form, FormStateInterface $form_state): void {
  \Drupal::service('server_configurator.processors_feature')
    ->resetProcessorsSubmit($form, $form_state);
}

function server_configurator_show_processors_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.processors_feature')
    ->showProcessorsAjaxCallback($form, $form_state);
}

function server_configurator_reset_processors_ajax_callback(array &$form, FormStateInterface $form_state) {
  return \Drupal::service('server_configurator.processors_feature')
    ->resetProcessorsAjaxCallback($form, $form_state);
}
