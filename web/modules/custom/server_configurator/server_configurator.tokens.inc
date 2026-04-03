<?php

/**
 * @file
 * Token callbacks for Server Configurator module.
 */

/**
 * Implements hook_token_info().
 *
 * ОПИСАНИЕ:
 * Эта функция ОБЪЯВЛЯЕТ новые токены, которые будет
 * предоставлять наш модуль. Она НЕ обрабатывает токены,
 * а только регистрирует их в системе Drupal.
 *
 * КОГДА ВЫЗЫВАЕТСЯ:
 * Когда система токенов строит список всех доступных токенов
 * (например, в браузере токенов или при создании правил).
 *
 * ПАРАМЕТРЫ:
 * Не принимает параметров кроме стандартных $options.
 */
function server_configurator_token_info() {
  // Создаём массив для хранения информации о токенах
  $info = [];

  // === ДОБАВЛЯЕМ ТОКЕНЫ ДЛЯ НОД (Content) ===
  $info['tokens']['node'] = [
    // Токен #1: LAN характеристики
    'server_platform_lan' => [
      'name' => t('Server Platform LAN'),
      'description' => t('LAN характеристики платформы сервера. Возвращает значение поля field_lan_ports_speed из связанной платформы.'),
    ],

    // Токен #2: Все характеристики платформы (опционально)
    'server_platform_full' => [
      'name' => t('Server Platform Full Specs'),
      'description' => t('Все характеристики платформы сервера в HTML формате.'),
    ],

    // Токен #3: Название платформы (опционально)
    'server_platform_name' => [
      'name' => t('Server Platform Name'),
      'description' => t('Название платформы (заголовок ноды Platform).'),
    ],
  ];

  // === МОЖНО ДОБАВИТЬ ТОКЕНЫ ДЛЯ ДРУГИХ ТИПОВ СУЩНОСТЕЙ ===
  $info['tokens']['server_platform'] = [
    'lan' => [
      'name' => t('Platform LAN'),
      'description' => t('LAN характеристики платформы'),
    ],
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 *
 * ОПИСАНИЕ:
 * Эта функция ОБРАБАТЫВАЕТ токены - заменяет токен на реальное значение.
 * Когда где-то в тексте встречается [node:server_platform_lan],
 * Drupal вызывает эту функцию, чтобы получить фактическое значение.
 *
 * КОГДА ВЫЗЫВАЕТСЯ:
 * Каждый раз, когда нужно заменить токены в тексте.
 *
 * ПАРАМЕТРЫ:
 * 1. $type - тип токена ('node', 'user', 'site', etc.)
 * 2. $tokens - массив токенов, которые нужно обработать
 *    Например: ['server_platform_lan' => '[node:server_platform_lan]']
 * 3. $data - контекстные данные (например, объект ноды)
 * 4. $options - дополнительные опции (язык, безопасность и т.д.)
 *
 * ВОЗВРАЩАЕТ:
 * Массив замен: ['[node:server_platform_lan]' => '10 Gb Ethernet']
 */
function server_configurator_tokens($type, $tokens, array $data, array $options) {
  // Массив для хранения результатов замены
  $replacements = [];

  // === ОБРАБОТКА ТОКЕНОВ ДЛЯ НОД ===
  if ($type == 'node' && !empty($data['node'])) {
    $node = $data['node'];

    // Перебираем все запрошенные токены
    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Токен: [node:server_platform_lan]
        case 'server_platform_lan':
          // Проверяем, что это нода типа "Server"
          if ($node->bundle() == 'server') {
            // Проверяем, есть ли поле field_platform
            if ($node->hasField('field_platform') && !$node->field_platform->isEmpty()) {
              // Получаем связанную ноду Platform
              $platform = $node->field_platform->entity;

              // Проверяем, что платформа существует и имеет поле field_lan_ports_speed
              if ($platform && $platform->hasField('field_lan_ports_speed') && !$platform->field_lan_ports_speed->isEmpty()) {
                // Получаем значение поля
                $lan_value = $platform->field_lan_ports_speed->value;

                // Добавляем в массив замен
                $replacements[$original] = $lan_value;
              } else {
                // Если данных нет - возвращаем пустую строку
                $replacements[$original] = '';
              }
            }
          }
          break;

        // Токен: [node:server_platform_name] (опционально)
        case 'server_platform_name':
          if ($node->bundle() == 'server' &&
            $node->hasField('field_platform') &&
            !$node->field_platform->isEmpty()) {
            $platform = $node->field_platform->entity;
            if ($platform) {
              $replacements[$original] = $platform->getTitle();
            }
          }
          break;

        // Токен: [node:server_platform_full] (опционально)
        case 'server_platform_full':
          if ($node->bundle() == 'server' &&
            $node->hasField('field_platform') &&
            !$node->field_platform->isEmpty()) {
            $platform = $node->field_platform->entity;

            if ($platform) {
              $specs = [];

              // Собираем все поля платформы
              if ($platform->hasField('field_lan_ports_speed') && !$platform->field_lan_ports_speed->isEmpty()) {
                $specs[] = '<strong>LAN:</strong> ' . $platform->field_lan_ports_speed->value;
              }

              if ($platform->hasField('field_cpu') && !$platform->field_cpu->isEmpty()) {
                $specs[] = '<strong>CPU:</strong> ' . $platform->field_cpu->value;
              }

              if ($platform->hasField('field_memory') && !$platform->field_memory->isEmpty()) {
                $specs[] = '<strong>Memory:</strong> ' . $platform->field_memory->value;
              }

              // Объединяем все спецификации
              if (!empty($specs)) {
                $replacements[$original] = implode('<br>', $specs);
              }
            }
          }
          break;
      }
    }
  }

  // === ОБРАБОТКА ТОКЕНОВ ДРУГИХ ТИПОВ ===
  // (опционально, если нужно)
  if ($type == 'server_platform') {
    // Можно получить ноду из контекста запроса
    $node = \Drupal::routeMatch()->getParameter('node');

    if ($node && $node->bundle() == 'server') {
      foreach ($tokens as $name => $original) {
        if ($name == 'lan' &&
          $node->hasField('field_platform') &&
          !$node->field_platform->isEmpty()) {

          $platform = $node->field_platform->entity;
          if ($platform && $platform->hasField('field_lan_ports_speed')) {
            $replacements[$original] = $platform->field_lan_ports_speed->value;
          }
        }
      }
    }
  }

  return $replacements;
}
