<?php

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;

/**
 * @file
 * Processor browser logic for Main Configurator form.
 */

/**
 * Check whether processor view can be built safely.
 */
function server_configurator_can_build_processor_view(FormStateInterface $form_state): bool {
  $cpu_generation = server_configurator_get_submitted_value($form_state, 'cpu_generation_entity_selection');
  $processors_count = server_configurator_get_submitted_value($form_state, 'kolichestvo_processorov');

  return server_configurator_has_value($cpu_generation)
    && server_configurator_has_value($processors_count);
}

/**
 * Submit handler for "show processors".
 */
function server_configurator_show_processors_submit(array &$form, FormStateInterface $form_state): void {
  $form_state->setValue('show_processors', 1);

  $user_input = $form_state->getUserInput();
  $user_input['show_processors'] = 1;
  $form_state->setUserInput($user_input);

  $form_state->setRebuild(TRUE);
}

/**
 * Submit handler for "reset processors".
 */
function server_configurator_reset_processors_submit(array &$form, FormStateInterface $form_state): void {
  $form_state->setValue('show_processors', 0);
  $form_state->setValue('selected_processors_text', '');

  $user_input = $form_state->getUserInput();
  $user_input['show_processors'] = 0;
  $user_input['selected_processors_text'] = '';
  $form_state->setUserInput($user_input);

  $form_state->setRebuild(TRUE);
}

/**
 * Attach AJAX and submit handlers to processor buttons.
 */
function server_configurator_attach_main_form_processor_ajax(
  array &$show_processors_button,
  array &$reset_button
): void {
  $show_processors_button['#type'] = 'submit';
  $show_processors_button['#ajax'] = [
    'callback' => 'server_configurator_show_processors_ajax_callback',
    'wrapper' => 'processors-ajax-wrapper',
    'event' => 'click',
    'progress' => [
      'type' => 'throbber',
      'message' => t('Загружаем совместимые процессоры...'),
    ],
  ];
  $show_processors_button['#submit'][] = 'server_configurator_show_processors_submit';
  $show_processors_button['#limit_validation_errors'] = [];

  $reset_button['#type'] = 'submit';
  $reset_button['#ajax'] = [
    'callback' => 'server_configurator_reset_processors_ajax_callback',
    'wrapper' => 'processors-ajax-wrapper',
    'event' => 'click',
  ];
  $reset_button['#submit'][] = 'server_configurator_reset_processors_submit';
  $reset_button['#limit_validation_errors'] = [];
}

/**
 * Prepare processor wrapper and processor view during rebuild.
 */
function server_configurator_prepare_main_form_processor_view(
  array &$form,
  FormStateInterface $form_state,
  &$processor_wrapper,
  &$processor_view,
  &$show_processors_element
): void {
  if ($processor_wrapper !== NULL) {
    $processor_wrapper['#attributes']['id'] = 'processors-ajax-wrapper';
  }

  if ($processor_view !== NULL && !server_configurator_can_build_processor_view($form_state)) {
    $processor_view['#access'] = FALSE;
  }

  if ($show_processors_element !== NULL) {
    $show_value = server_configurator_get_submitted_value($form_state, 'show_processors');
    $show_processors_element['#default_value'] = server_configurator_has_value($show_value) ? (int) $show_value : 0;
  }
}

/**
 * AJAX callback for showing processors.
 */
function server_configurator_show_processors_ajax_callback(array &$form, FormStateInterface $form_state) {
  $response = new AjaxResponse();

  $wrapper = &server_configurator_get_processors_wrapper($form);

  if ($wrapper !== NULL) {
    $render_copy = $wrapper;
    $html = (string) \Drupal::service('renderer')->renderRoot($render_copy);
    $response->addCommand(new ReplaceCommand('#processors-ajax-wrapper', $html));
    return $response;
  }

  $response->addCommand(new ReplaceCommand(
    '#processors-ajax-wrapper',
    '<div id="processors-ajax-wrapper"></div>'
  ));

  return $response;
}

/**
 * AJAX callback for resetting processors block.
 */
function server_configurator_reset_processors_ajax_callback(array &$form, FormStateInterface $form_state) {
  $response = new AjaxResponse();

  $wrapper = &server_configurator_get_processors_wrapper($form);

  if ($wrapper !== NULL) {
    $render_copy = $wrapper;
    $html = (string) \Drupal::service('renderer')->renderRoot($render_copy);
    $response->addCommand(new ReplaceCommand('#processors-ajax-wrapper', $html));
    return $response;
  }

  $response->addCommand(new ReplaceCommand(
    '#processors-ajax-wrapper',
    '<div id="processors-ajax-wrapper"></div>'
  ));

  return $response;
}
