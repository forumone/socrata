<?php

namespace Drupal\socrata;

use Drupal\Core\Database\Query\SelectExtender;
use Drupal\socrata\Entity\Endpoint;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use GuzzleHttp\Exception\RequestException;

/**
 * @file
 * Decorator class for a Socrata dataset query.
 */
class SocrataSelectQuery extends SelectExtender {

  use DependencySerializationTrait;

  /**
   * Socrata Query Language parameters.
   *
   * @var array
   */
  public $params = [];

  /**
   * Socrata Endpoint object.
   *
   * @var \Drupal\socrata\Entity\Endpoint
   */
  protected $endpoint;

  /**
   * {@inheritdoc}
   */
  public function __construct($query, $connection) {
    parent::__construct($query, $connection);
    $this->setEndpoint();
  }

  /**
   * Get the Socrata source object.
   */
  public function setEndpoint(Endpoint $endpoint = NULL) {
    if (is_null($endpoint)) {
      $tables = $this->query->getTables();
      $table_names = array_keys($tables);
      $id = $table_names[0];
      $endpoint = Endpoint::load($id);
    }
    $this->endpoint = $endpoint;
  }

  /**
   * Execute the query. In our case, this is actually the cURL request.
   *
   * @param string $type
   *   cURL request type, if not the default. The only acceptable value is
   *   currently 'metadata', but we might want others in the future?
   *
   * @return array
   *   Array containing response data.
   */
  public function execute($type = NULL) {
    $return_data = FALSE;
    $client = \Drupal::httpClient();

    try {
      $options = \Drupal::config('socrata.settings')->get('socrata_curl_options');
      \Drupal::moduleHandler()->alter('socrata_curl_options', $options);

      if ($type == 'metadata') {
        // Note that 'metadata' here represents an entirely separate API and
        // endpoint, and is different from the special fields of type
        // 'meta_data' (mentioned below) that are returned from the normal
        // dataset API/endpoint, but when using an old version of the API.
        // @todo Add version number for "old".
        $url = $this->endpoint->getMetaDataURL();
      }
      else {
        // Can build the SODA URL now that all the parameters have been set.
        $url = $this->endpoint->getSodaUrl($this->params);
      }

      _socrata_dbg($url);

      $response = $client->get($url, [
        'curl' => $options,
      ]);

      $return_data['headers'] = $this->parseHeaders($response->getHeaders());

      if (isset($return_data['headers']['x-soda2-fields']) && isset($return_data['headers']['x-soda2-types'])) {
        foreach ($return_data['headers']['x-soda2-fields'] as $idx => $name) {
          $return_data['fields'][$name] = $return_data['headers']['x-soda2-types'][$idx];
        }
      }

      $return_data['data'] = json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (RequestException $e) {
      \Drupal::logger('socrata')->error(
        'Server returned error code @errno for @url',
        [
          '@errno' => $e->getCode(),
          '@url' => $url,
        ]
      );
    }

    return $return_data;
  }

  /**
   * Magic method to return a string for the object.
   *
   * @return string
   *   The Socrata query.
   */
  public function __toString() {
    if ($this->endpoint) {
      $soda_url = $this->endpoint->getUnencodedSodaUrl($this->params);
      $this->query->comment('Socrata URL: "' . $soda_url . '"' . "\r\nCorresponding SQL query: ");
    }
    return (string) $this->query;
  }

  /**
   * Util function to parse out HTTP response headers.
   */
  private function parseHeaders($headers) {
    $headers_arr = [];
    foreach ($headers as $header => $values) {
      if ('HTTP' == substr($header, 0, 4)) {
        continue;
      }
      $name = strtolower(trim($header));
      if (in_array($name, ['x-soda2-fields', 'x-soda2-types'])) {
        $headers_arr[$name] = [];
        foreach ($values as $value) {
          $headers_arr[$name] = array_merge($headers_arr[$name], json_decode($value));
        }
      }
      else {
        $headers_arr[$name] = trim(implode(', ', $values));
      }
    }

    return $headers_arr;
  }

}
