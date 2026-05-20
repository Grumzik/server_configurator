<?php

namespace Drupal\server_configurator\Presentation\Feature\Dependencies;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\Helpers\Form\FormElementLocator;

/**
 * AJAX behavior and callbacks for dependent configurator elements.
 */
final class DependenciesAjax {

  public function __construct(
    private readonly FormElementLocator $locator,
  ) {}

  /**
   * Adds wrappers and AJAX callbacks to dependent form elements.
   */
  public function attachAjax(array &$cpuVendorElement, array &$cpuGenerationElement, array &$platformElement, array &$serverElement, array &$formFactorElement, array &$storageFormFactorElement): void {
    $this->locator->addWrapper($cpuGenerationElement, 'cpu-generation-wrapper');
    $this->locator->addWrapper($platformElement, 'platform-wrapper');
    $this->locator->addWrapper($serverElement, 'server-wrapper');

    $cpuVendorElement['#ajax'] = [
      'callback' => 'server_configurator_cpu_vendor_ajax_callback',
      'event' => 'change',
    ];

    $cpuGenerationElement['#ajax'] = [
      'callback' => 'server_configurator_filters_ajax_callback',
      'event' => 'change',
    ];

    $formFactorElement['#ajax'] = [
      'callback' => 'server_configurator_filters_ajax_callback',
      'event' => 'change',
    ];

    $storageFormFactorElement['#ajax'] = [
      'callback' => 'server_configurator_filters_ajax_callback',
      'event' => 'change',
    ];

    // Usually platform_select is hidden, but this keeps the dependency working
    // if you temporarily make it visible for debugging.
    $platformElement['#ajax'] = [
      'callback' => 'server_configurator_platform_ajax_callback',
      'event' => 'change',
    ];
  }

  /**
   * Rebuilds CPU generation, platform and server after CPU vendor changes.
   */
  public function cpuVendorAjaxCallback(array &$form, FormStateInterface $formState): AjaxResponse {
    $response = new AjaxResponse();

    $this->replaceElement($response, $form, DependenciesElementNames::CPU_GENERATION, '#cpu-generation-wrapper');
    $this->replaceElement($response, $form, DependenciesElementNames::PLATFORM, '#platform-wrapper');
    $this->replaceElement($response, $form, DependenciesElementNames::SERVER, '#server-wrapper');

    return $response;
  }

  /**
   * Rebuilds platform and server after filters change.
   */
  public function filtersAjaxCallback(array &$form, FormStateInterface $formState): AjaxResponse {
    $response = new AjaxResponse();

    $this->replaceElement($response, $form, DependenciesElementNames::PLATFORM, '#platform-wrapper');
    $this->replaceElement($response, $form, DependenciesElementNames::SERVER, '#server-wrapper');

    return $response;
  }

  /**
   * Rebuilds server list after selected platform changes.
   */
  public function platformAjaxCallback(array &$form, FormStateInterface $formState): AjaxResponse {
    $response = new AjaxResponse();

    $this->replaceElement($response, $form, DependenciesElementNames::SERVER, '#server-wrapper');

    return $response;
  }

  /**
   * Backward-compatible method for old procedural wrappers.
   */
  public function cpuGenerationAjaxCallback(array &$form, FormStateInterface $formState): AjaxResponse {
    return $this->filtersAjaxCallback($form, $formState);
  }

  /**
   * Backward-compatible method for old procedural wrappers.
   */
  public function formFactorAjaxCallback(array &$form, FormStateInterface $formState): AjaxResponse {
    return $this->filtersAjaxCallback($form, $formState);
  }

  /**
   * Backward-compatible method for old procedural wrappers.
   */
  public function storageFormFactorAjaxCallback(array &$form, FormStateInterface $formState): AjaxResponse {
    return $this->filtersAjaxCallback($form, $formState);
  }

  private function replaceElement(AjaxResponse $response, array &$form, string $elementName, string $selector): void {
    $element = &$this->locator->findElement($form, $elementName);

    if ($element !== NULL) {
      $response->addCommand(new ReplaceCommand($selector, $element));
    }
  }

}
