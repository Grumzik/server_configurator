// Слушает пользователя и обновляет state
(function (Drupal, once, drupalSettings) {

  Drupal.serverConfiguratorEvents = function (context, state) {
    const summary = document.querySelector('#server-config-summary');
    if (!summary) {
      return;
    }

    const settings = drupalSettings.serverConfigurator || {};
    const platformMap = settings.platformMap || {};
    const mode = state.getMode();

    function recalc() {
      Drupal.serverConfiguratorEngine.recalculate();
    }

    function syncPlatformToState(platformId) {
      const normalizedId = platformId ? String(platformId) : '';
      const platformData = normalizedId && platformMap[normalizedId]
        ? platformMap[normalizedId]
        : null;

      state.setForm({
        platform_entity_selection: normalizedId,
      });

      state.setPlatform(platformData || {});
    }

    function syncSelectedCpuToForm(cpus) {
      const input = document.querySelector('[name="selected_processors_text"]');
      if (!input) return;

      const text = cpus.map((cpu, i) => `CPU #${i + 1} ${cpu.pretty}`).join("\n");
      input.value = text;
    }

    const FIELD_MAP = {
      'field-processor-number': 'model',
      'field-cpu-max-memory-size': 'max_memory',
      'field-processor-base-frequency': 'base_freq',
      'field-max-turbo-frequency': 'turbo_freq',
      'field-total-cores': 'cores',
    };

    function mapKey(key) {
      return FIELD_MAP[key] || key.replace(/-/g, '_');
    }

    function formatRow(row) {
      return (
        `\nМодель процессора: ${row.model}\n` +
        `Количество ядер: ${row.cores}\n` +
        `Частота: ${row.base_freq} → ${row.turbo_freq}\n` +
        `Memory: max ${row.max_memory}\n`
      );
    }

    function collectSelected() {
      const selected = [];

      document
        .querySelectorAll('input[name^="entity_browser_select"]:checked')
        .forEach((cb) => {
          const row = cb.closest('tr');
          if (!row) return;

          const rowData = { id: cb.value };

          row.querySelectorAll('td').forEach((td) => {
            const fieldClass = [...td.classList].find((c) => c.startsWith('views-field-'));
            if (!fieldClass) return;

            const rawKey = fieldClass.replace('views-field-', '');
            rowData[mapKey(rawKey)] = td.innerText.trim();
          });

          rowData.pretty = formatRow(rowData);
          selected.push(rowData);
        });

      return selected;
    }

    once('server-configurator-cpu', 'input[name^="entity_browser_select"]', context).forEach((cb) => {
      cb.addEventListener('change', () => {
        const selected = collectSelected();
        state.setCpu(selected);
        syncSelectedCpuToForm(selected);
      });
    });

    function bindRadioElements(elementname) {
      once(`server-configurator-${elementname}`, `[name="${elementname}"]`, context).forEach((radio) => {
        radio.addEventListener('change', (e) => {
          if (!e.target.checked) return;

          state.setForm({
            [elementname]: e.target.value,
          });
        });
      });
    }

    function bindSelectElement(elementname) {
      once(`server-configurator-${elementname}`, `select[name="${elementname}"]`, context).forEach((select) => {
        select.addEventListener('change', (e) => {
          state.setForm({
            [elementname]: e.target.value,
          });
        });
      });
    }

    function bindTextElement(elementname) {
      once(`server-configurator-${elementname}`, `input[name="${elementname}"], textarea[name="${elementname}"]`, context).forEach((input) => {
        input.addEventListener('change', (e) => {
          const value = e.target.value?.trim();
          state.setForm({
            [elementname]: value,
          });
        });
      });
    }

    function bindPlatformSelection() {
      once('server-configurator-platform-entity-selection', 'select[name="platform_entity_selection"]', context).forEach((select) => {
        select.addEventListener('change', (e) => {
          syncPlatformToState(e.target.value || '');
        });

        if (mode === 'main_configurator' && select.value) {
          syncPlatformToState(select.value);
        }
      });
    }

    function bindStorageComposite(localContext) {
      const selector = 'tr[data-drupal-selector^="edit-kompozitnyy-elemen-storage-items-"]';

      once('server-configurator-storage', selector, localContext).forEach((row) => {
        row.addEventListener('change', collectAllRows);
      });

      function collectAllRows() {
        const rows = document.querySelectorAll(selector);
        const storage = {};

        rows.forEach((row) => {
          const typeInput = row.querySelector('.storage_types:checked');
          const type = typeInput ? typeInput.value.trim() : null;

          const countInput = row.querySelector('.storage_count');
          const count = countInput ? parseInt(countInput.value, 10) : 0;

          const volumeInput = row.querySelector('.storage-volume input:checked');
          const volume = volumeInput ? volumeInput.value : null;

          const idItem = Drupal.serverConfiguratorUtils.getRowId(row);

          if (type && count > 0) {
            storage[idItem] = {
              type,
              count,
              volume,
            };
          }
        });

        state.setStorage(storage);
      }
    }

    bindRadioElements('kolichestvo_processorov');
    bindRadioElements('obem_operativnoy_pamyati_v_gb');
    bindRadioElements('tip_ustanovlennyh_nakopiteley');
    bindRadioElements('kolichestvo_ustanovlennyh_nakopiteley');
    bindRadioElements('obem_nakopitelya_ssd');
    bindRadioElements('obem_nakopitelya_hdd');
    bindTextElement('ili_ukazhite_zhelaemyy_obshchiy_obem_diskovoy_podsistemy');
    bindRadioElements('nalichie_otdelnogo_nakopitelya_dlya_ustanovki_os');
    bindRadioElements('nalichie_appratnogo_diskovogo_kontrollera');
    bindSelectElement('nalichie_adaptera_fibre_channel_dual_port');
    bindRadioElements('add_setevoy_interfeys');
    bindRadioElements('tip_setevyh_interfeysov');
    bindRadioElements('kolichestvo_setevyh_portov');
    bindRadioElements('skorost_setevyh_portov_gbit_sek_med');
    bindRadioElements('skorost_setevyh_portov_gbit_sek_optika');
    bindPlatformSelection();
    bindStorageComposite(context);

    recalc();
  };

})(Drupal, once, drupalSettings);
