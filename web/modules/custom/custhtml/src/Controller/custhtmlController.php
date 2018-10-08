<?php
/**
 * @file
 * Contains \Drupal\custhtml\Controller\custhtmlController.
 */

namespace Drupal\custhtml\Controller;

use Drupal\Core\Controller\ControllerBase;


class custhtmlController extends ControllerBase {
  public function givefrontcontent() {
    return [
      '#theme' => 'custom_front',
      '#test_var' => 'THIS IS MY CUSTOM VARIABLE!!!',
    ];
  }
  public function givecontacts() {
    return [
      '#theme' => 'custom_contacts',
    ];
  }
  public function giverequisites() {
    return [
      '#theme' => 'custom_requisites',
    ];
  }
  public function givepartners() {
    return [
      '#theme' => 'custom_partners',
    ];
  }
  public function givelogistics() {
    return [
      '#theme' => 'custom_front',
    ];
  }
}
