// bootstrap Drupal behavior - точка входа:
// создаёт summary DOM
// инициализирует state
// создаёт renderer
// запускает engine
// подключает events


(function (Drupal, once, drupalSettings) {

  Drupal.behaviors.serverConfigurator = {
    attach(context) {
      setTimeout(() => {
        Drupal.serverConfiguratorEngine.recalculate();
      }, 50);

      /* ---------- 1. Summary container ---------- */
      // let summary = context.querySelector('#server-config-summary');
      const summary = document.querySelector('#server-config-summary');
      if (!summary) return;
      // if (!summary) {
      //   // const form = context.querySelector('form');
      //   const form = document.querySelector('form');
      //   if (!form) return;
      //
      //   summary = document.createElement('div');
      //   summary.id = 'server-config-summary';
      //   summary.innerHTML = `
      //     <h2>Итоговая конфигурация</h2>
      //     <div class="summary-server"></div>
      //     <div class="summary-platform"></div>
      //     <div class="summary-cpu"></div>
      //     <div class="summary-form"></div>
      //   `;
      //   form.appendChild(summary);
      // }

      /* ---------- 2. Init state ---------- */
      const state = Drupal.serverConfiguratorState();

      const serverData =
        drupalSettings.serverConfigurator?.server || {};

      const platformData =
        drupalSettings.serverConfigurator?.platform || {};

      state.setServer(serverData);
      state.setPlatform(platformData);



      /* ---------- 3. Init render ---------- */

      if (!Drupal.serverConfiguratorRenderer) {

        Drupal.serverConfiguratorRenderer =
        Drupal.serverConfiguratorRender();

      }

      // const render = Drupal.serverConfiguratorRenderer;
      // render.render(state);


      /* ---------- 4. Init engine ---------- */
// console.log(state);
      Drupal.serverConfiguratorEngine.init(
        state,
        Drupal.serverConfiguratorRenderer
      );





      /* ---------- 5. Init events ---------- */

       // Drupal.serverConfiguratorEvents(context, state, render);
      Drupal.serverConfiguratorEvents(context, state);





  }
  };
})(Drupal, once, drupalSettings);
