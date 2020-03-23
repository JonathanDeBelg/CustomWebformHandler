<?php

namespace Drupal\webform_client_creator;

use Drupal\contact\MailHandler;

class UserMailer {
  /**
   * @param array $submission_array
   * @param $userName
   * @return
   */
  public function send_mail(array $submission_array, $userName)
  {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $module = 'webform_client_creator';
    $key = 'create_user';
    $to = $submission_array['e_mailadres'];

    $html = [
      '#theme' => 'confirmation_mail',
      '#loginlink' => "bosjevandrosje.nl/user/",
      '#newUserName' => $userName
    ];

    $params['message'] = render($html);;
    $params['subject'] = 'We hebben uw verzoek ontvangen!';

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
