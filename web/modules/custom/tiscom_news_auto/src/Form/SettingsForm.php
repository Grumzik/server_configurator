<?php

namespace Drupal\tiscom_news_auto\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for Tiscom News Auto.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'tiscom_news_auto_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['tiscom_news_auto.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('tiscom_news_auto.settings');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable automatic import on cron'),
      '#default_value' => $config->get('enabled') ?? FALSE,
    ];

    $form['source'] = [
      '#type' => 'details',
      '#title' => $this->t('Source settings'),
      '#open' => TRUE,
    ];
    $form['source']['techspot_url'] = [
      '#type' => 'url',
      '#title' => $this->t('TechSpot Hardware URL'),
      '#default_value' => $config->get('techspot_url') ?: 'https://www.techspot.com/category/hardware/',
      '#required' => TRUE,
    ];
    $form['source']['count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of news drafts'),
      '#default_value' => $config->get('count') ?: 5,
      '#min' => 1,
      '#max' => 20,
    ];
    $form['source']['days_back'] = [
      '#type' => 'number',
      '#title' => $this->t('Look back period, days'),
      '#default_value' => $config->get('days_back') ?: 7,
      '#min' => 1,
      '#max' => 60,
    ];
    $form['source']['cron_interval'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum interval between automatic imports, seconds'),
      '#default_value' => $config->get('cron_interval') ?: 604800,
      '#min' => 3600,
    ];

    $form['drupal'] = [
      '#type' => 'details',
      '#title' => $this->t('Drupal news settings'),
      '#open' => TRUE,
    ];
    $form['drupal']['content_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content type machine name'),
      '#default_value' => $config->get('content_type') ?: 'news',
      '#required' => TRUE,
    ];
    $form['drupal']['anons_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Announcement field'),
      '#default_value' => $config->get('anons_field') ?: 'field_news_anons',
      '#required' => TRUE,
    ];
    $form['drupal']['image_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image field'),
      '#default_value' => $config->get('image_field') ?: 'field_image',
      '#required' => TRUE,
    ];
    $form['drupal']['image_directory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image directory'),
      '#default_value' => $config->get('image_directory') ?: 'public://news-auto/',
      '#required' => TRUE,
    ];
    $form['drupal']['import_images'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Download source images into image field'),
      '#default_value' => $config->get('import_images') ?? TRUE,
      '#description' => $this->t('Temporary mode. Check image usage rights before using this in production.'),
    ];

    $form['openai'] = [
      '#type' => 'details',
      '#title' => $this->t('OpenAI settings'),
      '#open' => TRUE,
    ];
    $form['openai']['openai_api_key'] = [
      '#type' => 'password',
      '#title' => $this->t('OpenAI API key'),
      '#default_value' => $config->get('openai_api_key') ?: '',
      '#description' => $this->t('Stored in Drupal config. For production, consider key/private config management.'),
    ];
    $form['openai']['openai_model'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenAI model'),
      '#default_value' => $config->get('openai_model') ?: 'gpt-4.1-mini',
      '#required' => TRUE,
    ];

    $form['telegram'] = [
      '#type' => 'details',
      '#title' => $this->t('Telegram settings'),
      '#open' => TRUE,
    ];
    $form['telegram']['telegram_bot_token'] = [
      '#type' => 'password',
      '#title' => $this->t('Telegram Bot Token'),
      '#default_value' => $config->get('telegram_bot_token') ?: '',
    ];
    $form['telegram']['telegram_chat_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Telegram Chat ID'),
      '#default_value' => $config->get('telegram_chat_id') ?: '',
    ];
    $form['telegram']['telegram_webhook_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Telegram webhook secret'),
      '#default_value' => $config->get('telegram_webhook_secret') ?: bin2hex(random_bytes(16)),
      '#required' => TRUE,
      '#description' => $this->t('Use this secret in the Telegram webhook URL.'),
    ];

    $form['manual'] = [
      '#type' => 'details',
      '#title' => $this->t('Manual start'),
      '#open' => TRUE,
    ];
    $form['manual']['run_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Run import now'),
      '#url' => \Drupal\Core\Url::fromRoute('tiscom_news_auto.manual_run'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('tiscom_news_auto.settings')
      ->set('enabled', (bool) $form_state->getValue('enabled'))
      ->set('techspot_url', $form_state->getValue('techspot_url'))
      ->set('count', (int) $form_state->getValue('count'))
      ->set('days_back', (int) $form_state->getValue('days_back'))
      ->set('cron_interval', (int) $form_state->getValue('cron_interval'))
      ->set('content_type', $form_state->getValue('content_type'))
      ->set('anons_field', $form_state->getValue('anons_field'))
      ->set('image_field', $form_state->getValue('image_field'))
      ->set('image_directory', $form_state->getValue('image_directory'))
      ->set('import_images', (bool) $form_state->getValue('import_images'))
      ->set('openai_api_key', $form_state->getValue('openai_api_key') ?: $this->config('tiscom_news_auto.settings')->get('openai_api_key'))
      ->set('openai_model', $form_state->getValue('openai_model'))
      ->set('telegram_bot_token', $form_state->getValue('telegram_bot_token') ?: $this->config('tiscom_news_auto.settings')->get('telegram_bot_token'))
      ->set('telegram_chat_id', $form_state->getValue('telegram_chat_id'))
      ->set('telegram_webhook_secret', $form_state->getValue('telegram_webhook_secret'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
