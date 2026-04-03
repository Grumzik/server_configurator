(function (Drupal, once) {
  Drupal.behaviors.serverConfiguratorButtonOnScroll = {
    attach(context) {

      const wrapper = once(
        'configurator-wrapper',
        '.js-configurator-wrapper',
        context
      )[0];

      const button = once(
        'configurator-button',
        '.js-configurator-summary',
        context
      )[0];

      if (!wrapper || !button) return;

      const details = wrapper.closest('details');
      if (!details) return;

      const summary = details.querySelector('summary');
      if (!summary) return;

      function onScroll() {
        const wrapperRect = wrapper.getBoundingClientRect();
        const summaryRect = summary.getBoundingClientRect();
        const viewportHeight = window.innerHeight;

        const summaryPassedTop = summaryRect.bottom <= 0;
        const wrapperNotFinished = wrapperRect.bottom > viewportHeight;

        if (summaryPassedTop && wrapperNotFinished) {
          button.classList.add('is-fixed');
          wrapper.classList.add('has-fixed-button');
        } else {
          button.classList.remove('is-fixed');
          wrapper.classList.remove('has-fixed-button');
        }
      }

      window.addEventListener('scroll', onScroll);
      window.addEventListener('resize', onScroll);

      onScroll();
    }
  };
})(Drupal, once);
