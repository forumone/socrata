<?php

namespace Drupal\socrata\Form;

use Drupal\Core\Entity\EntityForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\socrata\Entity\Endpoint;

/**
 * Endpoint entity form.
 */
class EndpointForm extends EntityForm {

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $endpoint = $this->entity;

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $endpoint->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$endpoint->isNew(),
    ];
    $form['label'] = [
      '#id' => 'id',
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $endpoint->label(),
      '#description' => $this->t("The human-readable name of this endpoint."),
      '#required' => TRUE,
    ];
    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#maxlength' => 255,
      '#default_value' => $endpoint->getUrl(),
      '#description' => $this->t("URL of the dataset endpoint (e.g., https://data.seattle.gov/resource/tqh5-8vm2.json)."),
      '#required' => TRUE,
    ];
    $form['app_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application token'),
      '#maxlength' => 255,
      '#default_value' => $endpoint->getAppToken(),
      '#description' => $this->t("Application token (some endpoints require this for access)."),
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $url = $form_state->getValue('url');
    $app_token = $form_state->getValue('app_token');

    // Ensure we have a SODA2 URL for the endpoint.
    if (strpos($url, 'resource/') === FALSE) {
      $form_state->setErrorByName('url', $this->t('The endpoint "@url" does not point to a valid SODA2 resource. The URL should be formatted like: http://data.example.com/resource/1234-abcd.json', ['@url' => $url]));
      // Bail out or otherwise the query below will bork.
      return;
    }

    // Ensure we get a valid response from the endpoint.
    $endpoint = new Endpoint(
      [
        'url' => $url,
        'app_token' => $app_token,
      ],
      'endpoint'
    );
    $query = \Drupal::database()->select($url)->extend('Drupal\socrata\SocrataSelectQuery');
    $query->setEndpoint($endpoint);
    $query->params['$limit'] = 1;
    $resp = $query->execute();

    if (!$resp) {
      $form_state->setErrorByName('url', $this->t('A request to the endpoint "@url" did not return a valid response. Try pasting the URL into your browser to be sure you see results.', ['@url' => $url]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $endpoint = $this->entity;

    // Prevent leading and trailing spaces.
    $endpoint->set('label', trim($endpoint->label()));
    $endpoint->set('url', $form_state->getValue('url'));
    $endpoint->set('app_token', $form_state->getValue('app_token'));
    $status = $endpoint->save();

    if ($status) {
      drupal_set_message(
        $this->t('Saved the %label endpoint.',
          [
            '%label' => $endpoint->label(),
          ]
        )
      );
    }
    else {
      drupal_set_message(
        $this->t('The %label endpoint was not saved.',
          [
            '%label' => $endpoint->label(),
          ]
        )
      );
    }

    $form_state->setRedirect('entity.endpoint.collection');
  }

  /**
   * Check if the endpoint exists.
   *
   * @param string $id
   *   The endpoint identifier.
   *
   * @return bool
   *   Whether the endpoint exists.
   */
  public function exist($id) {
    $entity = $this->entityQuery->get('endpoint')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
