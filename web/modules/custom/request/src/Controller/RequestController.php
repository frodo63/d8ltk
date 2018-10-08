<?php
namespace Drupal\request\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;

/**
* Request controller.
*/

class RequestController extends ControllerBase {


public function showdata() {

$listall = \Drupal::database()->select('request', 'n');
$listall->fields('n', array('requestid', 'name', 'contact', 'reqtext'));
$result=$listall->execute()->fetchAll();

// Create the row element.
    $rows = array();
    foreach ($result as $row => $content) {
        $rows[] = array('data' => array($content->requestid));
        $rows[] = array('data' => array($content->name));
        $rows[] = array('data' => array($content->contact));
        $rows[] = array('data' => array($content->reqtext));
        //Actions.
        $edit_link = Link::createFromRoute($this->t('Edit'), '<front>',['id' => $content->requestid], ['absolute'=>TRUE]);

$delete_link = Link::createFromRoute($this->t('Delete'),'<front>',['id'=>$content->requestid], ['absolute' => TRUE]);


$build_link_action = [
    'action_edit' => [
        '#type' => 'html_tag',
        '#value' => $edit_link->toString(),
        '#tag' => 'div',
        '#attributes'=>['class'=>['action-edit']]
    ],
    'action_delete' => [
        '#type' => 'html_tag',
        '#value' => $delete_link->toString(),
        '#tag' => 'div',
        '#attributes'=>['class'=>['action-edit']]
    ]
];


 $rows[] = ['data' => \Drupal::service('renderer')->render($build_link_action)];
}

// Create the header.
    $headers = array('requestid', 'name', 'contact', 'reqtext','options');


    $output['results_table'] = [
        '#theme' => 'table',
        '#rows' => $rows,
        '#header' => $headers,
        '#empty' => $this->t('There are no items to display.')
    ];
    return $output;
}

//public function deleterequest(){
  //  $query = \Drupal::database()->delete('request');
    //$query->condition('requestid', '%did');
    //$query->execute();
//}



}
