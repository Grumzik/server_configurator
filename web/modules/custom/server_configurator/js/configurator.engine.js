// логика перерасчёта конфигуратора:
// считает лимиты
// обновляет UI
// вызывает render


(function (Drupal) {
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
      if (isUpdating || !state || !render) return;
      isUpdating = true;

      const platform = state.getPlatform();
      const storage = state.getStorage();

      if (!platform) return;

      const maxStorage = Number(platform.storage_bays || 0);

      Drupal.serverConfiguratorStorage.recalcLimits(maxStorage);
      Drupal.serverConfiguratorStorage.updateAddButtons(maxStorage);
      render.render(state);

      isUpdating = false;

    }
  };

})(Drupal);
