<?php

namespace Drupal\server_configurator\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\Service\ConfiguratorBootstrapManager;
use Drupal\server_configurator\Service\ConfiguratorFieldLocator;

class MainConfiguratorFormAlter {

  public function __construct(
    protected ConfiguratorBootstrapManager $bootstrapManager,
    protected ConfiguratorFieldLocator $locator,
    protected MainConfiguratorDependencyFeature $dependencyFeature,
    protected MainConfiguratorProcessorFeature $processorFeature,
  ) {}

  public function alter(array &$form, FormStateInterface $form_state): void {
    $this->bootstrapManager->attachToMainForm($form);

    $cpu_vendor_element = &$this->locator->findElement($form, 'cpu_vendor');
    $cpu_generation_element = &$this->locator->findElement($form, 'cpu_generation_entity_selection');
    $platform_element = &$this->locator->findElement($form, 'platform_entity_selection');
    $form_factor_element = &$this->locator->findElement($form, 'form_factor_units');
    $show_processors_button = &$this->locator->findElement($form, 'show_processors_button');
    $reset_button = &$this->locator->findElement($form, 'my_reset');
    $show_processors_element = &$this->locator->findElement($form, 'show_processors');
    $processor_wrapper = &$this->locator->getProcessorsWrapper($form);
    $processor_view = &$this->locator->getProcessorView($form);

    if (
      $cpu_vendor_element === NULL ||
      $cpu_generation_element === NULL ||
      $platform_element === NULL ||
      $form_factor_element === NULL
    ) {
      return;
    }

    $this->dependencyFeature->attachAjax(
      $cpu_vendor_element,
      $cpu_generation_element,
      $platform_element,
      $form_factor_element
    );

    if ($show_processors_button !== NULL && $reset_button !== NULL) {
      $this->processorFeature->attachAjax(
        $show_processors_button,
        $reset_button
      );
    }

    $this->dependencyFeature->apply(
      $form_state,
      $cpu_generation_element,
      $platform_element
    );

    $this->processorFeature->prepareView(
      $form,
      $form_state,
      $processor_wrapper,
      $processor_view,
      $show_processors_element
    );

    $form_state->setRebuild(TRUE);
  }

}
