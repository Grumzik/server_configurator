(function (Drupal, once) {

    Drupal.behaviors.serverConfiguratorReload = {
        attach(context) {
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) {
                    location.reload();
                }
            });
        }
    };

    /**
     * Раньше здесь state сбрасывался на mousedown у submit/reset.
     * Это ломало сохранение выбранных CPU и другие части state.
     * Оставляем behavior пустым, чтобы не ломать текущую логику.
     */
    Drupal.behaviors.serverConfiguratorResetFix = {
        attach(context) {
            once('server-reset-fix', context).forEach(() => {
                // intentionally empty
            });
        }
    };

    Drupal.behaviors.serverConfiguratorRestore = {
        attach(context) {
            const state = Drupal.serverConfiguratorState();

            once('restoreCpuAfterView', context.querySelectorAll('[data-drupal-selector="edit-processor-for-server"]'))
                .forEach(() => {
                    Drupal.serverConfiguratorRestore.restoreCpu(state);
                    Drupal.serverConfiguratorEngine.recalculate();
                });
        }
    };

    Drupal.behaviors.serverConfiguratorMarks = {
        attach(context) {
            once('server-configurator-marks', document.body).forEach(() => {
                document.body.addEventListener('click', function (e) {
                    let mark = e.target.closest('.range-mark, .range-tick');

                    if (!mark) return;

                    const value = Number(mark.dataset.value);
                    if (isNaN(value)) return;

                    const rangeWrapper = mark.closest('.form-type-range, .js-form-type-range');
                    const rangeInput = rangeWrapper?.querySelector('input[type="range"]');

                    if (rangeInput) {
                        rangeInput.value = value;
                        rangeInput.dispatchEvent(new Event('input', { bubbles: true }));
                        rangeInput.dispatchEvent(new Event('change', { bubbles: true }));
                        rangeInput.focus();
                        return;
                    }

                    const dualWrapper = mark.closest('.dual-range-wrapper');
                    if (!dualWrapper) return;

                    const sliderElement = dualWrapper.querySelector('.dual-range-slider');
                    if (!sliderElement || !sliderElement.noUiSlider) return;

                    const slider = sliderElement.noUiSlider;
                    const currentValues = slider.get().map(Number);

                    const distanceToFirst = Math.abs(currentValues[0] - value);
                    const distanceToSecond = Math.abs(currentValues[1] - value);

                    const handleIndex = distanceToFirst <= distanceToSecond ? 0 : 1;
                    slider.setHandle(handleIndex, value);

                    const handle = sliderElement.querySelectorAll('.noUi-handle')[handleIndex];
                    if (handle) handle.focus();
                });
            });
        }
    };

    Drupal.behaviors.storageStates = {
        attach(context) {
            once('storageStatesRow', 'tr[data-drupal-selector^="edit-kompozitnyy-elemen-storage-items-"]', context).forEach(function (row) {
                const radios = row.querySelectorAll(
                    'input[type="radio"][name*="[tip_ustanovlennyh_nakopiteley]"]'
                );

                const sata = row.querySelector('.storage-volume--sata');
                const nvme = row.querySelector('.storage-volume--nvme');
                const hdd  = row.querySelector('.storage-volume--hdd');

                if (!radios.length || !sata) return;

                function updateVisibility() {
                    const checked = row.querySelector(
                        'input[type="radio"][name*="[tip_ustanovlennyh_nakopiteley]"]:checked'
                    );

                    [sata, nvme, hdd].forEach((el) => {
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

                radios.forEach(function (radio) {
                    radio.addEventListener('change', updateVisibility);
                });

                updateVisibility();
            });

            once(
                'storageRemoveHandler',
                context.querySelectorAll('input[name^="kompozitnyy_elemen_storage_table_remove_"]')
            ).forEach((button) => {
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

    Drupal.behaviors.serverConfiguratorStorage = {
        attach(context) {
            once('storage-slider', context.querySelectorAll('.storage_count'))
                .forEach((slider) => {
                    slider.addEventListener('change', function () {
                        // Reserved for future storage-specific hooks.
                    });
                });
        }
    };

    Drupal.behaviors.showProcessors = {
        attach(context) {
            const state = Drupal.serverConfiguratorState();

            const showProcessorsField = state.getField
                ? state.getField('show_processors', 'show_processors')
                : 'show_processors';

            const selectedProcessorsField = state.getField
                ? state.getField('selected_processors_text', 'selected_processors_text')
                : 'selected_processors_text';

            const coresMinField = state.getProcessorsFilterField
                ? state.getProcessorsFilterField('cores_min', 'cores_min')
                : 'cores_min';

            const coresMaxField = state.getProcessorsFilterField
                ? state.getProcessorsFilterField('cores_max', 'cores_max')
                : 'cores_max';

            const freqMinField = state.getProcessorsFilterField
                ? state.getProcessorsFilterField('frequency_min', 'frequency_min')
                : 'frequency_min';

            const freqMaxField = state.getProcessorsFilterField
                ? state.getProcessorsFilterField('frequency_max', 'frequency_max')
                : 'frequency_max';

            const coresMinDefault = state.getProcessorsDefault
                ? state.getProcessorsDefault('cores_min', '2')
                : '2';

            const coresMaxDefault = state.getProcessorsDefault
                ? state.getProcessorsDefault('cores_max', '144')
                : '144';

            const freqMinDefault = state.getProcessorsDefault
                ? state.getProcessorsDefault('frequency_min', '1.0')
                : '1.0';

            const freqMaxDefault = state.getProcessorsDefault
                ? state.getProcessorsDefault('frequency_max', '4.0')
                : '4.0';

            once(
                'showProcessorsResetMain',
                context.querySelectorAll('input[name="my_reset"], button[name="my_reset"], [data-drupal-selector*="my-reset"]')
            ).forEach((resetBtn) => {
                resetBtn.addEventListener('click', () => {
                    const checkbox = document.querySelector(`[name="${showProcessorsField}"]`);
                    if (checkbox) {
                        checkbox.checked = false;
                    }

                    const coresMin = document.querySelector(`[name="${coresMinField}"]`);
                    const coresMax = document.querySelector(`[name="${coresMaxField}"]`);
                    const freqMin = document.querySelector(`[name="${freqMinField}"]`);
                    const freqMax = document.querySelector(`[name="${freqMaxField}"]`);

                    if (coresMin) coresMin.value = String(coresMinDefault);
                    if (coresMax) coresMax.value = String(coresMaxDefault);
                    if (freqMin) freqMin.value = String(freqMinDefault);
                    if (freqMax) freqMax.value = String(freqMaxDefault);

                    document.querySelectorAll('.dual-range-wrapper').forEach((wrapper) => {
                        const sliderElement = wrapper.querySelector('.dual-range-slider');
                        if (!sliderElement || !sliderElement.noUiSlider) {
                            return;
                        }

                        const fields = wrapper.dataset.fields || '';

                        if (fields === 'cores') {
                            sliderElement.noUiSlider.set([Number(coresMinDefault), Number(coresMaxDefault)]);
                        }

                        if (fields === 'frequency') {
                            sliderElement.noUiSlider.set([Number(freqMinDefault), Number(freqMaxDefault)]);
                        }
                    });

                    // Таблицу скрываем, но выбранные CPU в state НЕ удаляем.
                    state.setShowProcessors(false);

                    // hidden field с выбранными CPU тоже не очищаем — он нужен для восстановления.
                    const selectedText = document.querySelector(`[name="${selectedProcessorsField}"]`);
                    if (selectedText && !selectedText.value) {
                        const selected = state.getCpu ? state.getCpu() : [];
                        selectedText.value = selected.map((cpu, i) => `CPU #${i + 1} ${cpu.pretty}`).join('\n');
                    }
                });
            });
        }
    };

})(Drupal, once);
