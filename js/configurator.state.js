//Это аналог Domain / State Store
(function (Drupal) {


    if (Drupal.serverConfiguratorState) {
      return; // если state уже существует — не создаём заново
    }

    const state = {
      platform: {},
      server: {},
      cpu: [],
      storage: {},
      form: {},
      showProcessors: ''
    };

  function recalc() {
    if (Drupal.serverConfiguratorEngine) {
      Drupal.serverConfiguratorEngine.recalculate();
    }
  }


  Drupal.serverConfiguratorState = function () {

    const instanceId = Math.random().toString(36).slice(2, 8);


    return {
      stateInit(){
        state.form = {};
        state.cpu = [];
        state.storage = {};
        state.showProcessors = false;
        recalc();
      },

      /* PLATFORM */
      setPlatform(data) {
        state.platform = data || {};
        recalc();
      },

      getPlatform() {
        return state.platform;
      },

      /* CPU */
      setCpu(list) {
        state.cpu = Array.isArray(list) ? list : [];
        recalc();
      },

      getCpu() {
        return state.cpu;
      },

      /* SERVER */
      setServer(data) {
        state.server = data || {};
        recalc();
      },

      getServer() {
        return state.server;
      },

      /* STORAGE (хранилище для элемента component накопители) */
      setStorageItem(id, data) {
        state.storage[id] = {
          ...(state.storage[id] || {}),
          ...data
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
        const used = Object.keys(state.storage || {}).length;
        return Math.max(total - used, 0);
      },

      getStorage() {
        return state.storage;
      },

      /* FORM */
      setForm(data) {
        state.form = { ...state.form, ...(data || {}) };
        recalc();
      },

      getForm() {
        return state.form;
      },

      /* ShowProcessors */
      setShowProcessors(value) {
        state.showProcessors = value;
         recalc();
      },

      getShowProcessors(){
        return state.showProcessors || false;

      },

      /* FULL STATE */
      getState() {
        return state;
      }



    };
  };

})(Drupal);
