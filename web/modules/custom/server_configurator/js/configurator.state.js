(function (Drupal) {
  if (Drupal.serverConfiguratorState) {
    return;
  }

  const state = {
    mode: '',
    config: {
      features: {},
      fields: {},
      summary_fields: [],
      processors: {},
      summary: {},
    },
    platform: {},
    server: {},
    cpu: [],
    storage: {},
    form: {},
    touchedFields: {},
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
        state.touchedFields = {};
        state.showProcessors = false;
        recalc();
      },

      setMode(mode) {
        state.mode = mode || '';
      },

      getMode() {
        return state.mode || '';
      },

      setConfig(config) {
        state.config = {
          features: {},
          fields: {},
          summary_fields: [],
          processors: {},
          summary: {},
          ...(config || {}),
        };
      },

      getConfig() {
        return state.config || {};
      },

      getFeatures() {
        return state.config?.features || {};
      },

      hasFeature(name) {
        return !!(state.config?.features && state.config.features[name]);
      },

      getFields() {
        return state.config?.fields || {};
      },

      getField(key, fallback = '') {
        return state.config?.fields?.[key] || fallback;
      },

      getProcessorsConfig() {
        return state.config?.processors || {};
      },

      getProcessorsDefault(key, fallback = '') {
        return state.config?.processors?.defaults?.[key] ?? fallback;
      },

      getProcessorsFilterField(key, fallback = '') {
        return state.config?.processors?.filters?.[key] ?? fallback;
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

      touchField(fieldName) {
        if (!fieldName) {
          return;
        }
        state.touchedFields[fieldName] = true;
      },

      untouchField(fieldName) {
        if (!fieldName) {
          return;
        }
        delete state.touchedFields[fieldName];
      },

      isFieldTouched(fieldName) {
        return !!state.touchedFields[fieldName];
      },

      getTouchedFields() {
        return state.touchedFields || {};
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
