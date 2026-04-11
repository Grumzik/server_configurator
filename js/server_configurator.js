// bootstrap Drupal behavior - точка входа:
// создаёт summary DOM
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
      state.setMode(mode);
      state.setServer(serverData);
      state.setPlatform(platformData);

      if (!Drupal.serverConfiguratorRenderer) {
        Drupal.serverConfiguratorRenderer = Drupal.serverConfiguratorRender();
      }

      Drupal.serverConfiguratorEngine.init(
        state,
        Drupal.serverConfiguratorRenderer
      );

      Drupal.serverConfiguratorEvents(context, state);

      setTimeout(() => {
        Drupal.serverConfiguratorStorage.restoreRowsFromState(state);
        Drupal.serverConfiguratorStorage.refresh(state);
        Drupal.serverConfiguratorEngine.recalculate();
      }, 50);
    }
  };

})(Drupal, once, drupalSettings);
