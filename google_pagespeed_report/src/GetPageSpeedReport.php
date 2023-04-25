<?php

namespace Drupal\google_pagespeed_report;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Component\Datetime\TimeInterface;
use GuzzleHttp\Exception\ClientException;

/**
 * Class GetPageSpeedReport.
 */
class GetPageSpeedReport {

  CONST PAGESPEED_API_URL = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
  CONST CACHE_ID = 'google_page_speed:cacheResponse';

  protected $_pagespeedResult = [];

  /**
   * GuzzleHttp client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $selfServiceConfig;

  /**
   * The cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * A loggerFactory instance.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * GetPageSpeedReport constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Http Client object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Logger object.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   */
  public function __construct(
    ClientInterface $http_client,
    ConfigFactoryInterface $config_factory,
    CacheBackendInterface $cache,
    LoggerChannelFactory $loggerFactory,
    TimeInterface $time
  ) {
    $this->httpClient = $http_client;
    $this->selfServiceConfig = $config_factory->getEditable('google_pagespeed.adminsettings');
    $this->cache = $cache;
    $this->loggerFactory = $loggerFactory->get('google_pagespeed_report');
    $this->time = $time;
  }

  /**
   * Callback to get response from API service based on request url.
   *
   * @param string $url
   *   Site URL.
   *
   * @return array
   *   Rest API Response object.
   */
  public function generateReport($url, $strategy = 'mobile') {
    $cache_id = self::CACHE_ID . '-' . $url . '-' . $strategy;
    $pagespeed_url = self::PAGESPEED_API_URL;
    $params['url'] = $url;
    $params['strategy'] = $strategy;
    $client = $this->httpClient;

    $headers = [
      'Content-Type' => 'application/json',
    ];
    if (!empty($params)) {
      $pagespeed_url .= '?' . http_build_query($params);
    }

    try {
      if ($cache = $this->cache->get($cache_id)) {
        return $cache->data;
      }

      $response = $client->get($pagespeed_url, [
        'headers' => $headers,
      ]);
      $data = (string) $response->getBody();
      $response_data = json_decode($data, 1);

      $status_code = $response->getStatusCode();

      if ($status_code == 200) {
        $this->loggerFactory->info("Response for @type service is @response", [
          '@type' => 'PageSpeed Status',
          '@response' => json_encode($response_data),
        ]);
        if (is_array($response_data) && array_key_exists('lighthouseResult', $response_data)) {
          $this->cache->set($cache_id, $response_data['lighthouseResult'], $this->time->getRequestTime() + $this->selfServiceConfig->get('google_pagespeed_data_refresh_time'));
        }

        return (array_key_exists('lighthouseResult', $response_data))
          ? $response_data['lighthouseResult'] : $response_data;
      }
      else {
          $this->loggerFactory->error('Status @status in service Response Msg @response - ', [
          '@status' => $status_code,
          '@response' => $response_data
        ]);
      }
    }
    catch (ClientException $e) {
      $this->loggerFactory->error('Error in service Response Msg - ' . print_r($e->getResponse(), TRUE));
    }
  }

}
