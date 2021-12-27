<?php

namespace Drupal\guestbook\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Contains \Drupal\guestbook\Form\gbadminForm.
 *
 * @file
 */

/**
 * Provides an Cat form.
 */
class GbadminForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gb_admin_form';
  }

  /**
   * Get all cats for page.
   *
   * @return array
   *   A simple array.
   */
  public function load() {
    $connection = \Drupal::service('database');
    $query = $connection->select('guestbook', 'a');
    $query->fields('a', ['name', 'mail', 'nomer', 'AVA', 'vidguk', 'created', 'image', 'id']);
    $result = $query->execute()->fetchAll();
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $info = json_decode(json_encode($this->load()), TRUE);
    $info = array_reverse($info);
    $headers = [
      t('Name'),
      t('Ava'),
      t('Mail'),
      t('Nomer'),
      t('Image'),
      t('Vidguk'),
      t('Time'),
      t('Delete'),
      t('Edit'),
    ];
    $rows = [];
    foreach ($info as &$value) {
      $fid = $value['image'];
      $id = $value['id'];
      $name = $value['name'];
      $mail = $value['mail'];
      $nomer = $value['nomer'];

      $created = $value['created'];

      array_splice($value, 0, 5);
      $renderer = \Drupal::service('renderer');
      $file = File::load($fid);
      $img = [
        '#type' => 'image',
        '#theme' => 'image_style',
        '#style_name' => 'thumbnail',
        '#uri' => !empty($file) ? $file->getFileUri() : '',
      ];
      if (empty($avafile)) {
        $ava = [
          '#markup' => '<img src="/modules/custom/guestbook/img/default-user-avatar-300x293.png"/>',
        ];
      }
      else {
        $ava = [
          '#type' => 'image',
          '#theme' => 'image_style',
          '#style_name' => 'thumbnail',
          '#uri' => $avafile->getFileUri() ,
        ];
      }


      $delete = [
        '#type' => 'link',
        '#url' => Url::fromUserInput("/guestbook/gbDel/$id"),
        '#title' => $this->t('Delete'),
        '#attributes' => [
          'data-dialog-type' => ['modal'],
          'class' => ['button', 'use-ajax'],
        ],
      ];
      $edit = [
        '#type' => 'link',
        '#url' => Url::fromUserInput("/admin/guestbook/gbChange/$id"),
        '#title' => $this->t('Edit'),
        '#attributes' => ['class' => ['button']],
      ];
      $newId = [
        '#type' => 'hidden',
        '#value' => $id,
      ];

      $value[0] = $name;
      $value[1] = $renderer->render($ava);

      $value[2] = $mail;
      $value[3] = $nomer;
      $value[4] = $renderer->render($img);

      $value[6] = $created;
      $value[7] = $renderer->render($delete);
      $value[8] = $renderer->render($edit);
      $value[9] = $newId;
      array_push($rows, $value);
    }
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $headers,
      '#options' => $rows,
      '#empty' => t('No entries available.'),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete selected'),
      '#description' => $this->t('Submit, #type = submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form['table']['#value'];
    $connection = \Drupal::service('database');
    foreach ($value as $key => $val) {
      $result = $connection->delete('guestbook');
      $result->condition('id', $form['table']['#options'][$key][9]["#value"]);
      $result->execute();
    }
  }

}
