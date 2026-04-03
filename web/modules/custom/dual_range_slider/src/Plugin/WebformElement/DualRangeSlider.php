<?php

namespace Drupal\dual_range_slider\Plugin\WebformElement;

use Drupal\Core\Render\Markup;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformElement\WebformElement;

/**
 * Provides a "Dual Range Slider" Webform element.
 *
 * @WebformElement(
 *   id = "dual_range_slider",
 *   label = @Translation("Dual Range Slider"),
 *   description = @Translation("Provides a dual-thumb slider for selecting a numeric range."),
 *   category = @Translation("Advanced elements")
 * )
 */
class DualRangeSlider extends WebformElement {

    /**
     * {@inheritdoc}
     */
    protected function defineDefaultProperties() {
        return [
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'default_value' => '10,90',
            ] + parent::defineDefaultProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
        parent::prepare($element, $webform_submission);

        $element += [
            '#min' => $this->getDefaultProperty('min'),
            '#max' => $this->getDefaultProperty('max'),
            '#step' => $this->getDefaultProperty('step'),
            '#default_value' => $this->getDefaultProperty('default_value'),
        ];

        $element['#attached']['library'][] = 'dual_range_slider/dual_range_slider';

        // Base name: remove "dual_range_slider_" prefix.
        $full_name = $element['#name'];
        $title = $element['#title'];
        $base = str_replace('dual_range_slider_', '', $full_name);

        $min = (int) $element['#min'];
        $max = (int) $element['#max'];
        $step = (int) $element['#step'];
        $value = htmlspecialchars($element['#default_value'], ENT_QUOTES);

        $markup = '<div class="dual-range-wrapper"
                 data-fields="'. $base .'"
                 data-min="'. $min .'"
                 data-max="'. $max .'"
                 data-step="'. $step .'"
                 data-start="'. $value .'"
               >';

      $markup .= '  <div class="dual-range-labels">
                    <lable class="form-label">'.  $title .' </lable>
                    <span class="dual-range-min hidden">Min: '. $min .'</span>
                    <span class="dual-range-max hidden">Max: '. $max .'</span>
                  </div>';

        $markup .= '  <div class="dual-range-current">
                    <span class="dual-range-current-values">'. $value .'</span>
                  </div>';
      $markup .= '  <div class="dual-range-slider"></div>';

    //   $markup .= '  <div class="dual-range-debug">Debug:
    //                <span class="dual-range-debug-value">'. $value .'</span>
    //              </div>';


        $markup .= '</div>';

        $element['#markup'] = Markup::create($markup);
    }

    public function preview() {
        return [
            '#markup' => '<em>Dual Range Slider preview</em>',
        ];
    }

}
