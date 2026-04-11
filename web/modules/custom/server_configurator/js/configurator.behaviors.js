(function (Drupal, once) {


  Drupal.behaviors.serverConfiguratorReload = {

    attach(context) {
        window.addEventListener("pageshow", function (e) {

          if (e.persisted) {
            location.reload();
          }

        });

    }
  };
  Drupal.behaviors.serverConfiguratorResetFix = {

    attach(context) {
        once('server-reset-fix', context)
        .forEach(() => {

          document
            .querySelectorAll( '.webform-button--reset, .webform-button--submit' )
            .forEach(btn => {
              btn.addEventListener('mousedown', () => {
                const state = Drupal.serverConfiguratorState();
                const formBefore = state.getForm();
                state.stateInit();
                const formAfter = state.getForm();
              });

            });

        });

    }

  };

  Drupal.behaviors.serverConfiguratorRestore = {
     attach(context) {


       const state =   Drupal.serverConfiguratorState();
      const summary =   document.querySelector('#server-config-summary');

      // мы не на странице конфигуратора
      // if (!summary) return;
      // if (!state.getShowProcessors()) return;

      once('restoreCpuAfterView', context
        .querySelectorAll('[data-drupal-selector="edit-processor-for-server"]'  ))
        .forEach((view) => {

            Drupal.serverConfiguratorRestore.restoreCpu(state);
            Drupal.serverConfiguratorEngine.recalculate();
        });
      }
  };

  Drupal.behaviors.serverConfiguratorMarks = {

    attach(context) {

      // Инициализируем только один раз на весь документ
      once('server-configurator-marks', document.body).forEach(() => {
          document.body.addEventListener('click', function (e) {
          let mark = e.target.closest('.range-mark, .range-tick');

          if (!mark) return;

          const value = Number(mark.dataset.value);
          if (isNaN(value)) return;

          /* ===============================
             NORMAL RANGE
          =============================== */

          const rangeWrapper = mark.closest('.form-type-range');
          const rangeInput = rangeWrapper?.querySelector('input[type="range"]');

          if (rangeInput) {

            rangeInput.value = value;

            rangeInput.dispatchEvent(new Event('input', { bubbles: true }));
            rangeInput.dispatchEvent(new Event('change', { bubbles: true }));

            rangeInput.focus();
            return;
          }

          /* ===============================
             DUAL RANGE (noUiSlider)
          =============================== */

          const dualWrapper = mark.closest('.dual-range-wrapper');
          if (!dualWrapper) return;

          const sliderElement = dualWrapper.querySelector('.dual-range-slider');
          if (!sliderElement || !sliderElement.noUiSlider) return;

          const slider = sliderElement.noUiSlider;

          const currentValues = slider.get().map(Number);

          const distanceToFirst = Math.abs(currentValues[0] - value);
          const distanceToSecond = Math.abs(currentValues[1] - value);

          const handleIndex =
            distanceToFirst <= distanceToSecond ? 0 : 1;

          slider.setHandle(handleIndex, value);

          const handle =
            sliderElement.querySelectorAll('.noUi-handle')[handleIndex];

          if (handle) handle.focus();
          // }

        });

      });

    }
  };



  Drupal.behaviors.storageStates = {
    attach(context) {



      // Ищем каждую строку composite (каждый delta)
      once('storageStatesRow', 'tr[data-drupal-selector^="edit-kompozitnyy-elemen-storage-items-"]', context).forEach(function (row) {

        // Все radio внутри текущей строки
        const radios = row.querySelectorAll(
          'input[type="radio"][name*="[tip_ustanovlennyh_nakopiteley]"]'
        );

        const sata = row.querySelector('.storage-volume--sata');
        const nvme = row.querySelector('.storage-volume--nvme');
        const hdd  = row.querySelector('.storage-volume--hdd');



        if (!radios.length || !sata ) return;


        // Функция обновления отображения
        function updateVisibility() {

          const checked = row.querySelector(
            'input[type="radio"][name*="[tip_ustanovlennyh_nakopiteley]"]:checked'
          );

          [sata, nvme, hdd].forEach(el => {
            if (el) el.style.display = 'none';
          });

          if (!checked) return;


          if (checked.value.trim() === 'SSD SATA' && sata) {
            sata.style.display = 'block';
          }

          if (checked.value.trim() === 'SSD NVMe' && nvme) {
            nvme.style.display = 'block';
          }

          if (checked.value.trim() === 'HDD' && hdd) {
            hdd.style.display = 'block';
          }
        }


        // Вешаем change на КАЖДЫЙ radio
        radios.forEach(function (radio) {
          radio.addEventListener('change', updateVisibility);
        });

        // Запускаем при инициализации (чтобы отработал default)
        updateVisibility();

      });



      once('storageRemoveHandler',
        context.querySelectorAll('input[name^="kompozitnyy_elemen_storage_table_remove_"]')
      ).forEach(button => {

        button.addEventListener('mousedown', function () {

          const row = this.closest('tr');

          if (!row) return;

          const id = Drupal.serverConfiguratorUtils.getRowId(row);

          if (id) {
            const state = Drupal.serverConfiguratorState();
            state.removeStorageItem(id);
            }

        });

      });


    }
  };

  // Drupal.behaviors.storageRecalc = {
  //   attach(context) {
  //
  //     once('storage-recalc', context.querySelectorAll('.storage_count'))
  //       .forEach(slider => {
  //
  //         slider.addEventListener('change', () => {
  //
  //           const state = Drupal.serverConfiguratorState();
  //           const bays = state.getPlatform()?.storage_bays || 0;
  //
  //           recalcStorageLimits(bays);
  //
  //         });
  //
  //       });
  //
  //   }
  // };

  Drupal.behaviors.serverConfiguratorStorage = {

    attach(context) {

      once('storage-slider', context.querySelectorAll('.storage_count'))
        .forEach(slider => {

          slider.addEventListener('change', function () {

             // Drupal.serverConfiguratorEvents.collectAllRows();

            // const row = this.closest('tr');
            // const id = row.dataset.webformKey || row.rowIndex;
            //
            // const state = Drupal.serverConfiguratorState();
            //
            // state.setStorageItem(id, {
            //   count: Number(this.value)
            // });
            //
            // Drupal.serverConfiguratorEngine.recalculate();

          });

        });

    }

  };

  Drupal.behaviors.showProcessors = {
    attach(context) {

      const state = Drupal.serverConfiguratorState();

      /* =========================
         CHECKBOX
      ========================= */

      once(
        'showProcessorsCheckbox',
        context.querySelectorAll('[name="show_processors"]')
      ).forEach(checkbox => {

        const saved = state.getShowProcessors();

        checkbox.checked = !!saved;

        checkbox.addEventListener('change', () => {

          state.setShowProcessors(
            !!checkbox.checked
          );

        });

      });


      // /* =========================
      //    BUTTON SHOW
      // ========================= */

      once(
        'showProcessorsBtn',
        context.querySelectorAll(
          '[data-drupal-selector^="edit-actions-01-draft"]'
        )
      ).forEach(showBtn => {

        showBtn.addEventListener('click', () => {

          const checkbox =
            document.querySelector('[name="show_processors"]');

          if (!checkbox) return;
            checkbox.checked = true;

          state.setShowProcessors(true);

        });

      });


      // /* =========================
      //    BUTTON RESET
      // ========================= */

      once(
        'showProcessorsReset',
        context.querySelectorAll('.configurator-reset')
      ).forEach(resetBtn => {

        resetBtn.addEventListener('click', () => {
          // Не блокируем штатный submit/AJAX reset формы.
          // Только очищаем frontend state, чтобы restoreCpu не возвращал старый выбор.
  console.log('js reset');
          state.setShowProcessors(false);
          state.setCpu([]);

          const checkbox = document.querySelector('[name="show_processors"]');
          if (checkbox) {
            checkbox.checked = false;
          }

          const selectedText = document.querySelector('[name="selected_processors_text"]');
          if (selectedText) {
            selectedText.value = '';
          }
        });

      });

    }
  };


  // Drupal.behaviors.serverConfiguratorProcessorReset = {
  //   attach(context) {
  //     once('server-configurator-processor-reset', '.configurator-reset', context).forEach((resetBtn) => {
  //       resetBtn.addEventListener('click', () => {
  //         const coresMin = document.querySelector('[name="cores_min"]');
  //         const coresMax = document.querySelector('[name="cores_max"]');
  //         const freqMin = document.querySelector('[name="frequency_min"]');
  //         const freqMax = document.querySelector('[name="frequency_max"]');
  //
  //         if (coresMin) coresMin.value = '2';
  //         if (coresMax) coresMax.value = '144';
  //         if (freqMin) freqMin.value = '1.0';
  //         if (freqMax) freqMax.value = '4.0';
  //
  //         const coresWrapper = document.querySelector('[data-fields="cores"]');
  //         if (coresWrapper) {
  //           const current = coresWrapper.querySelector('.dual-range-current-values');
  //           if (current) current.textContent = '2 — 144';
  //         }
  //
  //         const freqWrapper = document.querySelector('[data-fields="frequency"]');
  //         if (freqWrapper) {
  //           const current = freqWrapper.querySelector('.dual-range-current-values');
  //           if (current) current.textContent = '1.0 — 4.0';
  //         }
  //
  //         const checkbox = document.querySelector('[name="show_processors"]');
  //         if (checkbox) {
  //           checkbox.checked = false;
  //         }
  //       });
  //     });
  //   }
  // };



})(Drupal, once);
