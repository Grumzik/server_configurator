<?php

namespace Drupal\server_configurator\Feature;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\DTO\ConfiguratorDefinition;
use Drupal\server_configurator\Service\ConfiguratorFieldLocator;

class DependenciesFeature {

  public function __construct(
    protected ConfiguratorFieldLocator $locator,
  ) {}

  public function alter(array &$form, FormStateInterface $form_state, ConfiguratorDefinition $definition): void {
    $cpuVendorField = $definition->getField('cpu_vendor');
    $cpuGenerationField = $definition->getField('cpu_generation_select');
    $platformField = $definition->getField('platform');
    $formFactorField = $definition->getField('form_factor');
    $storageFormFactorField = $definition->getField('form_faktor_nakopiteley_v_dyuymah');

    $cpu_vendor_element = &$this->locator->findElement($form, $cpuVendorField);
    $cpu_generation_element = &$this->locator->findElement($form, $cpuGenerationField);
    $platform_element = &$this->locator->findElement($form, $platformField);
    $form_factor_element = &$this->locator->findElement($form, $formFactorField);
    $storage_form_factor_element = &$this->locator->findElement($form, $storageFormFactorField );

    if ($cpu_vendor_element === NULL || $cpu_generation_element === NULL || $platform_element === NULL || $form_factor_element === NULL || $storage_form_factor_element  === NULL) {
      return;
    }

    $this->attachAjax($cpu_vendor_element, $cpu_generation_element, $platform_element, $form_factor_element,  $storage_form_factor_element );
    $this->apply($form_state, $cpu_generation_element, $platform_element, $definition);
  }

  public function attachAjax(array &$cpu_vendor_element, array &$cpu_generation_element, array &$platform_element, array &$form_factor_element, array &$storage_form_factor_element): void {
    $this->locator->addWrapper($cpu_generation_element, 'cpu-generation-wrapper');
    $this->locator->addWrapper($platform_element, 'platform-wrapper');

    $cpu_vendor_element['#ajax'] = [
      'callback' => 'server_configurator_cpu_vendor_ajax_callback',
      'wrapper' => 'cpu-generation-wrapper',
      'event' => 'change',
    ];

    $cpu_generation_element['#ajax'] = [
      'callback' => 'server_configurator_cpu_generation_ajax_callback',
      'wrapper' => 'platform-wrapper',
      'event' => 'change',
    ];

    $form_factor_element['#ajax'] = [
      'callback' => 'server_configurator_form_factor_ajax_callback',
      'wrapper' => 'platform-wrapper',
      'event' => 'change',
    ];

    $storage_form_factor_element['#ajax'] = [
      'callback' => 'server_configurator_storage_form_factor_ajax_callback',
      'wrapper' => 'platform-wrapper',
      'event' => 'change',
    ];
  }

  public function apply(FormStateInterface $form_state, array &$cpu_generation_element, array &$platform_element, ConfiguratorDefinition $definition): void {
    $trigger_key = $this->locator->getTriggerKey($form_state);

    $vendor_value = $this->locator->getSubmittedValue($form_state, $definition->getField('cpu_vendor'));
    $cpu_generation_value = $this->locator->getSubmittedValue($form_state, $definition->getField('cpu_generation_select'));
    $form_factor_value = $this->locator->getSubmittedValue($form_state, $definition->getField('form_factor'));
    $storage_form_factor_value = $this->locator->getSubmittedValue($form_state, $definition->getField('form_faktor_nakopiteley_v_dyuymah'));


    $this->applyCpuGenerationDependency($cpu_generation_element, $vendor_value);

    if ($trigger_key === $definition->getField('cpu_vendor')) {
      $this->locator->resetSelect($cpu_generation_element);
      $cpu_generation_value = NULL;
    }

    if (in_array($trigger_key, [$definition->getField('cpu_generation_select'), $definition->getField('form_factor'), $definition->getField('cpu_vendor')], TRUE)) {
      $this->locator->resetSelect($platform_element);
    }

    $this->applyPlatformDependency($platform_element, $cpu_generation_value, $form_factor_value, $storage_form_factor_value);
  }

  public function cpuVendorAjaxCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $cpu_generation_element = &$this->locator->findElement($form, 'cpu_generation_entity_selection');
    $platform_element = &$this->locator->findElement($form, 'platform_entity_selection');

    if ($cpu_generation_element !== NULL) {
      $response->addCommand(new ReplaceCommand('#cpu-generation-wrapper', $cpu_generation_element));
    }

    if ($platform_element !== NULL) {
      $response->addCommand(new ReplaceCommand('#platform-wrapper', $platform_element));
    }

    return $response;
  }

  public function cpuGenerationAjaxCallback(array &$form, FormStateInterface $form_state) {
    $platform_element = &$this->locator->findElement($form, 'platform_entity_selection');
    return $platform_element !== NULL ? $platform_element : $form;
  }

  public function formFactorAjaxCallback(array &$form, FormStateInterface $form_state) {
    $platform_element = &$this->locator->findElement($form, 'platform_entity_selection');
    return $platform_element !== NULL ? $platform_element : $form;
  }

  public function storageFormFactorAjaxCallback(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('storageFormFactorAjaxCallback')->notice('storageFormFactorAjaxCallback');
    $platform_element = &$this->locator->findElement($form, 'platform_entity_selection');
    return $platform_element !== NULL ? $platform_element : $form;
  }

  protected function applyCpuGenerationDependency(array &$cpu_generation_element, $vendor_value): void {
    if ($this->locator->hasValue($vendor_value)) {
      $this->locator->setViewArguments($cpu_generation_element, [$vendor_value], '- Выберите поколение процессора -');
    }
    else {
      $this->locator->setViewArguments($cpu_generation_element, [-1], '- Сначала выберите производителя -');
    }
  }

  protected function applyPlatformDependency(array &$platform_element, $cpu_generation_value, $form_factor_value, $form_faktor_nakopiteley_v_dyuymah): void {

    if ($form_faktor_nakopiteley_v_dyuymah == 2.5) {$storage_form_factor = $form_faktor_nakopiteley_v_dyuymah;}
    else {$storage_form_factor = null;}

    if ($this->locator->hasValue($cpu_generation_value) && $this->locator->hasValue($form_factor_value)) {
      $this->locator->setViewArguments($platform_element, [$cpu_generation_value, $form_factor_value, $storage_form_factor ], '- Выберите платформу -');
    }
    elseif ($this->locator->hasValue($cpu_generation_value)) {
      $this->locator->setViewArguments($platform_element, [-1, -1], '- Сначала выберите форм-фактор -');
    }
    else {
      $this->locator->setViewArguments($platform_element, [-1, -1], '- Сначала выберите поколение процессора -');
    }
  }

}
