// Слушает пользователя и обновляет state
(function (Drupal, once, drupalSettings) {

  Drupal.serverConfiguratorEvents = function (context, state) {
    const settings = drupalSettings.serverConfigurator || {};
    const platformMap = settings.platformMap || {};
    const mode = state.getMode();
    const fields = state.getFields ? state.getFields() : {};

    const platformField = fields.platform || 'platform_entity_selection';
    const selectedProcessorsField = fields.selected_processors_text || 'selected_processors_text';
    const processorsCountField = fields.processors_count || 'kolichestvo_processorov';
    const formFactorField = fields.form_factor || 'form_factor_units';
    const cpuVendorField = fields.cpu_vendor || 'cpu_vendor';
    const cpuGenerationField = fields.cpu_generation_select || 'cpu_generation_entity_selection';
    const storageFormFactorField = 'form_faktor_nakopiteley_v_dyuymah';

    function recalc() {
      Drupal.serverConfiguratorEngine.recalculate();
    }

    function syncPlatformToState(platformId, touch = false) {
      const normalizedId = platformId ? String(platformId) : '';
      const currentPlatformId = String(state.getForm()?.[platformField] || '');
      const platformData = normalizedId && platformMap[normalizedId]
          ? platformMap[normalizedId]
          : null;

      const select = document.querySelector(`select[name="${platformField}"]`);
      const label = select && select.selectedIndex >= 0
          ? (select.options[select.selectedIndex]?.text || '')
          : '';

      state.setForm({
        [platformField]: normalizedId,
        [`${platformField}_label`]: label,
      });

      if (touch) {
        state.touchField(platformField);
      }

      state.setPlatform(platformData || {});

      // Если платформа изменилась или очистилась,
      // storage нужно полностью сбросить.
      if (currentPlatformId && currentPlatformId !== normalizedId) {
        if (Drupal.serverConfiguratorStorage && Drupal.serverConfiguratorStorage.resetAll) {
          Drupal.serverConfiguratorStorage.resetAll(state);
          Drupal.serverConfiguratorStorage.applyStorageTypeRules(state);
          Drupal.serverConfiguratorStorage.refresh(state);
        }
      }

      // Если платформа очищена, в большом конфигураторе она должна исчезнуть из summary.
      if (!normalizedId) {
        state.untouchField(platformField);
      }
    }

    function syncSelectedCpuToForm(cpus) {
      const input = document.querySelector(`[name="${selectedProcessorsField}"]`);
      if (!input) return;

      const text = cpus.map((cpu, i) => `CPU #${i + 1} ${cpu.pretty}`).join('\n');
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
          state.touchField(elementname);

          if (elementname === storageFormFactorField && Drupal.serverConfiguratorStorage) {
            Drupal.serverConfiguratorStorage.applyStorageTypeRules(state);
            Drupal.serverConfiguratorStorage.refresh(state);
          }
        });

        // Инициализация для логики, но НЕ как пользовательский выбор.
        if (radio.checked) {
          state.setForm({
            [elementname]: radio.value,
          });

          if (elementname === storageFormFactorField && Drupal.serverConfiguratorStorage) {
            Drupal.serverConfiguratorStorage.applyStorageTypeRules(state);
            Drupal.serverConfiguratorStorage.refresh(state);
          }
        }
      });
    }

    function bindSelectElement(elementname) {
      once(`server-configurator-${elementname}`, `select[name="${elementname}"]`, context).forEach((select) => {
        select.addEventListener('change', (e) => {
          const option = e.target.selectedIndex >= 0
              ? e.target.options[e.target.selectedIndex]
              : null;

          state.setForm({
            [elementname]: e.target.value,
            [`${elementname}_label`]: option ? option.text : '',
          });
          state.touchField(elementname);
        });

        // Инициализация значения для логики, но НЕ как пользовательский выбор.
        const option = select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
        state.setForm({
          [elementname]: select.value || '',
          [`${elementname}_label`]: option ? option.text : '',
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
          state.touchField(elementname);
        });
      });
    }

    function bindPlatformSelection() {
      once('server-configurator-platform-entity-selection', `select[name="${platformField}"]`, context).forEach((select) => {
        select.addEventListener('change', (e) => {
          syncPlatformToState(e.target.value || '', true);
        });

        // Всегда синхронизируем initial state для логики, но не touch.
        syncPlatformToState(select.value || '', false);
      });
    }

    bindRadioElements('form_faktor');
    bindRadioElements(storageFormFactorField);
    bindRadioElements('nalichie_otkazoustoychivogo_bloka_pitaniya');
    bindRadioElements(processorsCountField);
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
    bindSelectElement(formFactorField);
    bindSelectElement(cpuVendorField);
    bindSelectElement(cpuGenerationField);
    bindSelectElement(platformField);
    bindPlatformSelection();

    Drupal.serverConfiguratorStorage.init(context, state);

    recalc();
  };

})(Drupal, once, drupalSettings);
