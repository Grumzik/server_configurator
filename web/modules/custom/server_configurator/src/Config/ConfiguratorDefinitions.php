<?php

namespace Drupal\server_configurator\Config;

use Drupal\server_configurator\DTO\ConfiguratorDefinition;

/**
 * Central declarative registry of supported configurators.
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
   * @return array<string, \Drupal\server_configurator\DTO\ConfiguratorDefinition>
   */
  public static function all(): array {
    return [
      self::MAIN_CONFIGURATOR => new ConfiguratorDefinition(
        name: self::MAIN_CONFIGURATOR,
        label: 'Main Server Configurator',
        webformId: 'main_server_configurator',
        features: [
          self::FEATURE_SUMMARY => TRUE,
          self::FEATURE_STORAGE => TRUE,
          self::FEATURE_PROCESSORS => TRUE,
          self::FEATURE_DEPENDENCIES => TRUE,
        ],
        fields: [
          'form_faktor' => 'form_faktor',
          'form_factor_units' => 'form_factor_units',
          'form_faktor_nakopiteley_v_dyuymah' => 'form_faktor_nakopiteley_v_dyuymah',
          'resolved_cpu_generation' => 'cpu_generation_twig',
          'processors_count' => 'kolichestvo_processorov',
          'show_processors' => 'show_processors',
          'selected_processors_text' => 'selected_processors_text',
          'show_processors_button' => 'show_processors_button',
          'reset_processors_button' => 'my_reset',
          'processors_view' => 'processor_for_server',
          'processors_wrapper' => 'processors_ajax_wrapper',
          'cpu_vendor' => 'cpu_vendor',
          'cpu_generation_select' => 'cpu_generation_entity_selection',
          'platform_select' => 'platform_select',
          'server_select' => 'server_select',
          // Legacy fields can still exist in the webform YAML, but are no longer used
          // by the main configurator dependency logic.
          'platform' => 'platform_entity_selection',
          'server' => 'server',
          'form_factor' => 'form_factor_units',
        ],
        dependencies: [
          [
            'type' => 'cpu_generation_by_vendor',
            'source' => 'cpu_vendor',
            'target' => 'cpu_generation_select',
          ],
          [
            'type' => 'platform_by_generation_and_form_factor',
            'source' => ['cpu_generation_select', 'form_factor', 'form_faktor_nakopiteley_v_dyuymah'],
            'target' => 'platform_select',
          ],
          [
            'type' => 'server_by_platform',
            'source' => ['platform_select'],
            'target' => 'server_select',
          ],
        ],
        summaryFields: [
          ['field' => 'form_faktor', 'label' => 'Форм-фактор'],
          ['field' => 'form_factor_units', 'label' => 'Высота в юнитах'],
          ['field' => 'form_faktor_nakopiteley_v_dyuymah', 'label' => 'Форм-фактор накопителей в дюймах'],
          ['field' => 'nalichie_otkazoustoychivogo_bloka_pitaniya', 'label' => 'Наличие отказоустойчивого блока питания'],
          ['field' => 'cpu_vendor', 'label' => 'Производитель процессора'],
          ['field' => 'cpu_generation_entity_selection', 'label' => 'Поколение процессора'],
          ['field' => 'platform_select', 'label' => 'Платформа'],
          ['field' => 'server_select', 'label' => 'Сервер'],
          ['field' => 'form_factor_units', 'label' => 'Форм-фактор'],
          ['field' => 'kolichestvo_processorov', 'label' => 'Количество процессоров'],
          ['field' => 'obem_operativnoy_pamyati_v_gb', 'label' => 'Объем оперативной памяти в Gb'],
          ['field' => 'ili_ukazhite_zhelaemyy_obshchiy_obem_diskovoy_podsistemy', 'label' => 'Желаемый общий объем дисковой подсистемы'],
          ['field' => 'nalichie_otdelnogo_nakopitelya_dlya_ustanovki_os', 'label' => 'Наличие отдельного накопителя для установки ОС'],
          ['field' => 'nalichie_appratnogo_diskovogo_kontrollera', 'label' => 'Наличие аппаратного дискового контроллера'],
          ['field' => 'nalichie_adaptera_fibre_channel_dual_port', 'label' => 'Наличие адаптера Fibre Channel'],
          ['field' => 'add_setevoy_interfeys', 'label' => 'Добавить дополнительный сетевой адаптер', 'format' => 'yes_if_true'],
          ['field' => 'tip_setevyh_interfeysov', 'label' => 'Тип сетевых интерфейсов'],
          ['field' => 'kolichestvo_setevyh_portov', 'label' => 'Количество сетевых портов'],
          ['field' => 'skorost_setevyh_portov_gbit_sek_med', 'label' => 'Скорость сетевых портов Гбит/сек'],
          ['field' => 'skorost_setevyh_portov_gbit_sek_optika', 'label' => 'Скорость сетевых портов Гбит/сек'],
        ],
        processors: [
          'filters' => [
            'frequency_min' => 'frequency_min',
            'frequency_max' => 'frequency_max',
            'cores_min' => 'cores_min',
            'cores_max' => 'cores_max',
          ],
          'defaults' => [
            'frequency_min' => '1.0',
            'frequency_max' => '4.0',
            'cores_min' => '2',
            'cores_max' => '144',
          ],
        ],
        summary: [
          'server' => [
            'enabled' => FALSE,
          ],
          'platform' => [
            'enabled' => FALSE,
          ],
          'cpu' => [
            'enabled' => TRUE,
            'title' => 'Выбранные процессоры',
          ],
          'form' => [
            'enabled' => TRUE,
            'title' => 'Выбранные параметры',
            'fields' => [
              ['field' => 'form_faktor', 'label' => 'Форм-фактор'],
              ['field' => 'form_factor_units', 'label' => 'Высота в юнитах'],
              ['field' => 'form_faktor_nakopiteley_v_dyuymah', 'label' => 'Форм-фактор накопителей в дюймах'],
              ['field' => 'nalichie_otkazoustoychivogo_bloka_pitaniya', 'label' => 'Наличие отказоустойчивого блока питания'],
              ['field' => 'cpu_vendor', 'label' => 'Производитель процессора'],
              ['field' => 'cpu_generation_entity_selection', 'label' => 'Поколение процессора'],
              ['field' => 'platform_select', 'label' => 'Платформа'],
              ['field' => 'server_select', 'label' => 'Сервер'],
              ['field' => 'kolichestvo_processorov', 'label' => 'Количество процессоров'],
              ['field' => 'obem_operativnoy_pamyati_v_gb', 'label' => 'Объем оперативной памяти в Gb'],
              ['field' => 'ili_ukazhite_zhelaemyy_obshchiy_obem_diskovoy_podsistemy', 'label' => 'Желаемый общий объем дисковой подсистемы'],
              ['field' => 'nalichie_otdelnogo_nakopitelya_dlya_ustanovki_os', 'label' => 'Наличие отдельного накопителя для установки ОС'],
              ['field' => 'nalichie_appratnogo_diskovogo_kontrollera', 'label' => 'Наличие аппаратного дискового контроллера'],
              ['field' => 'nalichie_adaptera_fibre_channel_dual_port', 'label' => 'Наличие адаптера Fibre Channel'],
              ['field' => 'add_setevoy_interfeys', 'label' => 'Добавить дополнительный сетевой адаптер', 'format' => 'yes_if_true'],
              ['field' => 'tip_setevyh_interfeysov', 'label' => 'Тип сетевых интерфейсов'],
              ['field' => 'kolichestvo_setevyh_portov', 'label' => 'Количество сетевых портов'],
              ['field' => 'skorost_setevyh_portov_gbit_sek_med', 'label' => 'Скорость сетевых портов Гбит/сек'],
              ['field' => 'skorost_setevyh_portov_gbit_sek_optika', 'label' => 'Скорость сетевых портов Гбит/сек'],
            ],
          ],
        ],
      ),
      self::SERVER_CONFIGURATOR => new ConfiguratorDefinition(
        name: self::SERVER_CONFIGURATOR,
        label: 'Server Configurator',
        webformId: 'server_configurator',
        features: [
          self::FEATURE_SUMMARY => TRUE,
          self::FEATURE_STORAGE => TRUE,
          self::FEATURE_PROCESSORS => TRUE,
          self::FEATURE_DEPENDENCIES => FALSE,
        ],
        fields: [
          'resolved_cpu_generation' => 'cpu_generation_twig',
          'processors_count' => 'kolichestvo_processorov',
          'show_processors' => 'show_processors',
          'selected_processors_text' => 'selected_processors_text',
          'show_processors_button' => 'show_processors_button',
          'reset_processors_button' => 'my_reset',
          'processors_view' => 'processor_for_server',
          'processors_wrapper' => 'processors_ajax_wrapper',
          'servers_wrapper' => 'servers_ajax_wrapper',
        ],
        dependencies: [],
        summaryFields: [
          ['field' => 'kolichestvo_processorov', 'label' => 'Количество процессоров'],
          ['field' => 'obem_operativnoy_pamyati_v_gb', 'label' => 'Объем оперативной памяти в Gb'],
          ['field' => 'ili_ukazhite_zhelaemyy_obshchiy_obem_diskovoy_podsistemy', 'label' => 'Желаемый общий объем дисковой подсистемы'],
          ['field' => 'nalichie_otdelnogo_nakopitelya_dlya_ustanovki_os', 'label' => 'Наличие отдельного накопителя для установки ОС'],
          ['field' => 'nalichie_appratnogo_diskovogo_kontrollera', 'label' => 'Наличие аппаратного дискового контроллера'],
          ['field' => 'nalichie_adaptera_fibre_channel_dual_port', 'label' => 'Наличие адаптера Fibre Channel'],
          ['field' => 'add_setevoy_interfeys', 'label' => 'Добавить дополнительный сетевой адаптер', 'format' => 'yes_if_true'],
          ['field' => 'tip_setevyh_interfeysov', 'label' => 'Тип сетевых интерфейсов'],
          ['field' => 'kolichestvo_setevyh_portov', 'label' => 'Количество сетевых портов'],
          ['field' => 'skorost_setevyh_portov_gbit_sek_med', 'label' => 'Скорость сетевых портов Гбит/сек'],
          ['field' => 'skorost_setevyh_portov_gbit_sek_optika', 'label' => 'Скорость сетевых портов Гбит/сек'],
        ],
        processors: [
          'filters' => [
            'frequency_min' => 'frequency_min',
            'frequency_max' => 'frequency_max',
            'cores_min' => 'cores_min',
            'cores_max' => 'cores_max',
          ],
          'defaults' => [
            'frequency_min' => '1.0',
            'frequency_max' => '4.0',
            'cores_min' => '2',
            'cores_max' => '144',
          ],
        ],
        summary: [
          'server' => [
            'enabled' => TRUE,
            'title' => 'Сервер',
          ],
          'platform' => [
            'enabled' => TRUE,
            'title' => 'Параметры платформы',
          ],
          'cpu' => [
            'enabled' => TRUE,
            'title' => 'Выбранные процессоры',
          ],
          'form' => [
            'enabled' => TRUE,
            'title' => 'Выбранные параметры',
            'fields' => [
              ['field' => 'kolichestvo_processorov', 'label' => 'Количество процессоров'],
              ['field' => 'obem_operativnoy_pamyati_v_gb', 'label' => 'Объем оперативной памяти в Gb'],
              ['field' => 'ili_ukazhite_zhelaemyy_obshchiy_obem_diskovoy_podsistemy', 'label' => 'Желаемый общий объем дисковой подсистемы'],
              ['field' => 'nalichie_otdelnogo_nakopitelya_dlya_ustanovki_os', 'label' => 'Наличие отдельного накопителя для установки ОС'],
              ['field' => 'nalichie_appratnogo_diskovogo_kontrollera', 'label' => 'Наличие аппаратного дискового контроллера'],
              ['field' => 'nalichie_adaptera_fibre_channel_dual_port', 'label' => 'Наличие адаптера Fibre Channel'],
              ['field' => 'add_setevoy_interfeys', 'label' => 'Добавить дополнительный сетевой адаптер', 'format' => 'yes_if_true'],
              ['field' => 'tip_setevyh_interfeysov', 'label' => 'Тип сетевых интерфейсов'],
              ['field' => 'kolichestvo_setevyh_portov', 'label' => 'Количество сетевых портов'],
              ['field' => 'skorost_setevyh_portov_gbit_sek_med', 'label' => 'Скорость сетевых портов Гбит/сек'],
              ['field' => 'skorost_setevyh_portov_gbit_sek_optika', 'label' => 'Скорость сетевых портов Гбит/сек'],
            ],
          ],
        ],
      ),
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
