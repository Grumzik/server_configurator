(function (Drupal, once) {
  Drupal.serverConfiguratorRestore = {

    restoreCpu(state) {

      const cpu = state.getCpu();

      document
        .querySelectorAll('input[name^="entity_browser_select"]')
        .forEach(cb => {

          cb.checked = cpu.some(c => c.id == cb.value);

        });

    },



    restoreStorage(state) {

      const rows =
        document.querySelectorAll(
          'tr[data-drupal-selector^="edit-kompozitnyy-elemen-storage-items-"]'
        );

      const storage = {};

      rows.forEach(row => {

        const id =
          Drupal.serverConfiguratorUtils.getRowId(row);

        const type =
          row.querySelector('.storage_types:checked')?.value;

        const count =
          row.querySelector('.storage_count')?.value;

        const volume =
          row.querySelector('.storage-volume:checked')?.value;

        if (type && count && volume) {

          storage[id] = {
            type,
            count,
            volume
          };

        }

      });
      state.setStorage(storage);

    },

  };
})(Drupal, once);
