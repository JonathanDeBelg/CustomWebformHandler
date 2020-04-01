<?php

namespace Drupal\webform_client_creator;

use Drupal\Core\Entity\EntityInterface;

class UserMailer {
  /**
   * @param EntityInterface $createdUser
   * @param String $password
   * @param $submissions
   * @return
   */
  public function send_mail(String $password, $submissions)
  {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $module = 'webform_client_creator';
    $key = 'create_user';
    $to = $submissions["e_mailadres"];

    $html = [
      '#theme' => 'confirmation_mail',
      '#password' => $password,
      '#newUserName' => $submissions['voornaam']
    ];

    $params['message'] = render($html);
    $params['subject'] = 'We hebben uw inschrijving ontvangen!';

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, TRUE);
    $this->set_drupal_notification($result);

    return $result;
  }

  /**
   * @param $result
   */
  private function set_drupal_notification($result)
  {
    if ($result['result'] !== true) {
      \Drupal::messenger()->addMessage(t('There was a problem sending your registration and it was not sent. Call me! See the contact page for my number.'), 'error');
    } else {
      \Drupal::messenger()->addMessage(t('Uw inschrijving is verzonden! Controleer uw mailbox (vergeet niet de spam te controleren). Geen mail ontvangen? Bel ons even!'));
    }
  }
}
