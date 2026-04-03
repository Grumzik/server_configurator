// Слушает пользователя и обновляет state
(function (Drupal, once) {

  // Drupal.serverConfiguratorEvents = function (context, state, render) {
  Drupal.serverConfiguratorEvents = function (context, state) {
    const webform =
      document.querySelector('.webform-submission-server-configurator-form');

    if (!webform) {
console.log('Это не вебформа конфигуратора! не выполянем events');
      return;
    }




    /* ---------- CPU selection ---------- */
    const checkboxes = once(
      'server-configurator-cpu',
      'input[name^="entity_browser_select"]',
      context
    );

    const FIELD_MAP = {
      "field-processor-number": "model",
      "field-cpu-max-memory-size": "max_memory",
      "field-processor-base-frequency": "base_freq",
      "field-max-turbo-frequency": "turbo_freq",
      "field-total-cores": "cores"
    };

    function mapKey(key) {
      return FIELD_MAP[key] || key.replace(/-/g, "_");
    }

    function formatRow(row) {
      return (
        `\nМодель процессора:  ${row.model}\n` +
        `Количество ядер: ${row.cores}\n` +
        `Частота: ${row.base_freq} → ${row.turbo_freq}\n` +
        `Memory:  max ${row.max_memory}\n`
      );
    }




    function syncSelectedCpuToForm(cpus) {

      // const input = document.querySelector('[name="selected_processors"]');
      const input = document.querySelector('[name="selected_processors_text"]');

      if (!input) return;
       const text = cpus.map((cpu,i) =>  `CPU #${i + 1} ${cpu.pretty}`).join("\n");
       input.value = text;

    }

    function collectSelected() {
      const selected = [];

      context
        .querySelectorAll('input[name^="entity_browser_select"]:checked')
        .forEach(cb => {
          const row = cb.closest('tr');
          if (!row) return;

          const rowData = {id: cb.value};

          row.querySelectorAll('td').forEach(td => {
            const fieldClass = [...td.classList]
              .find(c => c.startsWith('views-field-'));

            if (!fieldClass) return;

            const rawKey = fieldClass.replace('views-field-', '');
            rowData[mapKey(rawKey)] = td.innerText.trim();
          });

          rowData.pretty = formatRow(rowData);
          selected.push(rowData);
        });

      return selected;
    }

    checkboxes.forEach(cb => {
      cb.addEventListener('change', () => {
        state.setCpu(collectSelected());
        Drupal.serverConfiguratorEngine.recalculate();
        syncSelectedCpuToForm(collectSelected());
      });
    });


    /* ---------- FORM-configurator elements selection ---------- */
    // ( function bindFormFields() {
    //   context
    //     .querySelectorAll('[name="kolichestvo_processorov"]')
    //     .forEach((radio) => {
    //       radio.addEventListener('change', (e) => {
    //         state.setForm({
    //           kolichestvo_processorov: e.target.value,
    //         });
    //         render.render(state.getState());
    //       });
    //     });
    // })();


    /* ---------- FORM-configurator elements selection Universal function ---------- */
    function bindRadioElements(elementname) {
      once(
        `server-configurator-${elementname}`,
        `[name="${elementname}"]`,
        context
      ).forEach((radio) => {
        radio.addEventListener('change', (e) => {
          if (!e.target.checked) return;

          state.setForm({
            [elementname]: e.target.value,
          });
          Drupal.serverConfiguratorEngine.recalculate();
        });
      });
    }

    function bindSelectElement(elementname) {
      once(
        `server-configurator-${elementname}`,
        `select[name="${elementname}"]`,
        context
      ).forEach((select) => {
        select.addEventListener('change', (e) => {
          state.setForm({
            [elementname]: e.target.value,
          });
          Drupal.serverConfiguratorEngine.recalculate();
        });
      });
    }

    function bindTextElement(elementname) {
      once(
        `server-configurator-${elementname}`,
        `input[name="${elementname}"], textarea[name="${elementname}"]`,
        context
      ).forEach((input) => {
        input.addEventListener('change', (e) => {
          const value = e.target.value?.trim();

          state.setForm({
            [elementname]: value,
          });
          Drupal.serverConfiguratorEngine.recalculate();
        });
      });
    }




    function bindStorageComposite(context) {
      const selector = 'tr[data-drupal-selector^="edit-kompozitnyy-elemen-storage-items-"]';

      once('server-configurator-storage', selector, context)
        .forEach((row) => {

          row.addEventListener('change', collectAllRows);
        });



      function collectAllRows() {

        const rows = document.querySelectorAll(selector);
        let id_item;
        const storage = {}; //new

        rows.forEach((row) => {

          const typeInput = row.querySelector('.storage_types:checked');
          const type = typeInput ? typeInput.value.trim() : null;

          const countInput = row.querySelector('.storage_count');
          const count = countInput ? parseInt(countInput.value) : 0;

          const volumeInput = row.querySelector('.storage-volume input:checked');
          const volume = volumeInput ? volumeInput.value : null;

          id_item = Drupal.serverConfiguratorUtils.getRowId(row);
          if (type && count > 0) {

     //++new
            storage[id_item] = {
              type,
              count,
              volume
            };
    //--new

            // state.setStorageItem(id_item, {
            //   type,
            //   count,
            //   volume
            // });
          }
          // else {
          //   state.removeStorageItem(id_item);
          // }



        })

        state.setStorage(storage);
        Drupal.serverConfiguratorEngine.recalculate();
      }
    }


    function bindButton(btnselector) {
      once(
        `server-configurator-${btnselector}`,
        `[data-drupal-selector="${btnselector}"]`,
        context
      ).forEach((btn) => {
        btn.addEventListener('click', (e) => {
          event.preventDefault();

          state.setForm({
            [elementname]: value,
          });
          Drupal.serverConfiguratorEngine.recalculate();
        });
      });
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
      // bindButton('edit-actions-01-reset');
      bindStorageComposite(context);


      /* ---------- Summary toggle ---------- */
      // const summary = document.querySelector('#server-config-summary');
      // const toggle = document.querySelector('.config-toggle');
      // const close = document.querySelector('.summary-close');
      // const backdrop = document.querySelector('.summary-backdrop');
      //
      // toggle?.addEventListener('click', () => {
      //   console.log('is-open');
      //   summary?.classList.toggle('is-open');
      // });
      //
      //
      //
      // once('summary-close', '.summary-close', context)
      //   .forEach((btn) => {
      //     btn.addEventListener('click', () => {
      //       console.log('summary click!!! ');
      //       document
      //         .querySelector('#server-config-summary')
      //         ?.classList.remove('is-open');
      //     });
      //   });
      //
      //
      // function openSummary() {
      //   summary?.classList.add('is-open');
      //   backdrop?.classList.add('is-open');
      // }
      //
      // function closeSummary() {
      //   summary?.classList.remove('is-open');
      //   backdrop?.classList.remove('is-open');
      // }
      //
      // toggle?.addEventListener('click', openSummary);
      // close?.addEventListener('click', closeSummary);
      // backdrop?.addEventListener('click', closeSummary);
      //
      // document.addEventListener('keydown', (e) => {
      //   if (e.key === 'Escape') closeSummary();
      // });

      /* ---------- The end ---------- */



    };
})(Drupal, once);
