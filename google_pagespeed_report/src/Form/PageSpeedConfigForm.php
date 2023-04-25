<?php

/**
 * @file
 * Contains Drupal\google_pagespeed_report\Form\GetPageSpeedForm.
 */
namespace Drupal\google_pagespeed_report\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class PageSpeedConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'google_pagespeed.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'Google Page Speed setting';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('google_pagespeed.adminsettings');

    $form['urls'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Add URLs for testing pagespeed'),
      '#description' => $this->t('Add one URL per line.'),
      '#default_value' => $config->get('urls') ? $config->get('urls') : 'https://www.drupal.org',
      '#required' => TRUE,
    ];

    $form['time'] = [
      '#type' => 'number',
      '#title' => $this->t('Data Refresh Time'),
      '#description' => $this->t('Google PageSpeed data refresh timing.'),
      '#default_value' => $config->get('time') ? $config->get('time') : 86400,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $data_refresh_time = $form_state->getValue('time') ? $form_state->getValue('time') : 86400;

    $this->config('google_pagespeed.adminsettings')
      ->set('urls', $form_state->getValue('urls'))
      ->set('time', $data_refresh_time)
      ->save();
  }

}
