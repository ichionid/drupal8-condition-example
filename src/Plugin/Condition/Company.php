<?php

namespace Drupal\test_drupal\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Company' condition.
 *
 * @Condition(
 *   id = "company",
 *   label = @Translation("Company"),
 * )
 *
 */
class Company extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  private $request;
  private $fruit_options;
  /**
   * {@inheritdoc}
   */
  public function __construct(\Symfony\Component\HttpFoundation\RequestStack $request, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->request = $request->getCurrentRequest();
    $this->fruit_options = ["apple" => "apple", "orange" => "orange", "strawberry" => "strawberry"];
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $container->get('request_stack'), $configuration, $plugin_id, $plugin_definition
    );
  }
  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['fruits'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Fruit selection'),
      '#default_value' => $this->configuration['fruits'],
      '#options' => $this->fruit_options,
      '#description' => $this->t('Select fruit to enforce. If none are selected, all companies will be allowed.'),
    );
    return $form;
    //return parent::buildConfigurationForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['fruits'] = array_filter($form_state->getValue('fruits'));
    parent::submitConfigurationForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function summary() {
    return t('Show this an a fruit specific page.');
  }
  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['fruits']) && !$this->isNegated()) {
      return TRUE;
    }
    $fruit = $this->request->get("fruit");
    if (!empty($fruit) && array_search($fruit, $this->configuration['fruits']) != FALSE) {
      // Fruit passed with the request is chosen in the configuration.
      return TRUE;
    }
    return FALSE;
  }

}
