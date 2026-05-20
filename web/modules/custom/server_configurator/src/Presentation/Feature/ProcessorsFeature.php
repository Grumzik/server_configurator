<?php

namespace Drupal\server_configurator\Presentation\Feature;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\server_configurator\Application\Context\ConfiguratorContextResolver;
use Drupal\server_configurator\Domain\Configurator\ConfiguratorDefinition;
use Drupal\server_configurator\Helpers\Form\FormElementLocator;

/**
 * Shared processors feature for all configurators.
 *
 * Reset defaults are read from ConfiguratorDefinition.
 */
class ProcessorsFeature {

  public function __construct(
    protected FormElementLocator $locator,
    protected RendererInterface $renderer,
    protected ConfiguratorContextResolver $contextResolver,
  ) {}

  private const RESOLVED_CPU_GENERATION_FIELD = 'cpu_generation_twig';
  private const PROCESSORS_COUNT_FIELD = 'kolichestvo_processorov';
  private const SHOW_PROCESSORS_FIELD = 'show_processors';
  private const SELECTED_PROCESSORS_TEXT_FIELD = 'selected_processors_text';
  private const SHOW_PROCESSORS_BUTTON = 'show_processors_button';
  private const RESET_PROCESSORS_BUTTON = 'reset_processors_button';
  private const PROCESSORS_VIEW = 'processor_for_server';
  private const PROCESSORS_WRAPPER = 'processors_ajax_wrapper';

  public function alter(array &$form, FormStateInterface $form_state, ConfiguratorDefinition $definition): void {
    $show_button = &$this->locator->findElement($form, self::SHOW_PROCESSORS_BUTTON);
    $reset_button = &$this->locator->findElement($form, self::RESET_PROCESSORS_BUTTON);
    $show_element = &$this->locator->findElement($form, self::SHOW_PROCESSORS_FIELD);
    $processors_count_element = &$this->locator->findElement($form, self::PROCESSORS_COUNT_FIELD);
    $wrapper = &$this->locator->findElement($form, self::PROCESSORS_WRAPPER);
    $view = &$this->locator->findElement($form, self::PROCESSORS_VIEW);

    if ($show_button !== NULL && $reset_button !== NULL) {
      $this->attachAjax($show_button, $reset_button);
    }

    if ($processors_count_element !== NULL) {
      $this->attachProcessorsCountAjax($processors_count_element);
    }

    $this->prepareView($form, $form_state, $wrapper, $view, $show_element, $definition);
  }

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



  /**
   * When the visitor changes the requested CPU count, previously displayed
   * processor rows are no longer guaranteed to be compatible.
   */
  public function attachProcessorsCountAjax(array &$processors_count_element): void {
    $processors_count_element['#ajax'] = [
      'callback' => 'server_configurator_processors_count_ajax_callback',
      'wrapper' => 'processors-ajax-wrapper',
      'event' => 'change',
      'progress' => [
        'type' => 'throbber',
        'message' => t('Сбрасываем список совместимых процессоров...'),
      ],
    ];
    $processors_count_element['#limit_validation_errors'] = [];
  }

  public function prepareView(
    array &$form,
    FormStateInterface $form_state,
    &$processor_wrapper,
    &$processor_view,
    &$show_processors_element,
    ConfiguratorDefinition $definition
  ): void {
    if ($processor_wrapper !== NULL) {
      $processor_wrapper['#attributes']['id'] = 'processors-ajax-wrapper';
    }

    if ($processor_view !== NULL && !$this->canBuildProcessorView($form_state, $definition)) {
      $processor_view['#access'] = FALSE;
    }

    if ($show_processors_element !== NULL) {
      $show_key = self::SHOW_PROCESSORS_FIELD;
      $show_value = $this->locator->getSubmittedValue($form_state, $show_key);
      $show_processors_element['#default_value'] = $this->locator->hasValue($show_value) ? (int) $show_value : 0;
    }
  }

  public function showProcessorsSubmit(array &$form, FormStateInterface $form_state): void {
    $definition = $this->getDefinitionFromForm($form);
    if ($definition === NULL) {
      return;
    }

    $showKey = self::SHOW_PROCESSORS_FIELD;
    $form_state->setValue($showKey, 1);

    $user_input = $form_state->getUserInput();
    $user_input[$showKey] = 1;
    $form_state->setUserInput($user_input);
    $form_state->setRebuild(TRUE);
  }

  public function resetProcessorsSubmit(array &$form, FormStateInterface $form_state): void {
    $definition = $this->getDefinitionFromForm($form);
    if ($definition === NULL) {
      return;
    }

    $showKey = self::SHOW_PROCESSORS_FIELD;
    $selectedTextKey = self::SELECTED_PROCESSORS_TEXT_FIELD;

    $form_state->setValue($showKey, 0);
    $form_state->setValue($selectedTextKey, '');

    $user_input = $form_state->getUserInput();
    $user_input[$showKey] = 0;
    $user_input[$selectedTextKey] = '';

    foreach (['frequency_min', 'frequency_max', 'cores_min', 'cores_max'] as $filterKey) {
      $fieldName = $definition->getProcessorsFilterField($filterKey);
      $defaultValue = $definition->getProcessorsDefault($filterKey);

      if ($fieldName !== NULL) {
        $form_state->setValue($fieldName, $defaultValue);
        $user_input[$fieldName] = $defaultValue;
      }
    }

    $form_state->setUserInput($user_input);
    $form_state->setRebuild(TRUE);
  }



  public function processorsCountAjaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
    $showKey = self::SHOW_PROCESSORS_FIELD;
    $selectedTextKey = self::SELECTED_PROCESSORS_TEXT_FIELD;

    $form_state->setValue($showKey, 0);
    $form_state->setValue($selectedTextKey, '');

    $user_input = $form_state->getUserInput();
    $user_input[$showKey] = 0;
    $user_input[$selectedTextKey] = '';
    $form_state->setUserInput($user_input);
    $form_state->setRebuild(TRUE);

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#processors-ajax-wrapper', '<div id="processors-ajax-wrapper"></div>'));
    $response->addCommand(new InvokeCommand('[name="' . $showKey . '"]', 'val', [0]));
    $response->addCommand(new InvokeCommand('[name="' . $selectedTextKey . '"]', 'val', ['']));
    return $response;
  }

  public function showProcessorsAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $this->renderAjaxCallbackWrapperResponse($form);
  }

  public function resetProcessorsAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $this->renderAjaxCallbackWrapperResponse($form);
  }

  public function canBuildProcessorView(FormStateInterface $form_state, ConfiguratorDefinition $definition): bool {
    $show_processors = $this->locator->getSubmittedValue($form_state, self::SHOW_PROCESSORS_FIELD);
    $cpu_generation = $this->getResolvedCpuGenerationValue($form_state, $definition);
    $processors_count = $this->locator->getSubmittedValue($form_state, self::PROCESSORS_COUNT_FIELD);

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

  /**
   * Read the canonical CPU generation field declared by current configurator.
   */
  protected function getResolvedCpuGenerationValue(FormStateInterface $form_state, ConfiguratorDefinition $definition) {
    $fieldName = self::RESOLVED_CPU_GENERATION_FIELD;

    if (!$fieldName) {
      return NULL;
    }

    return $this->locator->getSubmittedValue($form_state, $fieldName);
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

    $response->addCommand(new ReplaceCommand('#processors-ajax-wrapper', '<div id="processors-ajax-wrapper"></div>'));
    return $response;
  }

  protected function getDefinitionFromForm(array $form): ?ConfiguratorDefinition {
    $context = $this->contextResolver->resolveFromForm($form);
    return $context->getDefinition();
  }

}
