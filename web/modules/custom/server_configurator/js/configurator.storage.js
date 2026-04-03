(function (Drupal) {

  Drupal.serverConfiguratorStorage = {

   recalcLimits(maxBays) {
      const sliders = Array.from(document.querySelectorAll('.storage_count'));

      let usedBefore = 0;

      sliders.forEach(slider => {

        const current = Number(slider.value || 0);

        const allowedMax = Math.max(maxBays - usedBefore, 0);
        slider.max = allowedMax;
        if (current > allowedMax) {
          slider.value = allowedMax;
          slider.dispatchEvent(new Event('change', { bubbles: true }));
        }

        usedBefore += Number(slider.value || 0);

      });

    },

    updateAddButtons(maxBays) {

      let used = 0;

      document.querySelectorAll('.storage_count').forEach(input => {
        used += Number(input.value || 0);
      });

      const limitReached = used >= maxBays;

      const addButtons =
        document.querySelectorAll('.webform-multiple-add');

      addButtons.forEach(btn => {

        /* 1️⃣ HTML блокировка */
        btn.disabled = limitReached;

        /* 2️⃣ CSS блокировка */
        btn.classList.toggle('limit-reached', limitReached);

        /* 3️⃣ JS защита */
        if (!btn.dataset.limitBound) {

          btn.addEventListener('click', function(e) {

            if (this.disabled) {
              e.preventDefault();
              e.stopPropagation();
              return false;
            }

          });

          btn.dataset.limitBound = true;

        }

        /* 4️⃣ Сообщение о лимите */

        let msg = btn.parentNode.querySelector('.storage-limit-message');

        if (limitReached) {

          if (!msg) {

            msg = document.createElement('span');
            msg.className = 'storage-limit-message';
            msg.textContent = `Достигнут максимальный лимит для выбранной платформы: ${maxBays} накопителей`;

            btn.parentNode.appendChild(msg);

          }

        } else {

          if (msg) {
            msg.remove();
          }

        }

      });

    }

  };

})(Drupal);
