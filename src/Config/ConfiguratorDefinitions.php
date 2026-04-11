<?php

namespace Drupal\server_configurator\Config;

// Справочник констант и  конфигурации.
final class ConfiguratorDefinitions {

  public const SERVER_PAGE = 'server_page';
  public const MAIN_CONFIGURATOR = 'main_configurator';
  public const UNKNOWN = 'unknown';

  public const FEATURE_SUMMARY = 'summary';
  public const FEATURE_STORAGE = 'storage';
  public const FEATURE_PROCESSORS = 'processors';
  public const FEATURE_DEPENDENCIES = 'dependencies';

  /**
   * Return known form IDs grouped by configurator mode.
   */
  public static function webformIds(): array {
    return [
      self::MAIN_CONFIGURATOR => [
         'main_server_configurator',
      ],
    ];
  }

  /**
   * Return backend/frontend feature map per configurator mode.
   */
  public static function featureMap(): array {
    return [
      self::SERVER_PAGE => [
        self::FEATURE_SUMMARY => TRUE,
        self::FEATURE_STORAGE => TRUE,
        self::FEATURE_PROCESSORS => TRUE,
        self::FEATURE_DEPENDENCIES => FALSE,
      ],
      self::MAIN_CONFIGURATOR => [
        self::FEATURE_SUMMARY => TRUE,
        self::FEATURE_STORAGE => TRUE,
        self::FEATURE_PROCESSORS => TRUE,
        self::FEATURE_DEPENDENCIES => TRUE,
      ],
      self::UNKNOWN => [],
    ];
  }

}
