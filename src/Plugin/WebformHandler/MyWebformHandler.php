<?php
namespace Drupal\webform_client_creator\Plugin\WebformHandler;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\user\Entity\User;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform_client_creator\UserMailer;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
   * @throws EntityStorageException
   */

  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE)
  {
    $submission_array = $webform_submission->getData();
    $ids = \Drupal::entityQuery('user')
      ->condition('name', $submission_array['e_mailadres'])
      ->range(0, 1)
      ->execute();

    if(!empty($ids)){
      $redirect = new RedirectResponse("https://bosjevandrosje.nl/contact");
      $redirect->send();
      \Drupal::messenger()->addMessage(t('Dit e-mailadres staat al geregistreerd. We zoeken het samen uit! Bel ons op 06-42381468, of mail ons naar info@bosjevandrosje.nl'), 'error', TRUE);
      exit;
    } else {
      $tijdelijkwachtwoord = $submission_array['e_mailadres'] . "-tijdelijkWachtwoord123";
      $this->handleUser($submission_array);
      (new UserMailer)->send_mail($tijdelijkwachtwoord, $submission_array);
    }
  }

  /**
   * @param array $submission_array
   * @throws EntityStorageException
   */
  private function handleUser(array $submission_array)
  {
    $user = User::create([
      'name' => $submission_array['e_mailadres'],
      'field_voornaam_' => $submission_array['voornaam'],
      'field_achternaam' => $submission_array['achternaam'],
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
    $user->setPassword($submission_array['e_mailadres'] . "-tijdelijkWachtwoord123?]");
    $user->save();
  }
}
