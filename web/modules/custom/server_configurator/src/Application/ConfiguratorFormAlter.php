<?php

namespace Drupal\server_configurator\Application;

use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\Application\Context\ConfiguratorContextResolver;
use Drupal\server_configurator\Infrastructure\DataProvider\PlatformDataProvider;
use Drupal\server_configurator\Presentation\Feature\DependenciesFeature;
use Drupal\server_configurator\Presentation\Feature\ProcessorsFeature;
use Drupal\server_configurator\Presentation\Feature\StorageFeature;

/**
 * Single backend orchestration entrypoint for all configurator forms.
 */
class ConfiguratorFormAlter {

  public function __construct(
    protected ConfiguratorContextResolver $contextResolver,
    protected ConfiguratorBootstrapManager $bootstrapManager,
    protected PlatformDataProvider $platformDataProvider,
    protected DependenciesFeature $dependenciesFeature,
    protected ProcessorsFeature $processorsFeature,
    protected StorageFeature $storageFeature,
  ) {}

  public function alter(array &$form, FormStateInterface $form_state, string $form_id): void {
    $context = $this->contextResolver->resolveFromForm($form, $form_id);

    if (!$context->isConfigurator()) {
      return;
    }

    $definition = $context->getDefinition();
    if ($definition === NULL) {
      return;
    }
    

    $this->bootstrapManager->attachToForm($form, $context);

    if ($context->isServerConfigurator()) {
      $this->applyServerConfiguratorProcessorCountLimit($form, $form_state);
    }

    if ($context->hasFeature('dependencies')) {
      $this->dependenciesFeature->alter($form, $form_state, $definition);
    }

    if ($context->hasFeature('processors')) {
      $this->processorsFeature->alter($form, $form_state, $definition);
    }

    if ($context->hasFeature('storage')) {
      $this->storageFeature->alter($form, $form_state, $definition);
    }

    $form_state->setRebuild(TRUE);
  }


  /**
   * On a concrete server page the platform is already known.
   *
   * If the platform has only one CPU socket, the small configurator must always
   * use exactly one processor and must not allow the visitor to change it.
   */
  protected function applyServerConfiguratorProcessorCountLimit(array &$form, FormStateInterface $form_state): void {
    $platform = $this->platformDataProvider->getCurrentPlatformData();
    $socket_count = (int) ($platform['cpu_socket_count'] ?? 0);

    if ($socket_count !== 1) {
      return;
    }

    $element = &$this->findElement($form, 'kolichestvo_processorov');
    if ($element === NULL) {
      return;
    }

    $element['#default_value'] = 1;
    $element['#value'] = 1;
    $element['#access'] = FALSE;

    $form_state->setValue('kolichestvo_processorov', 1);

    $user_input = $form_state->getUserInput();
    $user_input['kolichestvo_processorov'] = 1;
    $form_state->setUserInput($user_input);
  }

  /**
   * Recursively find a form element by key and return it by reference.
   */
  protected function &findElement(array &$array, string $target_key) {
    foreach ($array as $key => &$value) {
      if ($key === $target_key) {
        return $value;
      }

      if (is_array($value)) {
        $found = &$this->findElement($value, $target_key);
        if ($found !== NULL) {
          return $found;
        }
      }
    }

    $null = NULL;
    return $null;
  }

}
