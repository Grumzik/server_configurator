(function (Drupal, once) {

  Drupal.serverConfiguratorStorage = {

    init(context, state) {
      this.bindRowCollection(context, state);
      this.bindRemoveButtons(context, state);
      this.restoreRowsFromState(state);
      this.refresh(state);
    },

    refresh(state) {
      const platform = state.getPlatform() || {};
      const maxBays = Number(platform.storage_bays || 0);

      this.recalcLimits(maxBays);
      this.updateAddButtons(maxBays);
    },

    bindRowCollection(context, state) {
      const selector = 'tr[data-drupal-selector^="edit-kompozitnyy-elemen-storage-items-"]';

      once('server-configurator-storage-row', selector, context).forEach((row) => {
        const handler = () => {
          this.collectFromDom(state);
        };

        row.addEventListener('change', handler);
        row.addEventListener('input', handler);
      });
    },

    bindRemoveButtons(context, state) {
      const selector = 'input[name^="kompozitnyy_elemen_storage_table_remove_"]';

      once('server-configurator-storage-remove', selector, context).forEach((button) => {
        button.addEventListener('mousedown', () => {
          const row = button.closest('tr');
          if (!row) {
            return;
          }

          const id = Drupal.serverConfiguratorUtils.getRowId(row);
          if (id) {
            state.removeStorageItem(id);
          }
        });
      });
    },

    collectFromDom(state) {
      const rows = document.querySelectorAll('tr[data-drupal-selector^="edit-kompozitnyy-elemen-storage-items-"]');
      const storage = {};

      rows.forEach((row) => {
        const id = Drupal.serverConfiguratorUtils.getRowId(row);
        if (!id) {
          return;
        }

        const typeInput = row.querySelector('.storage_types:checked');
        const type = typeInput ? typeInput.value.trim() : null;

        const countInput = row.querySelector('.storage_count');
        const count = countInput ? Number(countInput.value || 0) : 0;

        const volumeInput = row.querySelector('.storage-volume input:checked');
        const volume = volumeInput ? volumeInput.value : null;

        if (type || count > 0 || volume) {
          storage[id] = {
            type,
            count,
            volume,
          };
        }
      });

      state.setStorage(storage);
    },

    restoreRowsFromState(state) {
      const storage = state.getStorage() || {};
      const rows = document.querySelectorAll('tr[data-drupal-selector^="edit-kompozitnyy-elemen-storage-items-"]');

      rows.forEach((row) => {
        const id = Drupal.serverConfiguratorUtils.getRowId(row);
        const saved = id ? storage[id] : null;

        if (!saved) {
          return;
        }

        if (saved.type) {
          const typeInput = row.querySelector(`.storage_types[value="${CSS.escape(saved.type)}"]`);
          if (typeInput) {
            typeInput.checked = true;
            typeInput.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }

        const countInput = row.querySelector('.storage_count');
        if (countInput && saved.count !== undefined && saved.count !== null) {
          countInput.value = Number(saved.count || 0);
          countInput.dispatchEvent(new Event('input', { bubbles: true }));
          countInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (saved.volume) {
          const safeVolume = typeof CSS !== 'undefined' && CSS.escape ? CSS.escape(saved.volume) : saved.volume;
          const volumeInput = row.querySelector(`.storage-volume input[value="${safeVolume}"]`);
          if (volumeInput) {
            volumeInput.checked = true;
            volumeInput.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }
      });
    },

    recalcLimits(maxBays) {
      const sliders = Array.from(document.querySelectorAll('.storage_count'));
      let usedBefore = 0;

      sliders.forEach((slider) => {
        const current = Number(slider.value || 0);
        const allowedMax = Math.max(maxBays - usedBefore, 0);

        slider.max = allowedMax;

        if (current > allowedMax) {
          slider.value = allowedMax;
          slider.dispatchEvent(new Event('input', { bubbles: true }));
          slider.dispatchEvent(new Event('change', { bubbles: true }));
        }

        usedBefore += Number(slider.value || 0);
      });
    },

    updateAddButtons(maxBays) {
      let used = 0;

      document.querySelectorAll('.storage_count').forEach((input) => {
        used += Number(input.value || 0);
      });

      const limitReached = used >= maxBays;
      const addButtons = document.querySelectorAll('.webform-multiple-add');

      addButtons.forEach((btn) => {
        btn.disabled = limitReached;
        btn.classList.toggle('limit-reached', limitReached);

        if (!btn.dataset.limitBound) {
          btn.addEventListener('click', function (e) {
            if (this.disabled) {
              e.preventDefault();
              e.stopPropagation();
              return false;
            }
          });

          btn.dataset.limitBound = '1';
        }

        let msg = btn.parentNode.querySelector('.storage-limit-message');

        if (limitReached) {
          if (!msg) {
            msg = document.createElement('span');
            msg.className = 'storage-limit-message';
            msg.textContent = `Достигнут максимальный лимит для выбранной платформы: ${maxBays} накопителей`;
            btn.parentNode.appendChild(msg);
          }
        }
        else if (msg) {
          msg.remove();
        }
      });
    }

  };

})(Drupal, once);
