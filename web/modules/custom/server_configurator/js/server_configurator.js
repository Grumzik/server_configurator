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
      const config = settings.config || {};

      const state = Drupal.serverConfiguratorState();
      state.setConfig(config);

      if (mode) {
        state.setMode(mode);
      }

      if (serverData && Object.keys(serverData).length) {
        state.setServer(serverData);
      }

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
