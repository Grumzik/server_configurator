<?php

namespace Drupal\webform_nouislider\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElementBase;

/**
 * Provides a 'webform_nouislider' element.
 *
 * @WebformElement(
 *   id = "webform_nouislider",
 *   label = @Translation("noUiSlider"),
 *   description = @Translation("Provides a webform slider element."),
 *   category = @Translation("noUislider elements"),
 * )
 *
 * @see \Drupal\webform_nouislider\Element\WebformnoUiSliderElement
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class WebformnoUiSliderElement extends WebformElementBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    $properties = [
      'minimum' => '1',
      'maximum' => '100',
      'step' => '1',
      'start' => '1',
      'tooltips' => 'false',
      'animate' => 'false',
      'display_vertical' => '',
      'show_input_type' => '',
      'display_vertical_height' => '200',
    ] + parent::defineDefaultProperties();
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['scale'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Scale Settings'),
    ];
    $form['scale']['minimum'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum'),
      '#description' => $this->t('Input for the Scale Minimum Range'),
      '#min' => 0,
      '#max' => 100,
      '#default_value' => 1,
      '#weight' => -55,
    ];
    $form['scale']['maximum'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum'),
      '#description' => $this->t('Input for the Scale Maximum Range'),
      '#min' => 10,
      '#max' => 1000,
      '#default_value' => 1,
      '#weight' => -54,
    ];
    $form['scale']['step'] = [
      '#type' => 'number',
      '#title' => $this->t('Steps'),
      '#description' => $this->t('Input for the scale step'),
      '#min' => 0,
      '#max' => 100,
      '#default_value' => 1,
      '#weight' => -53,
    ];
    $form['scale']['start'] = [
      '#type' => 'number',
      '#title' => $this->t('Start'),
      '#description' => $this->t('The start option sets the number of handles and corresponding start positions.'),
      '#min' => 0,
      '#max' => 100,
      '#default_value' => 1,
      '#weight' => -52,
    ];
    $form['scale']['tooltips'] = [
      '#type' => 'select',
      '#title' => $this->t('Tooltips'),
      '#description' => $this->t('Use this to enable/disable tooltip'),
      '#options' => ['true' => $this->t('Enable'), 'false' => $this->t('Disable')],
      '#default_value' => 'false',
      '#weight' => -51,
    ];
    $form['scale']['animate'] = [
      '#type' => 'select',
      '#title' => $this->t('Set the animate option'),
      '#description' => $this->t('Want to enable animate'),
      '#options' => ['true' => $this->t('Enable'), 'false' => $this->t('Disable')],
      '#default_value' => 'false',
      '#weight' => -50,
    ];
    $form['scale']['show_input_type'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Input box'),
      '#description' => $this->t('Use this option to enable & synchronizing input box with noUiSlider elements.'),
      '#return_value' => TRUE,
      '#weight' => -49,
    ];
    $form['scale']['display_vertical'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display Vertical'),
      '#description' => $this->t('Check this box to set slider position to vertical view.'),
      '#return_value' => TRUE,
      '#weight' => -48,
    ];
    $form['scale']['display_vertical_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Vertical Slider Default height'),
      '#description' => $this->t('Set height for vertical slider.'),
      '#min' => 200,
      '#max' => 1000,
      '#default_value' => 200,
      '#weight' => -47,
    ];
    return $form;
  }

}
