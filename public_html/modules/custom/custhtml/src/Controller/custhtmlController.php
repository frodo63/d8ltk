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

  public function givefourothree() {
      return [
          '#theme' => 'custom_fourothree',
      ];
  }

  public function givefourofour() {
      return [
          '#theme' => 'custom_fourofour',
      ];
  }
  
    public function givesog() {
    return [
      '#theme' => 'custom_sog',
    ];
  }

}
