<?php

namespace Drupal\webform_client_creator;

abstract class UserMailer {
  /**
   * @param array $submission_array
   * @param $userName
   * @return
   */
  public static function send_mail(array $submission_array, $userName)
  {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = 'nl';
    $module = 'webform_client_creator';
    $key = 'create_user';
    $to = $submission_array['e_mailadres'];

    $loginLink = "bosjevandrosje.nl/user/";

    $html = self::introduction($submission_array);
    $html['blockContent'] = array(
      '#theme' => 'confirmationemail',
      '#loginlink' => $loginLink,
      '#newUserName' => $userName,
      '#editmode' => false,
      '#cache' => array('max-age' => 0)
    );

    $params['message'] = render($html);
    $params['subject'] = 'U bent klant!';

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, TRUE);
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
      \Drupal::messenger()->addMessage(t('Uw inschrijving is verzonden! Controleer uw mailbox (vergeet niet de spam te controleren. Geen mail ontvangen? Bel ons even!'));
    }
  }

  private static function introduction($submission_array) {
    return [0 => [
      '#type' => 'markup',
      '#markup' => '<p>Goedendag '. $submission_array['voornaam'] .', <br /></p>'
    ]];
  }
}
