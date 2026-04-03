(function (Drupal) {

  Drupal.serverConfiguratorUtils = Drupal.serverConfiguratorUtils || {};

  Drupal.serverConfiguratorUtils.getRowId = function(node) {
    // const selector = node.dataset.drupalSelector;
    const selector = node.getAttribute('data-drupal-selector');
    const match = selector.match(/(\d+)$/);
    return match ? match[1] : null;
  };

})(Drupal);
