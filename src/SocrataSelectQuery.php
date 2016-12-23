<?php
namespace Drupal\socrata;

use Drupal\Core\Database\Query\SelectExtender;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\socrata\Entity\Endpoint;

/**
 * @file
 * Decorator class for a Socrata dataset query
 */

class SocrataSelectQuery extends SelectExtender {

  /**
   * Socrata Query Language parameters.
   */
  public $params = array();

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
   * @param $type string
   *   cURL request type, if not the default. The only acceptable value is
   *   currently 'metadata', but we might want others in the future?
   *
   * @return array
   */
  public function execute($type = NULL) {
    $retval = FALSE;

    // Create a new cURL resource.
    $ch = curl_init();
    if ($ch) {
      // Pull and set custom curl options.
      $curlopts = \Drupal::config('socrata.settings')->get('socrata_curl_options');
      \Drupal::moduleHandler()->alter('socrata_curl_options', $curlopts);
      curl_setopt_array($ch, $curlopts);

      // Set required curl options.
      $req_curlopts = array(
        CURLOPT_HEADER => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
      );
      curl_setopt_array($ch, $req_curlopts);

      // Execute the request and cleanup.
      curl_setopt_array($ch, $curlopts);
      do {
        $retval = FALSE;

        // Construct the URL, based on the $type parameter.
        if ($type == 'metadata') {
          // Note that 'metadata' here represents an entirely separate API and
          // endpoint, and is different from the special fields of type
          // 'meta_data' (mentioned below) that are returned from the normal
          // dataset API/endpoint, but when using an old version of the API.
          // @todo Add version number for "old".
          $curl_url = $this->endpoint->getMetaDataURL();
        }
        else {
          // Can build the SODA URL now that all the parameters have been set.
          $curl_url = $this->endpoint->getSodaURL($this->params);
        }
        _socrata_dbg($curl_url);

        curl_setopt($ch, CURLOPT_URL, $curl_url);
        $resp = curl_exec($ch);
        if (FALSE !== $resp) {
          // Pull info from response and see if we had an error.
          $info = curl_getinfo($ch);
          if ($info['http_code'] >= 400) {
            \Drupal::logger('socrata')->error(
              'Server returned error code @errno for @url',
              [
                '@errno' => $info['http_code'],
                '@url' => $curl_url,
              ]
            );

            break;
          }
          else {
            // Split out response headers into name => value array.
            list($headers, $data) = explode("\r\n\r\n", $resp, 2);
            $retval['headers'] = _socrata_parse_headers($headers);

            // Test for redirect in the event curl wasn't able to automagically
            // follow due to server config.
            if (!empty($retval['headers']['location'])) {
              $url = $retval['headers']['location'];
            }
            else {
              // Generate an array mapping fields to types, if provided
              // Note that the old API returns some fields of type 'meta_data'
              // (e.g. ':id', ':created_at', ':updated_at').
              // @todo Add version number for "old".
              if (isset($retval['headers']['x-soda2-fields']) && isset($retval['headers']['x-soda2-types'])) {
                foreach ($retval['headers']['x-soda2-fields'] as $idx => $name) {
                  $retval['fields'][$name] = $retval['headers']['x-soda2-types'][$idx];
                }
              }

              // Decode data payload.
              $retval['data'] = json_decode($data, TRUE);
            }
          }
        }
        else {
          \Drupal::logger('socrata')->error(
            'curl_exec failed: @error [@errno] for @url',
            [
              '@error' => curl_error($ch),
              '@errno' => curl_errno($ch),
              '@url' => $curl_url,
            ]
          );

        }
      } while (FALSE !== $resp && !empty($retval['headers']['location']));

      // Close and cleanup.
      curl_close($ch);
    }
    else {
      \Drupal::logger('socrata')->error(
        'curl_init failed: @error [@errno]',
        [
          '@error' => curl_error($ch),
          '@errno' => curl_errno($ch),
        ]
      );
    }

    return $retval;
  }

  /**
   * Magic method to return a string for the object.
   *
   * @return string
   *   The Socrata query.
   */
  public function __toString() {
    if ($this->endpoint) {
      $soda_url = $this->endpoint->getUnencodedSodaURL($this->params);
      $this->query->comment('Socrata URL: "' . $soda_url . '"' . "\r\nCorresponding SQL query: ");
    }
    return (string) $this->query;
  }
}
