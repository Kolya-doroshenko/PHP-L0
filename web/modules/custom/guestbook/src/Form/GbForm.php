<?php

namespace Drupal\guestbook\Form;

use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Contains \Drupal\guestbook\Form\gbForm.
 *
 * @file
 */

/**
 * Provides an vidguk form.
 */
class GbForm extends FormBase {

  /**
   * The current time.
   *
   * @var \Drupal\Core\Datatime
   */
  protected $currentTime;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->currentTime = $container->get('datetime.time');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gb_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>',
    ];
    $form['name'] = [
      '#title' => t("Your name:"),
      '#type' => 'textfield',
      '#maxlength' => 100,
      '#description' => t("Name should be at least 2 characters and less than 100 characters"),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::setMessageName',
        'event' => 'keyup',
      ],
    ];
    $form['message2'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_email"></div>',
    ];
    $form['email'] = [
      '#title' => t("Email:"),
      '#type' => 'email',
      '#description' => t("example@gmail.com"),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::setMessageMail',
        'event' => 'keyup',
      ],
    ];
    $form['message3'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_nomer"></div>',
    ];
    $form['nomer'] = [
      '#title' => t("Mobile:"),
      '#maxlength' => 9,
      '#minlength' => 5,
      '#type' => 'textfield',
      '#description' => t("+380999999999"),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::setMessageNomer',
        'event' => 'keyup',
      ],
    ];
    $form['message4'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_vidguk"></div>',
    ];
    $form['vidguk'] = [
      '#title' => t("Your vidguk:"),
      '#type' => 'textfield',
      '#description' => t("Your vidguk:"),
      '#required' => TRUE,
    ];
    $form['AVA'] = [
      '#title' => t("Your AVA:"),
      '#type' => 'managed_file',
      '#upload_location' => 'public://module-images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg png jpeg'],
        'file_validate_size' => [2097152],
      ],
      '#description' => t("Your AVA, insert image below size of 2MB. Supported formats: png jpg jpeg."),
      '#required' => TRUE,
    ];
    $form['image'] = [
      '#title' => t("Your image to vidguk:"),
      '#type' => 'managed_file',
      '#upload_location' => 'public://module-images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5242880],
      ],
      '#description' => t("Your image to vidguk, insert image below size of 5MB. Supported formats: png jpg jpeg."),
      '#required' => TRUE,
    ];
    $form['message5'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_forma"></div>',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add vidguk'),
      '#ajax' => [
        'callback' => '::setMessage',
        'event' => 'click',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $connection = \Drupal::service('database');
    if (!$form_state->getValue('AVA')[0] == NULL) {
      $ava = File::load($form_state->getValue('AVA')[0]);
      $ava->setPermanent();
      $ava->save();
    }
    else {
      $form_state->getValue('AVA')[0] = 0;
    }
    $times = time() + 3 * 60 * 60;
    $result = $connection->insert('guestbook')
      ->fields([
        'name' => $form_state->getValue('name'),
        'vidguk' => $form_state->getValue('vidguk'),
        'nomer' => $form_state->getValue('nomer'),
        'AVA' => $form_state->getValue('AVA')[0],
        'mail' => $form_state->getValue('email'),
        'created' => date('d/m/Y G:i:s', $times),
        'image' => $form_state->getValue('image')[0],
      ])
      ->execute();
  }

  /**
   * Function that validate name input with ajax.
   */
  public function setMessageName(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $name = $form_state->getValue('name');
    $is_name = preg_match('/\w{2,32}/', $name);

    if ($is_name <= 0) {
      $response->addCommand(
        new HtmlCommand(
          '.result_message',
          '<div class="my_top_message">' . $this->t('Not correct name. Name < 2 sumbols and only a-Z.')
        )
      );
    }
    else {
      $response->addCommand(
        new HtmlCommand(
          '.result_message',
          '<div class="my_top_message">' . $this->t('Your name: %name.', ['%name' => $name])
        )
      );
    }
    return $response;
  }

  /**
   * Function that validate email input with ajax.
   */
  public function setMessageMail(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $email = $form_state->getValue('email');
    $is_email = preg_match("/^([a-zA-Z0-9]+(?:[-_]?[a-zA-Z0-9]+)?@[a-zA-Z_-]+(?:\.?[a-zA-Z]+)?\.[a-zA-Z]{2,5})/i", $email);
    if ($is_email > 0) {
      $response->addCommand(
        new HtmlCommand(
          '.result_email',
          '<div class="my_top_message">' . $this->t('You email: %title.', ['%title' => $email])
        )
      );
    }
    if ($is_email <= 0) {
      $response->addCommand(
        new HtmlCommand(
          '.result_email',
          '<div class="my_top_message">' . $this->t('Not correct email')
        )
      );
    }
    return $response;
  }

  /**
   * Function that validate nomer input with ajax.
   */
  public function setMessageNomer(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $nomer = $form_state->getValue('nomer');
    $is_nomer = preg_match('/[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]/', $nomer);
    if ($is_nomer > 0) {
      $response->addCommand(
        new HtmlCommand(
          '.result_nomer',
          '<div class="my_top_message">' . $this->t('Your nomer: +380 %title.', ['%title' => $nomer])
        )
      );
    }
    else {
      $response->addCommand(
        new HtmlCommand(
          '.result_nomer',
          '<div class="my_top_message">' . $this->t('Not valid nomer. Valid nomer: XXXXXXXXX. X=(0-9)')
        )
      );
    }
    return $response;
  }

  /**
   * Function that validate input with ajax.
   */
  public function setMessage(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $name = $form_state->getValue('name');
    $is_name = preg_match('/\w{2,32}/', $name);

    $nomer = $form_state->getValue('nomer');
    $is_nomer = preg_match('/[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]/', $nomer);

    $email = $form_state->getValue('email');
    $is_email = preg_match("/^([a-zA-Z0-9]+(?:[-_]?[a-zA-Z0-9]+)?@[a-zA-Z_-]+(?:\.?[a-zA-Z]+)?\.[a-zA-Z]{2,5})/i", $email);

    if ($form_state->getValue('vidguk') != NULL && $form_state->getValue('image')[0] > 0 && $is_nomer > 0 && $is_email > 0 && $is_name > 0) {
      $response->addCommand(new RedirectCommand('/guestbook/gb'));
    }
    else {
      $response->addCommand(
        new HtmlCommand(
          '.result_forma',
          '<div class="my_top_message">' . $this->t('Not valid Form.')
        )
      );
    }
    return $response;
  }

}
