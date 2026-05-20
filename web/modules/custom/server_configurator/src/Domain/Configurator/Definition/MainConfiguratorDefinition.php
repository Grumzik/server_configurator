<?php

namespace Drupal\server_configurator\Domain\Configurator\Definition;

use Drupal\server_configurator\Domain\Configurator\ConfiguratorDefinition;
use Drupal\server_configurator\Domain\Configurator\ConfiguratorDefinitions;

/**
 * Configuration for the universal server configurator.
 */
final class MainConfiguratorDefinition {

  public static function create(): ConfiguratorDefinition {
    return new ConfiguratorDefinition(
      name: ConfiguratorDefinitions::MAIN_CONFIGURATOR,
      label: 'Main Server Configurator',
      webformId: 'main_server_configurator',
      features: [
        ConfiguratorDefinitions::FEATURE_SUMMARY => TRUE,
        ConfiguratorDefinitions::FEATURE_STORAGE => TRUE,
        ConfiguratorDefinitions::FEATURE_PROCESSORS => TRUE,
        ConfiguratorDefinitions::FEATURE_DEPENDENCIES => TRUE,
      ],
      dependencies: [
        [
          'type' => 'cpu_generation_by_vendor',
          'source' => 'cpu_vendor',
          'target' => 'cpu_generation_entity_selection',
        ],
        [
          'type' => 'platform_by_generation_and_form_factor',
          'source' => [
            'cpu_generation_entity_selection',
            'form_factor_units',
            'storage_form_factor',
          ],
          'target' => 'platform_select',
        ],
        [
          'type' => 'server_by_platform',
          'source' => ['platform_select'],
          'target' => 'server_select',
        ],
      ],
      processors: self::processors(),
      summary: self::summary(),
    );
  }

  private static function processors(): array {
    return [
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
    ];
  }

  private static function summary(): array {
    return [
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
          ['field' => 'storage_form_factor', 'label' => 'Форм-фактор накопителей в дюймах'],
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
    ];
  }

}
