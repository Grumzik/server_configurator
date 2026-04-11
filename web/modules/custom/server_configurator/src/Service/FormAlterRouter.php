<?php

namespace Drupal\server_configurator\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\Context\ConfiguratorContextResolver;
use Drupal\server_configurator\Form\MainConfiguratorFormAlter;

class FormAlterRouter {

  public function __construct(
    protected ConfiguratorContextResolver $contextResolver,
    protected MainConfiguratorFormAlter $mainConfiguratorFormAlter,
  ) {}

  public function alter(array &$form, FormStateInterface $form_state, string $form_id): void {
    $webform_id = $form['#webform_id'] ?? NULL;

    if (!$webform_id && isset($form['#webform']) && is_object($form['#webform']) && method_exists($form['#webform'], 'id')) {
      $webform_id = $form['#webform']->id();
    }

    $context = $this->contextResolver->resolveFromWebformId($webform_id);

    if ($context->isMainConfigurator()) {
      $this->mainConfiguratorFormAlter->alter($form, $form_state);
    }
  }

}
