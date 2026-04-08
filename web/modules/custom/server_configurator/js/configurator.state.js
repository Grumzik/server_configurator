// Это аналог Domain / State Store
(function (Drupal) {

  if (Drupal.serverConfiguratorState) {
    return;
  }

  const state = {
    mode: '',
    platform: {},
    server: {},
    cpu: [],
    storage: {},
    form: {},
    showProcessors: false,
  };

  function recalc() {
    if (Drupal.serverConfiguratorEngine) {
      Drupal.serverConfiguratorEngine.recalculate();
    }
  }

  Drupal.serverConfiguratorState = function () {
    return {
      stateInit() {
        state.form = {};
        state.cpu = [];
        state.storage = {};
        state.showProcessors = false;
        recalc();
      },

      setMode(mode) {
        state.mode = mode || '';
      },

      getMode() {
        return state.mode || '';
      },

      setPlatform(data) {
        state.platform = data || {};
        recalc();
      },

      getPlatform() {
        return state.platform;
      },

      setCpu(list) {
        state.cpu = Array.isArray(list) ? list : [];
        recalc();
      },

      getCpu() {
        return state.cpu;
      },

      setServer(data) {
        state.server = data || {};
        recalc();
      },

      getServer() {
        return state.server;
      },

      setStorageItem(id, data) {
        state.storage[id] = {
          ...(state.storage[id] || {}),
          ...data,
        };
        recalc();
      },

      setStorage(data) {
        state.storage = {
          ...(data || {}),
        };
        recalc();
      },

      removeStorageItem(id) {
        delete state.storage[id];
        recalc();
      },

      getAvailableStorageSlots() {
        const total = Number(state.platform?.storage_bays || 0);
        const used = Object.values(state.storage || {}).reduce((sum, item) => {
          return sum + Number(item?.count || 0);
        }, 0);
        return Math.max(total - used, 0);
      },

      getStorage() {
        return state.storage;
      },

      setForm(data) {
        state.form = { ...state.form, ...(data || {}) };
        recalc();
      },

      getForm() {
        return state.form;
      },

      setShowProcessors(value) {
        state.showProcessors = !!value;
        recalc();
      },

      getShowProcessors() {
        return !!state.showProcessors;
      },

      getState() {
        return state;
      },
    };
  };

})(Drupal);
