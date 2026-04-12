<?php

namespace Drupal\server_configurator\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\server_configurator\Service\ConfiguratorFieldLocator;

class MainConfiguratorProcessorFeature {

  public function __construct(
    protected ConfiguratorFieldLocator $locator,
    protected RendererInterface $renderer,
  ) {}

  public function attachAjax(array &$show_processors_button, array &$reset_button): void {
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

  public function prepareView(
    array &$form,
    FormStateInterface $form_state,
    &$processor_wrapper,
    &$processor_view,
    &$show_processors_element
  ): void {
    if ($processor_wrapper !== NULL) {
      $processor_wrapper['#attributes']['id'] = 'processors-ajax-wrapper';
    }

    if ($processor_view !== NULL && !$this->canBuildProcessorView($form_state)) {
      $processor_view['#access'] = FALSE;
    }

    if ($show_processors_element !== NULL) {
      $show_value = $this->locator->getSubmittedValue($form_state, 'show_processors');
      $show_processors_element['#default_value'] = $this->locator->hasValue($show_value) ? (int) $show_value : 0;
    }
  }

  public function showProcessorsSubmit(array &$form, FormStateInterface $form_state): void {
    $form_state->setValue('show_processors', 1);

    $user_input = $form_state->getUserInput();
    $user_input['show_processors'] = 1;
    $form_state->setUserInput($user_input);
    $form_state->setRebuild(TRUE);
  }

  public function resetProcessorsSubmit(array &$form, FormStateInterface $form_state): void {
    $form_state->setValue('show_processors', 0);
    $form_state->setValue('selected_processors_text', '');

    $user_input = $form_state->getUserInput();
    $user_input['show_processors'] = 0;
    $user_input['selected_processors_text'] = '';
    $form_state->setUserInput($user_input);
    \Drupal::logger('resetProcessorsSubmit')->notice( $form_state->getValue('show_processors'));
    $form_state->setRebuild(TRUE);
  }

  public function showProcessorsAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $this->renderAjaxCallbackWrapperResponse($form);
  }

  public function resetProcessorsAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $this->renderAjaxCallbackWrapperResponse($form);
  }

  public function canBuildProcessorView(FormStateInterface $form_state): bool {
    $show_processors = $this->locator->getSubmittedValue($form_state, 'show_processors');
    $cpu_generation = $this->locator->getSubmittedValue($form_state, 'cpu_generation_entity_selection');
    $processors_count = $this->locator->getSubmittedValue($form_state, 'kolichestvo_processorov');

    if ((string) $show_processors !== '1') {
      return FALSE;
    }

    if (!$this->locator->hasValue($cpu_generation)) {
      return FALSE;
    }

    if (!$this->locator->hasValue($processors_count)) {
      return FALSE;
    }

    return TRUE;
  }

  protected function renderAjaxCallbackWrapperResponse(array &$form): AjaxResponse {
    $response = new AjaxResponse();
    $wrapper = &$this->locator->getProcessorsWrapper($form);

    if ($wrapper !== NULL) {
      $render_copy = $wrapper;
      $html = (string) $this->renderer->renderRoot($render_copy);
      $response->addCommand(new ReplaceCommand('#processors-ajax-wrapper', $html));
      return $response;
    }

    $response->addCommand(new ReplaceCommand(
      '#processors-ajax-wrapper',
      '<div id="processors-ajax-wrapper"></div>'
    ));

    return $response;
  }

}
