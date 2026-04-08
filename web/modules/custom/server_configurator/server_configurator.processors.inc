<?php

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;

function server_configurator_can_build_processor_view(FormStateInterface $form_state): bool {
  $cpu_generation = server_configurator_get_submitted_value($form_state, 'cpu_generation_entity_selection');
  $processors_count = server_configurator_get_submitted_value($form_state, 'kolichestvo_processorov');

  return server_configurator_has_value($cpu_generation)
    && server_configurator_has_value($processors_count);
}

function server_configurator_show_processors_submit(array &$form, FormStateInterface $form_state): void {
  $form_state->setValue('show_processors', 1);

  $user_input = $form_state->getUserInput();
  $user_input['show_processors'] = 1;
  $form_state->setUserInput($user_input);

  $form_state->setRebuild(TRUE);
}

function server_configurator_reset_processors_submit(array &$form, FormStateInterface $form_state): void {
  $form_state->setValue('show_processors', 0);
  $form_state->setValue('selected_processors_text', '');

  $user_input = $form_state->getUserInput();
  $user_input['show_processors'] = 0;
  $user_input['selected_processors_text'] = '';
  $form_state->setUserInput($user_input);

  $form_state->setRebuild(TRUE);
}

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
