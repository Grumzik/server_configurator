<?php

namespace Drupal\server_configurator\Domain\Configurator;

use Drupal\server_configurator\Domain\Configurator\Definition\MainConfiguratorDefinition;
use Drupal\server_configurator\Domain\Configurator\Definition\ServerConfiguratorDefinition;

/**
 * Registry of supported configurators.
 */
final class ConfiguratorDefinitions {

  public const MAIN_CONFIGURATOR = 'main_configurator';
  public const SERVER_CONFIGURATOR = 'server_configurator';
  public const UNKNOWN = 'unknown';

  public const FEATURE_SUMMARY = 'summary';
  public const FEATURE_STORAGE = 'storage';
  public const FEATURE_PROCESSORS = 'processors';
  public const FEATURE_DEPENDENCIES = 'dependencies';

  /**
   * @return array<string, \Drupal\server_configurator\Domain\Configurator\ConfiguratorDefinition>
   */
  public static function all(): array {
    return [
      self::MAIN_CONFIGURATOR => MainConfiguratorDefinition::create(),
      self::SERVER_CONFIGURATOR => ServerConfiguratorDefinition::create(),
    ];
  }

  public static function get(string $name): ?ConfiguratorDefinition {
    return self::all()[$name] ?? NULL;
  }

  public static function getByWebformId(?string $webformId): ?ConfiguratorDefinition {
    if (!$webformId) {
      return NULL;
    }

    foreach (self::all() as $definition) {
      if ($definition->getWebformId() === $webformId) {
        return $definition;
      }
    }

    return NULL;
  }

}
