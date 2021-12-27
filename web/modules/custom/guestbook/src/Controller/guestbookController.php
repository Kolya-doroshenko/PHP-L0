<?php

namespace Drupal\guestbook\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for the guestbook module.
 */
class guestbookController extends ControllerBase {

  /**
   * Form build interface.
   *
   * @var Drupal\Core\Form\FormBase
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->formBuilder = $container->get('form_builder');
    return $instance;
  }

  /**
   * Return form for vidguks.
   */
  public function form() {
    $form = $this->formBuilder->getForm('\Drupal\guestbook\Form\GbForm');
    return $form;
  }

  /**
   * Get all vidguks for page.
   *
   * @return array
   *   A simple array.
   */
  public function load() {
    $connection = \Drupal::service('database');
    $query = $connection->select('guestbook', 'a');
    $query->fields('a', ['name', 'mail', 'nomer', 'vidguk', 'AVA', 'created', 'image', 'id']);
    $result = $query->execute()->fetchAll();
    return $result;
  }

  /**
   * Render all vidguks entries.
   */
  public function report() {
    $info = json_decode(json_encode($this->load()), TRUE);
    $info = array_reverse($info);
    $form = $this->form();
    $rows = [];
    foreach ($info as &$value) {
      $fid = $value['image'];
      $file = File::load($fid);
      $value['image'] = !empty($file) ? file_url_transform_relative(file_create_url($file->getFileUri())) : '';

      $avafid = $value['AVA'];
      $avafile = File::load($avafid);
      $value['AVA'] = !empty($avafile) ? file_url_transform_relative(file_create_url($avafile->getFileUri())) : '';

      array_push($rows, $value);
    }
    return [
      '#theme' => 'gb_template',
      '#items' => $rows,
      '#form' => $form,
    ];
  }

}
