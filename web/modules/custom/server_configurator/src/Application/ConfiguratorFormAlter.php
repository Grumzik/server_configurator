<?php

namespace Drupal\server_configurator\Application;

use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\Context\ConfiguratorContextResolver;
use Drupal\server_configurator\Feature\DependenciesFeature;
use Drupal\server_configurator\Feature\ProcessorsFeature;
use Drupal\server_configurator\Feature\StorageFeature;

/**
 * Single backend orchestration entrypoint for all configurator forms.
 */
class ConfiguratorFormAlter {

  public function __construct(
    protected ConfiguratorContextResolver $contextResolver,
    protected ConfiguratorBootstrapManager $bootstrapManager,
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

}
