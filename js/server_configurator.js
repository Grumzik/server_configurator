// bootstrap Drupal behavior - точка входа:
// инициализирует state
// создаёт renderer
// запускает engine
// подключает events
(function (Drupal, once, drupalSettings) {

  Drupal.behaviors.serverConfigurator = {
    attach(context) {
      const summary = document.querySelector('#server-config-summary');
      if (!summary) {
        return;
      }

      const settings = drupalSettings.serverConfigurator || {};
      const mode = settings.mode || '';
      const serverData = settings.server || {};
      const platformData = settings.platform || {};

      const state = Drupal.serverConfiguratorState();

      if (mode) {
        state.setMode(mode);
      }

      if (serverData && Object.keys(serverData).length) {
        state.setServer(serverData);
      }

      // Важно: не затираем выбранную пользователем платформу пустым объектом
      // на частичных AJAX attach большого конфигуратора.
      if (platformData && Object.keys(platformData).length) {
        state.setPlatform(platformData);
      }

      if (!Drupal.serverConfiguratorRenderer) {
        Drupal.serverConfiguratorRenderer = Drupal.serverConfiguratorRender();
      }

      Drupal.serverConfiguratorEngine.init(
        state,
        Drupal.serverConfiguratorRenderer
      );

      Drupal.serverConfiguratorEvents(context, state);

      setTimeout(() => {
        Drupal.serverConfiguratorEngine.recalculate();
      }, 50);
    }
  };

})(Drupal, once, drupalSettings);
