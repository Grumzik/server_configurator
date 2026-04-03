<?php

namespace Drupal\webform_nouislider\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'webform_nouislider'.
 *
 * Webform elements are just wrappers around form elements, therefore every
 * webform element must have correspond FormElement.
 *
 * Below is the definition for a custom 'webform_nouislider' which just
 * renders a simple text field.
 *
 * @FormElement("webform_nouislider")
 *
 * @see \Drupal\Core\Render\Element\FormElement
 * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElement.php/class/FormElement
 * @see \Drupal\Core\Render\Element\RenderElement
 * @see https://api.drupal.org/api/drupal/namespace/Drupal%21Core%21Render%21Element
 * @see \Drupal\webform_nouislider\Element\WebformnoUiSliderElement
 */
class WebformnoUiSliderElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#input' => TRUE,
      '#size' => 60,
      '#process' => [
        [$class, 'processWebformnoUiSliderElement'],
        [$class, 'processAjaxForm'],
      ],
      '#element_validate' => [
        [$class, 'validateWebformnoUiSliderElement'],
      ],
      '#pre_render' => [
        [$class, 'preRenderWebformnoUiSliderElement'],
      ],
      '#theme' => 'input__webform_nouislider',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Processes a 'webform_nouislider' element.
   */
  public static function processWebformnoUiSliderElement(&$element, FormStateInterface $form_state, &$complete_form) {
    if (isset($element['#minimum'])) {
      $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['minimum'] = $element['#minimum'];
    }
    if (isset($element['#maximum'])) {
      $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['maximum'] = $element['#maximum'];
    }
    if (isset($element['#step'])) {
      $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['step'] = $element['#step'];
    }
    if (isset($element['#start'])) {
      $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['start'] = $element['#start'];
    }
    if (isset($element['#tooltips'])) {
      $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['tooltips'] = $element['#tooltips'];
    }
    if (isset($element['#animate'])) {
      $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['animate'] = $element['#animate'];
    }
    if (isset($element['#display_vertical'])) {
      $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['display_vertical'] = 'vertical';
      if (isset($element['#display_vertical_height'])) {
        $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['display_vertical_height'] = $element['#display_vertical_height'];
      }
      else {
        $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['display_vertical_height'] = '200';
      }
    }
    if (isset($element['#show_input_type'])) {
      $element['#attached']['drupalSettings']['nouislider_slider']['elements'][$element['#id']]['show_input_type'] = 'show_input_type';
    }
    $element['#attached']['library'][] = 'webform_nouislider/element.webform_nouislider';
    return $element;
  }

  /**
   * Webform element validation handler for #type 'webform_nouislider'.
   */
  public static function validateWebformnoUiSliderElement(&$element, FormStateInterface $form_state, &$complete_form) {
    // Here you can add custom validation logic.
  }

  /**
   * Prepares a #type 'email_multiple' render element for theme_element().
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for theme_element().
   */
  public static function preRenderWebformnoUiSliderElement(array $element) {
    $element['#attributes']['type'] = 'number';
    Element::setAttributes(
      $element, ['id', 'name', 'value', 'size', 'maxlength', 'placeholder']
    );
    static::setAttributes($element, ['form-text', 'webform-nouislider-element']);
    return $element;
  }

}
