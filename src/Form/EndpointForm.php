<?php
/**
 * @file
 * Contains \Drupal\socrata\Form\EndpointForm.
 */

namespace Drupal\socrata\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\socrata\Entity\Endpoint;

class EndpointForm extends EntityForm {

  /**
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

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $endpoint->id(),
      '#machine_name' => array(
        'exists' => array($this, 'exist'),
      ),
      '#disabled' => !$endpoint->isNew(),
    );
    $form['label'] = array(
      '#id' => 'id',
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $endpoint->label(),
      '#description' => $this->t("Name of the endpoint."),
      '#required' => TRUE,
    );
    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#maxlength' => 255,
      '#default_value' => $endpoint->getUrl(),
      '#description' => $this->t("URL of the endpoint."),
      '#required' => TRUE,
    );
    $form['app_token'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Application token'),
      '#maxlength' => 255,
      '#default_value' => $endpoint->getAppToken(),
      '#description' => $this->t("Application token."),
      '#required' => FALSE,
    );

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
    // @todo: Is there a better way to check this?
    if (strpos($url, 'resource/') === false) {
      $form_state->setErrorByName('url', t('The endpoint "@url" does not point to a valid SODA2 resource. The URL should be formatted like: http://data.example.com/resource/1234-abcd.json', ['@url' => $url]));
      return;
    }

    // Ensure we get a valid response from the endpoint.
    $endpoint = new Endpoint(array('url' => $url), 'endpoint');
    $query = db_select($url)->extend('Drupal\socrata\SocrataSelectQuery');
    $query->setEndpoint($endpoint);
    $query->params['$limit'] = 1;
    $resp = $query->execute();

    if (!$resp) {
      $form_state->setErrorByName('url', t('A request to the endpoint "@url" did not return a valid response. Try pasting the URL into your browser to be sure you see results.', ['@url' => $url]));
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
      drupal_set_message($this->t('Saved the %label endpoint.', array(
        '%label' => $endpoint->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label endpoint was not saved.', array(
        '%label' => $endpoint->label(),
      )));
    }

    $form_state->setRedirect('entity.endpoint.collection');
  }

  public function exist($id) {
    $entity = $this->entityQuery->get('endpoint')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }
}
