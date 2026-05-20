// логика перерасчёта конфигуратора:
// считает лимиты
// обновляет UI
// вызывает render
(function (Drupal, drupalSettings) {
  let isUpdating = false;
  let state;
  let render;

  Drupal.serverConfiguratorEngine = {

    init(s, r) {
      state = s;
      render = r;
      this.recalculate();
    },

    recalculate() {
      if (isUpdating || !state || !render) {
        return;
      }

      isUpdating = true;

      try {
        // const platform = state.getPlatform() || {};
        // const maxBays = Number(platform.storage_bays || 0);
        const settings = drupalSettings.serverConfigurator || {};
        const serverPlatformMap = settings.serverPlatformMap || {};

        const maxBays = Drupal.serverConfiguratorStorage.getMaxBaysFromServerPlatformMap(serverPlatformMap);
        Drupal.serverConfiguratorStorage.recalcLimits(maxBays);
        Drupal.serverConfiguratorStorage.renderSliderMarks();
        Drupal.serverConfiguratorStorage.updateAddButtons(maxBays);
        render.render(state);
      }
      finally {
        isUpdating = false;
      }
    }
  };

})(Drupal, drupalSettings);
