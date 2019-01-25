<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 01.10.18
 * Time: 23:05
 */

namespace Drupal\request\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class DeleteForm extends FormBase
{
    /**
     * {@inheritdoc}
     */

    public function getFormId() {
        return 'request_delete_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    }

}