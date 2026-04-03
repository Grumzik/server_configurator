/**
 * @file
 * Range slider behavior.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Process ranges_slider elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webform_nouislider = {
    attach: function attach(context, settings) {
      $(once('number', $(context).find('.form-type-webform-nouislider'))).each(function () {
        var get_input_id = $(this).find('input').attr('id');
        var elements = settings.nouislider_slider && settings.nouislider_slider.elements ? settings.nouislider_slider.elements[get_input_id] : null;
        var desktop_id = get_input_id + '-nouislider';

        if(elements['display_vertical_height']) {
          var vertical_height = 'height: ' + elements['display_vertical_height'] + 'px;';
        }
        else {
          var vertical_height = '';
        }
        $( '#' + get_input_id).after("<div id='" + desktop_id + "' class='slider' style='" + vertical_height + "'></div>");
        var pipsSlider = document.getElementById(desktop_id);
        var result_value = $( '#' + get_input_id ).val();
        if(result_value) {
          var start = result_value;
        }
        else if (elements['start']) {
          var start = elements['start'];
        }
        noUiSlider.create(pipsSlider, {
          range: {
            min: (elements !== null &&  elements['minimum'] ? elements['minimum'] : 0),
            max: (elements !== null &&  elements['maximum'] ? elements['maximum'] : 100)
          },
          start: (start ? start : 0),
          orientation:  (elements !== null &&  elements['display_vertical'] ? 'vertical' : 'horizontal'),
          step: (elements !== null &&  elements['step'] ? elements['step'] : 1),
          tooltips: (elements !== null &&  elements['tooltips'] ? true : false),
          pips: {
            mode: 'count',
            values: 11
          }
        });
        pipsSlider.noUiSlider.on('change.one', function () {
          var getVal = pipsSlider.noUiSlider.get();
          $( '#' + get_input_id ).val(Math.round(getVal));
        });

        //If input box is enabled
        if(elements['show_input_type']) {
          $( '#' + get_input_id ).on('change', function () {
            pipsSlider.noUiSlider.set(this.value);
          });

        }
        else {
          $( '#' + get_input_id ).css("display","none")
        };
      });
    }
  };

})(jQuery, Drupal);
