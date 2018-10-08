<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 12.05.18
 * Time: 9:10
 */

namespace Drupal\request\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class RequestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'request_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('имя'),
    ];
    $form['contact'] = [
      '#type' => 'textfield',
      '#title' => $this->t('контакт'),
    ];
    $form['reqtext'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Текст заявки'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Отправить заявку'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*if (strlen($form_state->getValue('contact')) < 5) {
      $form_state->setErrorByName('contact', $this->t('The phone number is too short. Please enter a full phone number.'));
    }*/
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    $contact = $form_state->getValue('contact');
    $reqtext = $form_state->getValue('reqtext');

    $connection = \Drupal::database();
    $result = $connection->insert('request')
      ->fields([
        'name' =>  $name,
        'contact' => $contact,
        'reqtext' =>  $reqtext ,
      ])
      ->execute();

  }

}