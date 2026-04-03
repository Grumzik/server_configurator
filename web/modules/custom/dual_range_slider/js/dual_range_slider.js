(function (Drupal) {
  'use strict';

  Drupal.behaviors.dualRangeNoUi_v6 = {
    attach: function (context) {

      const sliders = context.querySelectorAll('.dual-range-slider:not([data-initialized])');
      if (!sliders.length) return;

      sliders.forEach(function (sliderEl) {

        sliderEl.setAttribute('data-initialized', '1');

        const wrapper = sliderEl.closest('.dual-range-wrapper');
        if (!wrapper) return;

        const base = wrapper.dataset.fields;

        const fieldMin = document.querySelector(`input[name="${base}_min"]`);
        const fieldMax = document.querySelector(`input[name="${base}_max"]`);

        if (!fieldMin || !fieldMax) {
          console.error("[dual_range_slider] ERROR: min/max fields NOT found for key:", base);
          return;
        }

        const min = parseFloat(wrapper.dataset.min || 0);
        const max = parseFloat(wrapper.dataset.max || 100);
        const step = parseFloat(wrapper.dataset.step || 1);

        let startValues;

        if (fieldMin.value !== '' && fieldMax.value !== '') {
          startValues = [parseFloat(fieldMin.value), parseFloat(fieldMax.value)];
        } else {
          startValues = wrapper.dataset.start.split(',').map(v => parseFloat(v));
        }

        if (typeof noUiSlider === 'undefined') {
          console.error("noUiSlider not loaded!");
          return;
        }

        noUiSlider.create(sliderEl, {
          start: startValues,
          connect: true,
          step: step,
          animate: true,
          range: {
            min: min,
            max: max
          }
        });

        // ======================================================
        // ДЕЛАЕМ HANDLE FOCUSABLE (ключевой момент)
        // ======================================================

        const handles = sliderEl.querySelectorAll('.noUi-handle');
        handles.forEach(handle => {
          handle.setAttribute('tabindex', '0');
        });

        const currentValues = wrapper.querySelector('.dual-range-current-values');
        const debug = wrapper.querySelector('.dual-range-debug-value');

        // UPDATE (твоя логика)
        sliderEl.noUiSlider.on('update', function (values) {

          let v1;
          let v2;

          if (base === 'frequency') {
            v1 = parseFloat(values[0]).toFixed(1);
            v2 = parseFloat(values[1]).toFixed(1);
          } else {
            v1 = Math.round(values[0]);
            v2 = Math.round(values[1]);
          }

          if (currentValues) currentValues.textContent = `${v1} — ${v2}`;
          if (debug) debug.textContent = `${v1},${v2}`;

          fieldMin.value = v1;
          fieldMax.value = v2;

          fieldMin.dispatchEvent(new Event('input', { bubbles: true }));
          fieldMax.dispatchEvent(new Event('input', { bubbles: true }));
        });

        // ======================================================
        // ФОКУС ПРИ КЛИКЕ ПО ШКАЛЕ
        // ======================================================

        sliderEl.addEventListener('pointerdown', function (e) {

          if (e.target.classList.contains('noUi-handle')) return;

          const rect = sliderEl.getBoundingClientRect();
          const clickX = e.clientX - rect.left;
          const percent = clickX / rect.width;

          const values = sliderEl.noUiSlider.get();
          const v1 = parseFloat(values[0]);
          const v2 = parseFloat(values[1]);

          const pos1 = (v1 - min) / (max - min);
          const pos2 = (v2 - min) / (max - min);

          const handles = sliderEl.querySelectorAll('.noUi-handle');

          const targetHandle =
            Math.abs(percent - pos1) < Math.abs(percent - pos2)
              ? handles[0]
              : handles[1];

          if (targetHandle) {
            targetHandle.focus();
          }

        });

      });
    }
  };

})(Drupal);
