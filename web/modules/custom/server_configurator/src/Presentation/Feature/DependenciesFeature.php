<?php

namespace Drupal\server_configurator\Presentation\Feature;

use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\Domain\Configurator\ConfiguratorDefinition;
use Drupal\server_configurator\Helpers\Form\FormElementLocator;
use Drupal\server_configurator\Presentation\Feature\Dependencies\DependenciesAjax;
use Drupal\server_configurator\Presentation\Feature\Dependencies\DependenciesElementNames;
use Drupal\server_configurator\Presentation\Feature\Dependencies\DependenciesOptionsProvider;

/**
 * Coordinates dependent elements in the main configurator form.
 */
final class DependenciesFeature {

  public function __construct(
    private readonly FormElementLocator $locator,
    private readonly DependenciesAjax $ajax,
    private readonly DependenciesOptionsProvider $optionsProvider,
  ) {}

  /**
   * Prepares dependent elements, attaches AJAX and applies current options.
   */
  public function alter(array &$form, FormStateInterface $formState, ConfiguratorDefinition $definition): void {
    $cpuVendorElement = &$this->locator->findElement($form, DependenciesElementNames::CPU_VENDOR);
    $cpuGenerationElement = &$this->locator->findElement($form, DependenciesElementNames::CPU_GENERATION);
    $platformElement = &$this->locator->findElement($form, DependenciesElementNames::PLATFORM);
    $serverElement = &$this->locator->findElement($form, DependenciesElementNames::SERVER);
    $formFactorElement = &$this->locator->findElement($form, DependenciesElementNames::FORM_FACTOR);
    $storageFormFactorElement = &$this->locator->findElement($form, DependenciesElementNames::STORAGE_FORM_FACTOR);

    if (
      $cpuVendorElement === NULL ||
      $cpuGenerationElement === NULL ||
      $platformElement === NULL ||
      $serverElement === NULL ||
      $formFactorElement === NULL ||
      $storageFormFactorElement === NULL
    ) {
      return;
    }



    $this->preparePlatformElement($platformElement);
    $this->prepareServerTableselect($serverElement);
      // Main configurator only: replace the computed_twig LAN placeholder with a
      // JS-updated placeholder. The small server configurator does not use this feature.
    $presetLanElement = &$this->locator->findElement($form, DependenciesElementNames::PRESET_LAN);
      if ($presetLanElement !== NULL) {
          $this->preparePresetLanElement($presetLanElement);
    }



    $this->ajax->attachAjax(
      $cpuVendorElement,
      $cpuGenerationElement,
      $platformElement,
      $serverElement,
      $formFactorElement,
      $storageFormFactorElement,
    );

    $this->applyOptions($formState, $cpuGenerationElement, $platformElement, $serverElement);
  }

  private function applyOptions(FormStateInterface $formState, array &$cpuGenerationElement, array &$platformElement, array &$serverElement): void {
    $triggerKey = $this->locator->getTriggerKey($formState);
    $selectedValues = $this->collectSelectedValues($formState);

    if ($triggerKey === DependenciesElementNames::CPU_VENDOR) {
      $this->locator->resetSelect($cpuGenerationElement);
      $this->locator->resetSelect($platformElement);
      $this->locator->resetSelect($serverElement);
      $selectedValues['cpu_generation'] = NULL;
    }

    if (in_array($triggerKey, [
      DependenciesElementNames::CPU_GENERATION,
      DependenciesElementNames::FORM_FACTOR,
      DependenciesElementNames::STORAGE_FORM_FACTOR,
    ], TRUE)) {
      $this->locator->resetSelect($platformElement);
      $this->locator->resetSelect($serverElement);
    }

    $result = $this->optionsProvider->buildOptions($selectedValues);

    $this->locator->setViewArguments(
      $cpuGenerationElement,
      $result->cpuGenerationArguments,
      $this->getCpuGenerationEmptyOption($selectedValues['cpu_vendor'] ?? NULL),
    );

    $platformElement['#options'] = $result->platformOptions;
    $platformElement['#value'] = $result->platformIds;

    $serverElement['#options'] = $result->serverOptions;
    $serverPlatformMapTest = $result->serverPlatformMap;


        \Drupal::logger('ServerPlatformMap')->notice('@data', ['@data'=>print_r($serverPlatformMapTest, TRUE)]);


    $serverElement['#attached']['drupalSettings']['serverConfigurator']['serverPlatformMap'] = $result->serverPlatformMap;
    $serverElement['#empty'] = $this->buildServerEmptyMessage($selectedValues, $result->hasServers());
  }

  private function collectSelectedValues(FormStateInterface $formState): array {
    return [
      'cpu_vendor' => $this->locator->getSubmittedValue($formState, DependenciesElementNames::CPU_VENDOR),
      'cpu_generation' => $this->locator->getSubmittedValue($formState, DependenciesElementNames::CPU_GENERATION),
      'form_factor' => $this->locator->getSubmittedValue($formState, DependenciesElementNames::FORM_FACTOR),
      'storage_form_factor' => $this->locator->getSubmittedValue($formState, DependenciesElementNames::STORAGE_FORM_FACTOR),
    ];
  }

  private function preparePlatformElement(array &$platformElement): void {
    $platformElement['#access'] = FALSE;
    $platformElement['#multiple'] = TRUE;
    $platformElement['#options'] = [];
    $platformElement['#empty_option'] = '- Совместимые платформы не найдены -';
  }

  private function preparePresetLanElement(array &$presetLanElement): void {
    // In the main configurator the platform is now derived from the selected server row.
    // Markup elements do not render #title reliably, so the visible text is placed directly
    // into #markup and only the LAN value itself is updated on the client side.
    $presetLanElement['#type'] = 'markup';
    $presetLanElement['#title'] = NULL;
    $presetLanElement['#markup'] = '<div class="server-configurator-lan-preset"><span class="lan-preset-label">В сервере установлен </span><label class="lan-preset js-lan-preset"></label></div>';
    $presetLanElement['#wrapper_attributes']['class'][] = 'server-configurator-lan-preset-wrapper';
  }

  private function prepareServerTableselect(array &$serverElement): void {
    $serverElement['#type'] = 'tableselect';
    $serverElement['#multiple'] = FALSE;
    $serverElement['#title'] = $serverElement['#title'] ?? 'Совместимые серверы';
    $serverElement['#header'] = [
      'title' => 'Сервер',
      'platform' => 'Платформа',
      'lan' => 'LAN',
      'max_drives' => 'Макс. кол-во накопителей',
      'storage_form_factor' => 'Форм-фактор накопителей',
    ];
    $serverElement['#options'] = [];
    $serverElement['#empty'] = $serverElement['#empty'] ?? 'Совместимые серверы не найдены';

    // Tableselect uses #empty, not #empty_option.
    unset($serverElement['#empty_option']);
  }

  private function getCpuGenerationEmptyOption(mixed $cpuVendorValue): string {
    return $this->locator->hasValue($cpuVendorValue)
      ? '- Выберите поколение процессора -'
      : '- Сначала выберите производителя -';
  }

  private function buildServerEmptyMessage(array $selectedValues, bool $hasServers): string {
    if (!$this->locator->hasValue($selectedValues['cpu_generation'] ?? NULL)) {
      return 'Сначала выберите поколение процессора';
    }

    if (!$this->locator->hasValue($selectedValues['form_factor'] ?? NULL)) {
      return 'Сначала выберите форм-фактор';
    }

    return $hasServers ? '' : 'Совместимые серверы не найдены';
  }

}
