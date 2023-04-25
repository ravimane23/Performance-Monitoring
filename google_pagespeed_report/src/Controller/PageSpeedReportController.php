<?php

namespace Drupal\google_pagespeed_report\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Print PageSpeed Report details.
 */
class PageSpeedReportController extends ControllerBase {

  /**
   * Render data object.
   *
   * @var array
   */
  private $renderData;

  /**
   * Renders details page.
   *
   * @return array
   *   Index page
   */
  public function details() {
    $build = [];

    $config = \Drupal::configFactory()->getEditable('google_pagespeed.adminsettings');
    $check_urls = explode("\r\n", $config->get('urls'));

    $output = [];
    foreach ($check_urls as $urls) {
      $statistics = \drupal::service('google_pagespeed_report.get_google_pagespeed_report_report')->generateReport($urls);
      $this->renderData = $statistics;
      $output[$urls] = $this->getOutputData($statistics);
    }

    if ($output) {
      $build['content'] = [
        '#theme' => 'google_pagespeed_report',
        '#statistics_data' => $output,
        '#information_data' => $this->getInfoData(),
      ];
    }
    else {
      $build['content'] = [
        '#theme' => 'google_pagespeed_report',
        '#message' => $this->t('No data present. Please try again after clearing cache.'),
      ];
    }
    return $build;
  }

  protected function getInfoData() {
    return [
      'FCP' => $this->t('First Contentful Paint'),
      'SI' => $this->t('Speed Index'),
      'LCP' => $this->t('Largest Contentful Paint'),
      'TTI' => $this->t('Time To Interact'),
      'TBT' => $this->t('Total Blocking Time'),
      'CLS' => $this->t('Cumulative Layout Shift'),
      'FNP' => $this->t('First Meaningful Paint'),
    ];
  }

  /**
   * Cast api object to rendered array.
   *
   * @return array
   *   Returns output.
   */
  public function getOutputData(array $response): array {
    $output = '';

    if (!empty($response)) {
      $render_fields = $this->fieldsMapping();
      foreach ($render_fields as &$render_field) {
        $render_field = $this->renderData['audits'][$render_field]['displayValue'] ?? 0;
      }
      unset($render_field);

      $render_fields = array_filter($render_fields);
      foreach ($render_fields as $key => &$value) {
        $value = [$key, $value];
      }

      return $render_fields;
    }

  }

  /**
   * Map labels and fields.
   *
   * @param string $bundle
   *   Content type name for mapping.
   *
   * @return array
   *   Mapped fields.
   */
  protected function fieldsMapping(): array {
    return [
      (string) $this->t('Performance') => 'performance',
      (string) $this->t('Accessibility') => 'accessibility',
      (string) $this->t('Best Practice') => 'best_practice',
      (string) $this->t('SEO') => 'seo',
      (string) $this->t('FCP') => 'first-contentful-paint',
      (string) $this->t('SI') => 'speed-index',
      (string) $this->t('LCP') => 'largest-contentful-paint',
      (string) $this->t('TTI') => 'interactive',
      (string) $this->t('TBT') => 'total-blocking-time',
      (string) $this->t('CLS') => 'cumulative-layout-shift',
      (string) $this->t('FNP') => 'first-meaningful-paint',
    ];
  }

}
