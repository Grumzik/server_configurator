<?php

namespace Drupal\server_configurator\Feature;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\server_configurator\Context\ConfiguratorContextResolver;
use Drupal\server_configurator\DTO\ConfiguratorDefinition;
use Drupal\server_configurator\Service\ConfiguratorFieldLocator;

class DependenciesFeature {

  public function __construct(
    protected ConfiguratorFieldLocator $locator,
    protected ConfiguratorContextResolver $contextResolver,
  ) {}

  public function alter(array &$form, FormStateInterface $form_state, ConfiguratorDefinition $definition): void {
    if (!$definition->hasDependencies()) {
      return;
    }

    $this->attachAjax($form, $definition);

    $trigger_key = $this->locator->getTriggerKey($form_state);
    if ($trigger_key) {
      $this->apply($form, $form_state, $definition);
    }
  }

  public function attachAjax(array &$form, ConfiguratorDefinition $definition): void {
    foreach ($definition->getDependencies() as $dependency) {
      $this->attachTargetWrapper($form, $definition, $dependency);
      $this->attachSourceAjax($form, $definition, $dependency);
    }
  }

  protected function attachTargetWrapper(array &$form, ConfiguratorDefinition $definition, array $dependency): void {
    $wrapper_id = $definition->getDependencyWrapperId($dependency);
    $target_element = &$this->getTargetElement($form, $definition, $dependency);

    if ($target_element !== NULL && $wrapper_id) {
      $this->locator->addWrapper($target_element, $wrapper_id);
    }
  }

  protected function attachSourceAjax(array &$form, ConfiguratorDefinition $definition, array $dependency): void {
    $callback = $definition->getDependencyAjaxCallback($dependency, 'server_configurator_dependency_ajax_callback');
    $wrapper_id = $definition->getDependencyWrapperId($dependency) ?: 'dependencies-wrapper';

    foreach ((array) ($dependency['source'] ?? []) as $source_alias) {
      $source_element = &$this->getSourceElement($form, $definition, $source_alias);

      if ($source_element === NULL) {
        continue;
      }

      $source_element['#ajax'] = [
        'callback' => $callback,
        'wrapper' => $wrapper_id,
        'event' => 'change',
      ];
    }
  }

  public function apply(array &$form, FormStateInterface $form_state, ConfiguratorDefinition $definition): void {
    $trigger_key = $this->locator->getTriggerKey($form_state);
    $values = $this->collectDependencyValues($form_state, $definition);

    $this->resetAffectedTargets($form, $trigger_key, $definition);

    foreach ($definition->getDependencies() as $dependency) {
      $type = $dependency['type'] ?? 'unknown';
      $target_element = &$this->getTargetElement($form, $definition, $dependency);

      if ($target_element === NULL) {
        continue;
      }

      switch ($type) {
        case 'cpu_generation_by_vendor':
          $vendor_alias = (string) ($definition->getDependencySource($type) ?? '');
          $this->applyCpuGenerationByVendor(
            $target_element,
            $values[$vendor_alias] ?? NULL
          );
          break;

        case 'platform_by_generation_and_form_factor':
          $sources = (array) ($definition->getDependencySource($type) ?? []);
          $cpu_generation_alias = $sources[0] ?? NULL;
          $form_factor_alias = $sources[1] ?? NULL;

          $this->applyPlatformByGenerationAndFormFactor(
            $target_element,
            $cpu_generation_alias ? ($values[$cpu_generation_alias] ?? NULL) : NULL,
            $form_factor_alias ? ($values[$form_factor_alias] ?? NULL) : NULL
          );
          break;
      }
    }
  }

  protected function collectDependencyValues(FormStateInterface $form_state, ConfiguratorDefinition $definition): array {
    $values = [];

    foreach ($definition->getDependencies() as $dependency) {
      foreach ((array) ($dependency['source'] ?? []) as $source_alias) {
        if (!array_key_exists($source_alias, $values)) {
          $field_name = $definition->getField($source_alias);
          $values[$source_alias] = $field_name
            ? $this->locator->getSubmittedValue($form_state, $field_name)
            : NULL;
        }
      }
    }

    return $values;
  }

  protected function resetAffectedTargets(array &$form, ?string $trigger_key, ConfiguratorDefinition $definition): void {
    if (!$trigger_key) {
      return;
    }

    foreach ($definition->getDependencies() as $dependency) {
      $sources = (array) ($dependency['source'] ?? []);

      $source_fields = [];
      foreach ($sources as $source_alias) {
        $source_field = $definition->getField($source_alias);
        if ($source_field) {
          $source_fields[] = $source_field;
        }
      }

      if (!in_array($trigger_key, $source_fields, TRUE)) {
        continue;
      }

      $target_element = &$this->getTargetElement($form, $definition, $dependency);

      if ($target_element !== NULL) {
        $this->locator->resetSelect($target_element);
      }
    }
  }

  public function dependencyAjaxCallback(array &$form, FormStateInterface $form_state) {
    $definition = $this->getDefinitionFromForm($form);

    if ($definition === NULL) {
      return $form;
    }

    $response = new AjaxResponse();

    foreach ($definition->getDependencies() as $dependency) {
      $wrapper_id = $definition->getDependencyWrapperId($dependency);
      $target_element = &$this->getTargetElement($form, $definition, $dependency);

      if ($target_element !== NULL && $wrapper_id) {
        $response->addCommand(new ReplaceCommand('#' . $wrapper_id, $target_element));
      }
    }

    return $response;
  }

  protected function applyCpuGenerationByVendor(array &$target_element, $vendor_value): void {
    if ($this->locator->hasValue($vendor_value)) {
      $this->locator->setViewArguments(
        $target_element,
        [$vendor_value],
        '- Выберите поколение процессора -'
      );
    }
    else {
      $this->locator->setViewArguments(
        $target_element,
        [-1],
        '- Сначала выберите производителя -'
      );
    }
  }

  protected function applyPlatformByGenerationAndFormFactor(array &$target_element, $cpu_generation_value, $form_factor_value): void {
    if ($this->locator->hasValue($cpu_generation_value) && $this->locator->hasValue($form_factor_value)) {
      $this->locator->setViewArguments(
        $target_element,
        [$cpu_generation_value, $form_factor_value],
        '- Выберите платформу -'
      );
    }
    elseif ($this->locator->hasValue($cpu_generation_value)) {
      $this->locator->setViewArguments(
        $target_element,
        [-1, -1],
        '- Сначала выберите форм-фактор -'
      );
    }
    else {
      $this->locator->setViewArguments(
        $target_element,
        [-1, -1],
        '- Сначала выберите поколение процессора -'
      );
    }
  }

  protected function &getTargetElement(array &$form, ConfiguratorDefinition $definition, array $dependency) {
    $target_alias = $dependency['target'] ?? NULL;
    $target_field = $target_alias ? $definition->getField($target_alias) : NULL;

    if (!$target_field) {
      $null = NULL;
      return $null;
    }

    $element = &$this->locator->findElement($form, $target_field);
    return $element;
  }

  protected function &getSourceElement(array &$form, ConfiguratorDefinition $definition, string $source_alias) {
    $source_field = $definition->getField($source_alias);

    if (!$source_field) {
      $null = NULL;
      return $null;
    }

    $element = &$this->locator->findElement($form, $source_field);
    return $element;
  }

  protected function getDefinitionFromForm(array $form): ?ConfiguratorDefinition {
    $context = $this->contextResolver->resolveFromForm($form);
    return $context->getDefinition();
  }

}
