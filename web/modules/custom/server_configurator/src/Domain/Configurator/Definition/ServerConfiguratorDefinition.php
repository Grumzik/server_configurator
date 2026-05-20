<?php

namespace Drupal\server_configurator\Domain\Configurator\Definition;

use Drupal\server_configurator\Domain\Configurator\ConfiguratorDefinition;
use Drupal\server_configurator\Domain\Configurator\ConfiguratorDefinitions;

/**
 * Configuration for the configurator embedded on a concrete server page.
 */
final class ServerConfiguratorDefinition {

  public static function create(): ConfiguratorDefinition {
    return new ConfiguratorDefinition(
      name: ConfiguratorDefinitions::SERVER_CONFIGURATOR,
      label: 'Server Configurator',
      webformId: 'server_configurator',
      features: [
        ConfiguratorDefinitions::FEATURE_SUMMARY => TRUE,
        ConfiguratorDefinitions::FEATURE_STORAGE => TRUE,
        ConfiguratorDefinitions::FEATURE_PROCESSORS => TRUE,
        ConfiguratorDefinitions::FEATURE_DEPENDENCIES => FALSE,
      ],
      dependencies: [],
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
    ];
  }

}
