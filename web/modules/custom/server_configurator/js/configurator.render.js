// Отвечает ТОЛЬКО за DOM
//
(function (Drupal) {

  Drupal.serverConfiguratorRender = function () {

    /* ============================================================
            HELPERS
============================================================ */

    function getSummaryContainer(name){
      const container =  document.querySelector(`#server-config-summary .${name}`);
      if (!container) {
          return null;
      }
      return container;
    }

    function row (label, value){
      if (!value) return '';
      return `<div>${label}:  <span style="color: #ff820e">${value}<span></div>`;
    }

    /* ============================================================
            SUMMARY
============================================================ */

    function renderServer(server){

      const serverContainer = getSummaryContainer('summary-server');
      if (!serverContainer){
        console.log("ServerContainer container not found:  ", name);
        return;
      }
      let html = '';
      if (!server || Object.keys(server).length === 0) {
         html += `
          <div>Данные сервера не определены</div>
        `;
        return;
      }
      html += ` ${server.title ? `<h4>Сервер: ${server.title} </h4>` : ''} `;
      html += row('Тип сервера', server.server_types);
      serverContainer.innerHTML = html;
    }

    function renderPlatform(platform) {

      const platformContainer = getSummaryContainer('summary-platform');
      if (!platformContainer) {
        return;
      }
      let html = `<h4>Параметры платформы</h4>`;
      if (!platform || Object.keys(platform).length === 0) {
        html += `
          <div>Данные платформы не определены</div>
        `;
        return;
      }

      html += row('Форм-фактор', platform.form_factor);
      html += row('CPU Generation', platform.cpu_generation);
      html += row('TDP платформы', platform.tdp);
      html += row('Форм-фактор накопителей в дюймах', platform.storage_form_factor);
      html += row('Количество отсеков для накопителей', platform.storage_bays);
      html += row('Наличие отказоустойчивого блока питания', platform.psu_type);
      html += row('LAN (установлено) ', platform.lan);
      html += row('LAN manager (установлено) ', platform.lan_manager);

      platformContainer.innerHTML = html;
    }

    function renderCpu(cpuList) {

      const cpuContainer = getSummaryContainer('summary-cpu');
      if (!cpuContainer) {
        return;
      }
      let html = `<h4>Выбранные процессоры</h4>`;

      if (!cpuList.length) {
        cpuContainer.innerHTML = `
          <div>Процессоры не выбраны</div>
        `;
        return;
      }

      html += `<pre>
${cpuList.map(c => c.pretty).join('\n----------------\n')}
        </pre> `;
      cpuContainer.innerHTML = html;
    }



    function renderFormParams(form, storage){


      const formContainer = getSummaryContainer('summary-form');
      if (!formContainer) {
        return;
      }
      let html = '<h4>Выбранные параметры</h4>';

      if (!form || Object.keys(form).length === 0) {
        html += `
          <div>Параметры не выбраны</div>
        `;
        // return;
      }

      const items = Object.values(storage);
      html += row('Количество процессоров', form.kolichestvo_processorov);
      html += row('Объем оперативной памяти в Gb', form.obem_operativnoy_pamyati_v_gb );
      if (items.length){
        html += `
          <strong>Накопители:</strong>
        <div>
           ${items.map(item => `
            <div>
              ${item.count} × ${item.type} ${item.volume ?? ''}
            </div>
          `).join(' ')}
        </div>
        `;
      }
      html += row('Желаемый общий объем дисковой подсистемы', form.ili_ukazhite_zhelaemyy_obshchiy_obem_diskovoy_podsistemy);
      html += row('Наличие отдельного накопителя для установки ОС',  form.nalichie_otdelnogo_nakopitelya_dlya_ustanovki_os );
      html += row('Наличие аппратного дискового контроллера', form.nalichie_appratnogo_diskovogo_kontrollera);
      html += row('Наличие адаптера Fibre Channel', form.nalichie_adaptera_fibre_channel_dual_port);
      html += row('Добавить дополнительный сетевой адаптер', form.add_setevoy_interfeys? 'да' : '');
      html += row('Тип сетевых интерфейсов', form.tip_setevyh_interfeysov);
      html += row('Количество сетевых портов', form.kolichestvo_setevyh_portov );
      html += row('Скорость сетевых портов Гбит/сек', form.skorost_setevyh_portov_gbit_sek_med);
      html += row('Скорость сетевых портов Гбит/сек', form.skorost_setevyh_portov_gbit_sek_optika);

      formContainer.innerHTML = html;
    }


// // Отображение max-максимальное значение  радиокнопок Количество  отсеков для накопителей в зависимоти от field_storage_bays
//     function filterStorageCountOptions(maxBays) {
//       document
//         .querySelectorAll('.storage_count')
//         .forEach((radio) => {
//           const value = Number(radio.value);
//
//           const wrapper = radio.closest('.form-item');
//           if (!wrapper) return;
//
//           if (value > maxBays) {
//             wrapper.style.display = 'none';
//             radio.disabled = true;
//           } else {
//             wrapper.style.display = '';
//             radio.disabled = false;
//           }
//         });
//     }




    // Ограничение range-слайдера по количеству отсеков для элемента формы range
    function filterStorageCountOptions(maxBays) {
      const slider = document.querySelector('.storage_count');
      if (!slider) return;
      const max = Number(maxBays);
      if (!max || max <= 0) return;

      // Устанавливаем максимальное значение
      slider.max = max;

      // Если текущее значение больше допустимого — обрезаем
      if (Number(slider.value) > max) {
        slider.value = max;

        // Если используешь state — обязательно обновить
        slider.dispatchEvent(new Event('change', { bubbles: false }));
      }

    }


    /* ============================================================
   Шкала слайдера FIXED SCALE SYSTEM (MAJOR + MINOR TICKS)
============================================================ */

    function ensureMarksContainer(element) {

      let container = element.querySelector('.range-marks');

      if (!container) {
        container = document.createElement('div');
        container.className = 'range-marks';
        element.appendChild(container);
      }

      return container;
    }


    /* Определяем major шаг */
    function getMajorStep(max) {

      if (max <= 5) return 1;
      if (max <= 15) return 2;
      if (max <= 35) return 5;
      if (max <= 80) return 10;

      return 20;
    }


    /* Определяем minor шаг */
    function getMinorStep(max) {

      if (max <= 5) return null;
      if (max <= 15) return 1;
      if (max <= 35) return 1;
      if (max <= 80) return 5;

      return 10;
    }


    /* Проверка "слишком близко к max" */
    function isTooCloseToMax(value, max) {
      return (max - value) < (max * 0.05); // 5% от диапазона
    }

    /* Отрисовка шкалы */
    function renderMarksFor(min, max, containerElement) {

      if (!max || max <= min) return;

      const container = ensureMarksContainer(containerElement);
      container.innerHTML = '';

      const totalRange = max - min;
      const majorStep = getMajorStep(max);
      const minorStep = getMinorStep(max);

      const majorValues = new Set();

      // всегда min
      majorValues.add(min);

      // генерируем major по фиксированному шагу
      for (let v = majorStep; v < max; v += majorStep) {

        if (v > min && !isTooCloseToMax(v, max)) {
          majorValues.add(v);
        }
      }

      // всегда max
      majorValues.add(max);

      // === Отрисовка ===

      for (let value = min; value <= max; value++) {

        const percent = ((value - min) / totalRange) * 100;

        // Major
        if (majorValues.has(value)) {

          const mark = document.createElement('span');
          mark.className = 'range-mark';
          mark.style.left = percent + '%';
          mark.textContent = value;
          mark.dataset.value = value;
          container.appendChild(mark);

          continue;
        }

        // Minor
        if (minorStep && (value % minorStep === 0)) {

          const tick = document.createElement('span');
          tick.className = 'range-tick';
          tick.style.left = percent + '%';
          container.appendChild(tick);
        }
      }
    }


    /* Отрисовка подписей шкалы  элементов слайдеров. */
    function renderMarks() {

      // Обычные range
      document.querySelectorAll('input[type="range"]').forEach((slider) => {

        const min = Number(slider.min) || 0;
        const max = Number(slider.max);

        if (!max || max <= min) return;

        renderMarksFor(min, max, slider.parentElement);
      });


      /* ============================================================
     СЛАЙДЕР С ДВУМЯ ПОЛЗУНКАМИ  DUAL RANGE
============================================================ */

      // Dual range
      document.querySelectorAll('.dual-range-wrapper').forEach((wrapper) => {

        const min = Number(wrapper.dataset.min);
        const max = Number(wrapper.dataset.max);

        if (!max || max <= min) return;

        renderMarksFor(min, max, wrapper);
      });
    }


    /* ============================================================
   Отображение  типов накопителей в зависимости от форм-фактора накопителей
============================================================ */

    function filterStorageTypes(storageFormFactor) {

      document
        .querySelectorAll('.storage_types')
        .forEach((radio) => {
          let value = radio.value;
          if(value){
            value = value.trim();
          }

          const wrapper = radio.closest('.form-item');
          if (!wrapper) return;
          if (storageFormFactor === 2.5 &&  value === 'HDD') {
            wrapper.style.display = 'none';
            radio.disabled = true;
          } else {
            wrapper.style.display = '';
            radio.disabled = false;
          }
        });
    }



    return {
      render(state) {
        const webform =
          document.querySelector('.webform-submission-server-configurator-form');

        if (!webform) {
          console.log('Это не вебформа конфигуратора! не выполянем events');
          return;
        }
        const server  = state.getServer();
        if (server){renderServer(server)}
        const platform = state.getPlatform();
        if (platform){renderPlatform(platform)}
        renderCpu(state.getCpu());
        renderFormParams(state.getForm() || {}, state.getStorage() || {});
        renderMarks();
        if (state.getPlatform().storage_form_factor) {
          filterStorageTypes(state.getPlatform().storage_form_factor);
        }
      }
    };

  };

})(Drupal);
