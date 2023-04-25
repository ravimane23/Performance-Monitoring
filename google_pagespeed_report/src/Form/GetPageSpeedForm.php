<?php
/**
 * @file
 * Contains Drupal\google_pagespeed_report\Form\GetPageSpeedForm.
 */
namespace Drupal\google_pagespeed_report\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class GetPageSpeedForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drupal_get_page_speed_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['info'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Site Performance'),
        'site_url' => [
          '#type' => 'textfield',
          '#title' => $this->t('Site URL'),
          '#description' => $this->t('Please enter site base URL OR any page URL.'),
        ],
        'strategy' => [
          '#type' => 'select',
          '#title' => $this->t('Strategy'),
          '#description' => $this->t('Please the analysis strategy (desktop or mobile) to use, and desktop is the default.'),
          '#options' => [
            'desktop' => $this->t('Desktop'),
            'mobile' => $this->t('Mobile'),
          ],
          '#default_value' => 'desktop',
        ],
        'submit' => [
          '#type' => 'submit',
          '#value' => $this->t('Get Result'),
        ]
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    global $base_url;

    $url = $form_state->getValue('site_url');
    if (!$url) {
      $url = $base_url;
    }

    $strategy = $form_state->getValue('strategy');

    \Drupal::state()->set('site_url', $url);
    \Drupal::state()->set('strategy', $strategy);

    $form_state->setRedirect('google_pagespeed_report.pagespeed_report_controller');
  }

}
