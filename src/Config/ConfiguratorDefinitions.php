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
                    'platform' => 'platform_entity_selection',
                    'form_factor' => 'form_factor_units',
                ],
                dependencies: [
                    [
                        'type' => 'cpu_generation_by_vendor',
                        'source' => 'cpu_vendor',
                        'target' => 'cpu_generation_select',
                        'wrapper_id' => 'cpu-generation-wrapper',
                        'ajax_callback' => 'server_configurator_dependency_ajax_callback',
                    ],
                    [
                        'type' => 'platform_by_generation_and_form_factor',
                        'source' => ['cpu_generation_select', 'form_factor'],
                        'target' => 'platform',
                        'wrapper_id' => 'platform-wrapper',
                        'ajax_callback' => 'server_configurator_dependency_ajax_callback',
                    ],
                ],
                summaryFields: [
                    'ili_ukazhite_zhelaemyy_obshchiy_obem_diskovoy_podsistemy',
                    'nalichie_otdelnogo_nakopitelya_dlya_ustanovki_os',
                    'nalichie_appratnogo_diskovogo_kontrollera',
                    'nalichie_adaptera_fibre_channel_dual_port',
                    'add_setevoy_interfeys',
                    'tip_setevyh_interfeysov',
                    'kolichestvo_setevyh_portov',
                    'skorost_setevyh_portov_gbit_sek_med',
                    'skorost_setevyh_portov_gbit_sek_optika',
                    'cpu_vendor',
                    'cpu_generation_entity_selection',
                    'platform_entity_selection',
                    'form_factor_units',
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
                ],
                dependencies: [],
                summaryFields: [
                    'ili_ukazhite_zhelaemyy_obshchiy_obem_diskovoy_podsistemy',
                    'nalichie_otdelnogo_nakopitelya_dlya_ustanovki_os',
                    'nalichie_appratnogo_diskovogo_kontrollera',
                    'nalichie_adaptera_fibre_channel_dual_port',
                    'add_setevoy_interfeys',
                    'tip_setevyh_interfeysov',
                    'kolichestvo_setevyh_portov',
                    'skorost_setevyh_portov_gbit_sek_med',
                    'skorost_setevyh_portov_gbit_sek_optika',
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
