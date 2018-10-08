<?php
/**
 * @file
 * Contains \Drupal\custhtml\Plugin\Block\RequestBlock.
 */
namespace Drupal\request\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
/**
 * Provides a 'request module block' block.
 *
 * @Block(
 *   id = "request_module_block",
 *   admin_label = @Translation("Request block"),
 *   category = @Translation("Custom request block from Request module")
 * )
 */
class RequestBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\request\Form\RequestForm');
    return $form;
  }
}