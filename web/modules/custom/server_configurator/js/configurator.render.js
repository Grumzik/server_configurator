// Отвечает только за DOM-рендер summary.
(function (Drupal, drupalSettings) {

  Drupal.serverConfiguratorRender = function () {

    function getSummaryContainer(name) {
      return document.querySelector(`#server-config-summary .${name}`);
    }

    function row(label, value) {
      if (!value) {
        return '';
      }
      return `<div>${label}: <span style="color: #ff820e">${value}</span></div>`;
    }

    function getSummaryFieldDefinitions(state) {
      return state.getConfig?.().summary?.form?.fields || [];
    }

    function getSummarySections(state) {
      return state.getConfig?.().summary || {};
    }

    function isSectionEnabled(state, sectionName) {
      const sections = getSummarySections(state);
      return !!sections?.[sectionName]?.enabled;
    }

    function formatSummaryValue(rawValue, format) {
      if (format === 'yes_if_true') {
        return rawValue ? 'да' : '';
      }
      return rawValue;
    }

    function getDomSelectLabel(fieldName) {
      const select = document.querySelector(`select[name="${fieldName}"]`);
      if (!select || select.selectedIndex < 0) {
        return '';
      }

      const option = select.options[select.selectedIndex];
      if (!option) {
        return '';
      }

      const text = (option.text || '').trim();

      // Placeholder-ы в summary не показываем.
      if (!text || text.startsWith('-')) {
        return '';
      }

      return text;
    }

    function renderServer(server, state) {
      const serverContainer = getSummaryContainer('summary-server');
      if (!serverContainer) {
        return;
      }

      if (!isSectionEnabled(state, 'server')) {
        serverContainer.innerHTML = '';
        return;
      }

      let html = '';
      if (!server || Object.keys(server).length === 0) {
        serverContainer.innerHTML = '';
        return;
      }

      html += `${server.title ? `<h4>Сервер: ${server.title}</h4>` : ''}`;
      html += row('Тип сервера', server.server_types);
      serverContainer.innerHTML = html;
    }

    function renderPlatform(platform, state) {
      const platformContainer = getSummaryContainer('summary-platform');
      if (!platformContainer) {
        return;
      }

      if (!isSectionEnabled(state, 'platform')) {
        platformContainer.innerHTML = '';
        return;
      }

      let html = `<h4>Параметры платформы</h4>`;
      if (!platform || Object.keys(platform).length === 0) {
        platformContainer.innerHTML = '';
        return;
      }

      html += row('Форм-фактор', platform.form_factor);
      html += row('CPU Generation', platform.cpu_generation);
      html += row('CPU Socket Count', platform.cpu_socket_count);
      html += row('TDP платформы', platform.tdp);
      html += row('Форм-фактор накопителей в дюймах', platform.storage_form_factor);
      html += row('Количество отсеков для накопителей', platform.storage_bays);
      html += row('Наличие отказоустойчивого блока питания', platform.psu_type);
      html += row('LAN (установлено)', platform.lan);
      html += row('LAN manager (установлено)', platform.lan_manager);

      platformContainer.innerHTML = html;
    }

    function renderCpu(cpuList, state) {
      const cpuContainer = getSummaryContainer('summary-cpu');
      if (!cpuContainer) {
        return;
      }

      if (!isSectionEnabled(state, 'cpu')) {
        cpuContainer.innerHTML = '';
        return;
      }

      if (!cpuList.length) {
        cpuContainer.innerHTML = '';
        return;
      }

      let html = `<h4>Выбранные процессоры</h4>`;
      html += `<pre>${cpuList.map(c => c.pretty).join('\n----------------\n')}</pre>`;
      cpuContainer.innerHTML = html;
    }

    function renderStorageSummary(storage) {
      const items = Object.values(storage || {});
      if (!items.length) {
        return '';
      }

      return `
        <strong>Накопители:</strong>
        <div>
          ${items.map(item => `
            <div>${item.count} × ${item.type} ${item.volume ?? ''}</div>
          `).join('')}
        </div>
      `;
    }

    function renderConfiguredFormRows(form, state) {
      const definitions = getSummaryFieldDefinitions(state);
      let html = '';
      const mode = state.getMode ? state.getMode() : '';

      definitions.forEach((definition) => {
        if (!definition || !definition.field || !definition.label) {
          return;
        }

        const isTouched = state.isFieldTouched ? state.isFieldTouched(definition.field) : true;

        // В большом конфигураторе показываем только реально выбранные пользователем поля.
        if (mode === 'main_configurator' && !isTouched) {
          return;
        }

        const rawValue =
            form?.[`${definition.field}_label`] ||
            getDomSelectLabel(definition.field) ||
            form?.[definition.field];

        const value = formatSummaryValue(rawValue, definition.format || '');
        html += row(definition.label, value);
      });

      return html;
    }

    function renderFormParams(form, storage, state) {
      const formContainer = getSummaryContainer('summary-form');
      if (!formContainer) {
        return;
      }

      if (!isSectionEnabled(state, 'form')) {
        formContainer.innerHTML = '';
        return;
      }

      let html = '';
      const storageHtml = renderStorageSummary(storage);
      const formRowsHtml = renderConfiguredFormRows(form, state);

      if (!storageHtml && !formRowsHtml) {
        formContainer.innerHTML = '';
        return;
      }

      html += '<h4>Выбранные параметры</h4>';
      html += storageHtml;
      html += formRowsHtml;

      formContainer.innerHTML = html;
    }

    function ensureMarksContainer(element) {
      let container = element.querySelector('.range-marks');
      if (!container) {
        container = document.createElement('div');
        container.className = 'range-marks';
        element.appendChild(container);
      }
      return container;
    }

    function getMajorStep(max) {
      if (max <= 5) return 1;
      if (max <= 15) return 2;
      if (max <= 35) return 5;
      if (max <= 80) return 10;
      return 20;
    }

    function getMinorStep(max) {
      if (max <= 5) return null;
      if (max <= 15) return 1;
      if (max <= 35) return 1;
      if (max <= 80) return 5;
      return 10;
    }

    function isTooCloseToMax(value, max) {
      return (max - value) < (max * 0.05);
    }

    function renderMarksFor(min, max, containerElement) {
      if (!max || max <= min) return;

      const container = ensureMarksContainer(containerElement);
      container.innerHTML = '';

      const majorStep = getMajorStep(max);
      const majorValues = new Set();
      majorValues.add(min);

      for (let v = majorStep; v < max; v += majorStep) {
        if (v > min && !isTooCloseToMax(v, max)) {
          majorValues.add(v);
        }
      }
      majorValues.add(max);

      const minorStep = getMinorStep(max);
      const ticks = [];

      if (minorStep) {
        for (let v = min; v <= max; v += minorStep) {
          ticks.push(v);
        }
      }
      else {
        ticks.push(...majorValues);
      }

      ticks.forEach((value) => {
        const isMajor = majorValues.has(value);
        const mark = document.createElement('span');
        mark.className = isMajor ? 'range-mark' : 'range-tick';
        mark.dataset.value = value;
        mark.style.left = `${((value - min) / (max - min)) * 100}%`;
        if (isMajor) {
          mark.textContent = value;
        }
        container.appendChild(mark);
      });
    }

    return {
      render(state) {
        renderServer(state.getServer(), state);
        renderPlatform(state.getPlatform(), state);
        renderCpu(state.getCpu(), state);
        renderFormParams(state.getForm(), state.getStorage(), state);
      },
      renderMarksFor,
    };
  };

})(Drupal, drupalSettings);
