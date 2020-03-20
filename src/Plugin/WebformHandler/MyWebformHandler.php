<?php

namespace Drupal\webform_client_creator\Plugin\WebformHandler;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Create a new Article node from a webform submission.
 *
 * @WebformHandler(
 *   id = "Create a client node",
 *   label = @Translation("Create a client node on submit"),
 *   category = @Translation("Entity Creation"),
 *   description = @Translation("Creates a new Client node from Webform Submissions."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class MyWebformHandler extends WebformHandlerBase
{

  /**
   * {@inheritdoc}
   */

  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE)
  {
    $submission_array = $webform_submission->getData();
    $createdUser = $this->handleUser($submission_array);

    $result = $this->send_mail($submission_array, $createdUser->id());
    $this->set_drupal_notification($result);
  }

  /**
   * @param array $submission_array
   * @param $node_id
   * @return
   */
  private function send_mail(array $submission_array, $node_id)
  {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = 'nl';
    $module = 'webform_client_creator';
    $key = 'general_mail';
    $to = $submission_array['e_mailadres'];

    $params = [
      'mail_title' => 'Test',
      'body' => $this->generate_mail_message($node_id, $submission_array),
      'subject' => "U bent klant!",
    ];

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, TRUE);
    return $result;
  }

  /**
   * @param $node_id
   * @return string
   */
  private function generate_mail_message($node_id, array $submission_array): string
  {
    $message = "";
    $message .= "<p>Beste " . $submission_array['voornaam'] . ",</p>";
    $message .= '<p>Wij hebben uw mail in goede orde ontvagen! Om maar direct even met de deur in huis te vallen krijgt u ' .
      'van ons een klantnummer. Met deze klantnummer kunt u korting krijgen en uw account ' .
      'activeren/deactiveren. Gaat u op vakantie, dan is het natuurlijk zonde om de bloemen te ' .
      'laten verwelken. Wilt u dan tijdelijk uw account deactiveren? Dan gaat u naar ' .
      '<a href="bosjevandrosje.nl/klanten/' . $node_id . '">' . 'bosjevandrosje.nl/klant/</a>. ' .
      'U ziet ons bloemetjes spoedig tegemoet!</p>';
    $message .= "<p>Groetjes Beau Drost,</p>";
    $message .= "<br><p>Bosje van Drosje</p>";
    return $message;
  }

  /**
   * @param $result
   */
  private function set_drupal_notification($result)
  {
    if ($result['result'] !== true) {
      \Drupal::messenger()->addMessage(t('There was a problem sending your registration and it was not sent. Call me! See the contact page for my number.'), 'error');
    } else {
      \Drupal::messenger()->addMessage(t('Your registrations has been sent.'));
    }
  }

  /**
   * @param array $submission_array
   * @return EntityInterface
   * @throws EntityStorageException
   */
  private function handleUser(array $submission_array): EntityInterface
  {
    $user = User::create([
      'name' => $submission_array['voornaam'] . " " . $submission_array['achternaam'],
      'field_telefoon' => $submission_array['mobiele_telefoonnummer'],
      'field_adres' => $submission_array['adres'],
      'field_postcode' => $submission_array['postcode'],
      'field_woonplaats' => $submission_array['woonplaats'],
      'field_betaalopties' => $submission_array['betaalopties'],
      'field_hoevaak_bloemen' => $submission_array['hoevaak_wilt_u_bloemen_ontvangen_per_maand_'],
      'field_pakketkeuze' => $submission_array['welk_pakket_wilt_u_ontvangen_'],
      'field_abbo_actief' => TRUE,
    ]);
    $user->addRole('client');
    $user->activate();
    $user->setEmail($submission_array['e_mailadres']);
    $user->save();

    return $user;
  }
}
