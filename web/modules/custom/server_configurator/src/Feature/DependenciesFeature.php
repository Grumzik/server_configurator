<?php

namespace Drupal\server_configurator\Feature;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\DTO\ConfiguratorDefinition;
use Drupal\server_configurator\Service\ConfiguratorFieldLocator;
use Drupal\server_configurator\Service\ConfiguratorOptionsBuilder;

class DependenciesFeature {

  public function __construct(
    protected ConfiguratorFieldLocator $locator,
    protected ConfiguratorOptionsBuilder $optionsBuilder,
  ) {}

  public function alter(array &$form, FormStateInterface $form_state, ConfiguratorDefinition $definition): void {
    $cpuVendorField = $definition->getField('cpu_vendor');
    $cpuGenerationField = $definition->getField('cpu_generation_select');
    $platformField = $definition->getField('platform_select');
    $serverField = $definition->getField('server_select');
    $formFactorField = $definition->getField('form_factor');
    $storageFormFactorField = $definition->getField('form_faktor_nakopiteley_v_dyuymah');

    // If at least one field name is not configured, do not call findElement().
    // This protects the form from the "null given" TypeError.
    foreach ([$cpuVendorField, $cpuGenerationField, $platformField, $serverField, $formFactorField, $storageFormFactorField] as $fieldName) {
      if (!is_string($fieldName) || $fieldName === '') {
        return;
      }
    }

    $cpu_vendor_element = &$this->locator->findElement($form, $cpuVendorField);
    $cpu_generation_element = &$this->locator->findElement($form, $cpuGenerationField);
    $platform_element = &$this->locator->findElement($form, $platformField);
    $server_element = &$this->locator->findElement($form, $serverField);
    $form_factor_element = &$this->locator->findElement($form, $formFactorField);
    $storage_form_factor_element = &$this->locator->findElement($form, $storageFormFactorField);

    if (
      $cpu_vendor_element === NULL ||
      $cpu_generation_element === NULL ||
      $platform_element === NULL ||
      $server_element === NULL ||
      $form_factor_element === NULL ||
      $storage_form_factor_element === NULL
    ) {
      return;
    }

    // The old entity_select fields may still exist in the webform YAML. Hide them so
    // they do not confuse the user or summary logic while the new select elements are used.
    $legacy_platform_element = &$this->locator->findElement($form, $definition->getField('platform', ''));
    if ($legacy_platform_element !== NULL) {
      $legacy_platform_element['#access'] = FALSE;
    }
    $legacy_server_element = &$this->locator->findElement($form, $definition->getField('server', ''));
    if ($legacy_server_element !== NULL) {
      $legacy_server_element['#access'] = FALSE;
    }

    $this->attachAjax(
      $cpu_vendor_element,
      $cpu_generation_element,
      $platform_element,
      $server_element,
      $form_factor_element,
      $storage_form_factor_element,
    );

    $this->apply($form_state, $cpu_generation_element, $platform_element, $server_element, $definition);
  }

  public function attachAjax(array &$cpu_vendor_element, array &$cpu_generation_element, array &$platform_element, array &$server_element, array &$form_factor_element, array &$storage_form_factor_element): void {
    $this->locator->addWrapper($cpu_generation_element, 'cpu-generation-wrapper');
    $this->locator->addWrapper($platform_element, 'platform-wrapper');
    $this->locator->addWrapper($server_element, 'server-wrapper');

    $cpu_vendor_element['#ajax'] = [
      'callback' => 'server_configurator_cpu_vendor_ajax_callback',
      'event' => 'change',
    ];

    $cpu_generation_element['#ajax'] = [
      'callback' => 'server_configurator_filters_ajax_callback',
      'event' => 'change',
    ];

    $form_factor_element['#ajax'] = [
      'callback' => 'server_configurator_filters_ajax_callback',
      'event' => 'change',
    ];

    $storage_form_factor_element['#ajax'] = [
      'callback' => 'server_configurator_filters_ajax_callback',
      'event' => 'change',
    ];

    // Usually platform_select is hidden, but this keeps the dependency working if
    // you temporarily make it visible for debugging.
    $platform_element['#ajax'] = [
      'callback' => 'server_configurator_platform_ajax_callback',
      'event' => 'change',
    ];
  }

  public function apply(FormStateInterface $form_state, array &$cpu_generation_element, array &$platform_element, array &$server_element, ConfiguratorDefinition $definition): void {
    $trigger_key = $this->locator->getTriggerKey($form_state);

    $vendor_value = $this->locator->getSubmittedValue($form_state, $definition->getField('cpu_vendor'));
    $cpu_generation_value = $this->locator->getSubmittedValue($form_state, $definition->getField('cpu_generation_select'));
    $form_factor_value = $this->locator->getSubmittedValue($form_state, $definition->getField('form_factor'));
    $storage_form_factor_value = $this->locator->getSubmittedValue($form_state, $definition->getField('form_faktor_nakopiteley_v_dyuymah'));

    $this->applyCpuGenerationDependency($cpu_generation_element, $vendor_value);

    if ($trigger_key === $definition->getField('cpu_vendor')) {
      $this->locator->resetSelect($cpu_generation_element);
      $this->locator->resetSelect($platform_element);
      $this->locator->resetSelect($server_element);
      $cpu_generation_value = NULL;
    }

    if (in_array($trigger_key, [
      $definition->getField('cpu_generation_select'),
      $definition->getField('form_factor'),
      $definition->getField('form_faktor_nakopiteley_v_dyuymah'),
    ], TRUE)) {
      $this->locator->resetSelect($platform_element);
      $this->locator->resetSelect($server_element);
    }

    $this->applyPlatformAndServerDependencies(
      $platform_element,
      $server_element,
      $cpu_generation_value,
      $form_factor_value,
      $storage_form_factor_value,
    );
  }

  public function cpuVendorAjaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    $cpu_generation_element = &$this->locator->findElement($form, 'cpu_generation_entity_selection');
    $platform_element = &$this->locator->findElement($form, 'platform_select');
    $server_element = &$this->locator->findElement($form, 'server_select');

    if ($cpu_generation_element !== NULL) {
      $response->addCommand(new ReplaceCommand('#cpu-generation-wrapper', $cpu_generation_element));
    }
    if ($platform_element !== NULL) {
      $response->addCommand(new ReplaceCommand('#platform-wrapper', $platform_element));
    }
    if ($server_element !== NULL) {
      $response->addCommand(new ReplaceCommand('#server-wrapper', $server_element));
    }

    return $response;
  }

  /**
   * Common AJAX callback for CPU generation, form factor and storage form factor.
   */
  public function filtersAjaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    $platform_element = &$this->locator->findElement($form, 'platform_select');
    $server_element = &$this->locator->findElement($form, 'server_select');

    if ($platform_element !== NULL) {
      $response->addCommand(new ReplaceCommand('#platform-wrapper', $platform_element));
    }
    if ($server_element !== NULL) {
      $response->addCommand(new ReplaceCommand('#server-wrapper', $server_element));
    }

    return $response;
  }

  public function platformAjaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    $server_element = &$this->locator->findElement($form, 'server_select');
    if ($server_element !== NULL) {
      $response->addCommand(new ReplaceCommand('#server-wrapper', $server_element));
    }

    return $response;
  }

  // Backward-compatible methods for old procedural wrappers.
  public function cpuGenerationAjaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
    return $this->filtersAjaxCallback($form, $form_state);
  }

  public function formFactorAjaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
    return $this->filtersAjaxCallback($form, $form_state);
  }

  public function storageFormFactorAjaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
    return $this->filtersAjaxCallback($form, $form_state);
  }

  protected function applyCpuGenerationDependency(array &$cpu_generation_element, $vendor_value): void {
    if ($this->locator->hasValue($vendor_value)) {
      $this->locator->setViewArguments($cpu_generation_element, [$vendor_value], '- Выберите поколение процессора -');
    }
    else {
      $this->locator->setViewArguments($cpu_generation_element, [-1], '- Сначала выберите производителя -');
    }
  }

  protected function prepareServerTableselect(array &$server_element): void {
    $server_element['#type'] = 'tableselect';
    $server_element['#multiple'] = FALSE;
    $server_element['#title'] = $server_element['#title'] ?? 'Совместимые серверы';
    $server_element['#header'] = [
      'title' => 'Сервер',
      'platform' => 'Платформа',
      'max_drives' => 'Макс. кол-во накопителей',
    ];
    $server_element['#empty'] = $server_element['#empty'] ?? 'Совместимые серверы не найдены';

    // Tableselect uses #empty, not #empty_option.
    unset($server_element['#empty_option']);
  }

  protected function applyPlatformAndServerDependencies(array &$platform_element, array &$server_element, $cpu_generation_value, $form_factor_value, $storage_form_factor_value): void {
    $platform_element['#access'] = FALSE;
    $platform_element['#multiple'] = TRUE;
    $platform_element['#options'] = [];
    $platform_element['#empty_option'] = '- Совместимые платформы не найдены -';

    $this->prepareServerTableselect($server_element);
    $server_element['#options'] = [];
    $server_element['#empty'] = 'Сначала выберите поколение процессора и форм-фактор';

    if (!$this->locator->hasValue($cpu_generation_value)) {
      $server_element['#empty'] = 'Сначала выберите поколение процессора';
      return;
    }

    if (!$this->locator->hasValue($form_factor_value)) {
      $server_element['#empty'] = 'Сначала выберите форм-фактор';
      return;
    }

    $filters = [
      'cpu_generation' => $cpu_generation_value,
      'form_factor' => $form_factor_value,
      'storage_form_factor' => $this->normalizeStorageFormFactor($storage_form_factor_value),
    ];

    $result = $this->optionsBuilder->buildPlatformAndServerOptions($filters);

    $platform_element['#options'] = $result['platform_options'];
    $platform_element['#value'] = $result['platform_ids'];

    $server_element['#options'] = $result['server_options'];
    $server_element['#attached']['drupalSettings']['serverConfigurator']['serverPlatformMap'] = $result['server_platform_map'] ?? [];
    $server_element['#empty'] = empty($result['server_options'])
      ? 'Совместимые серверы не найдены'
      : '';
  }

  protected function normalizeStorageFormFactor($storage_form_factor_value): ?string {
    // Business rule from the old platform View arguments:
    // 2.5 means filter by 2.5; 3.5 means do not add storage form-factor filter,
    // because the old label was "3.5\"/2.5''".
    return ((string) $storage_form_factor_value === '2.5') ? '2.5' : NULL;
  }

}
