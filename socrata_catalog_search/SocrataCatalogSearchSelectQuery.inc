<?php
/**
 * @file
 * Decorator class for a Socrata dataset query
 */

class SocrataCatalogSearchSelectQuery extends SocrataSelectQuery {

  /**
   * Socrata Catalog Search filter lists.
   */
  public $suffix = '';

  /**
   * Get the Socrata Catalog Search source object.
   */
  public function getSource() {
    $source = new stdClass();
    $source->name = 'catalog_search';
    $source->description = 'Catalog Search API';
    $source->endpoint = 'http://api.us.socrata.com/api/catalog/v1';
    $source->app_token = '';

    return $source;
  }

  /**
   * Return full Socrata URL with endpoint and parameters.
   *
   * @param bool $encode
   *   Determins whether we should URL-encode the returned URL.
   *
   * @return string
   *   Formatted URL
   */
  protected function getCurlUrl($encode = TRUE) {
    $url = '';
    $source = isset($this->source) ? $this->source : $this->getSource();
    $endpoint = $source->endpoint;

    // Special handling for lists from the API such as categories, tags, etc.
    $suffix = $this->suffix;
    if ($suffix) {
      return $endpoint . '/' . $suffix;

    }

    $params = $this->params;

    // We might not want to encode the URL in cases where we just want it to be
    // output for humans to read, most notably in the query displayed in the
    // views preview.
    // In this case, assemble our URL with the query parameters directly,
    // rather than passing them in as query arguments to the URL function, where
    // they'll be URL-encoded.
    if (!$encode) {
      $endpoint_with_params = $endpoint;
      if (!empty($params)) {
        $endpoint_with_params .= '?';
        $params_query = array();
        foreach ($params as $key => $value) {
          $params_query[] = $key . '=' . $value;
        }
      }
      $endpoint_with_params .= implode('&', $params_query);
      $url = url($endpoint_with_params);
    }
    else {
      $url = url($endpoint, array('query' => $params, 'absolute' => TRUE));
    }
    return $url;
  }

}
