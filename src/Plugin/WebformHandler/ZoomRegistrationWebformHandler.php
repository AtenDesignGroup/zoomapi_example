<?php

namespace Drupal\zoomapi_example\Plugin\WebformHandler;

use GuzzleHttp\Exception\RequestException;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Send a webform registration to Zoom upon submission.
 *
 * @WebformHandler(
 *   id = "send_registration_to_zoom",
 *   label = @Translation("Send Registration to Zoom"),
 *   category = @Translation("Web Services"),
 *   description = @Translation("Sends a webinar registration to Zoom upon submission."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class ZoomRegistrationWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Get an array of the values from the submission.
    $data = $webform_submission->getData();

    // In this example zoom_webinar_id is part of a querystring param.
    // Webform is configured to pass it through as a hidden field.
    if (empty($data['zoom_webinar_id'])) {
      \Drupal::messenger()->addWarning(t('There was a problem sending your information to the webinar provider. Please contact us to let us know.'));
      return;
    }

    $webinar_id = $data['zoom_webinar_id'];
    // Build registrant info for Zoom.
    // This will get converted to JSON by the client.
    $registrant = [
      'email' => $data['email_address'],
      'first_name' => $data['name']['first'],
      'last_name' => $data['name']['last'],
      'org' => $data['organization_name'],
    ];

    // Send registrant to Zoom.
    $client = \Drupal::service('zoomapi.client');
    try {
      // Make the POST request to the zoom api.
      $client->request(
        'post', '/webinars/' . $webinar_id . '/registrants',
        [],
        $registrant
      );
      // Show success!.
      \Drupal::messenger()->addStatus(t('Thank you for registering. Please check your confirmation email for the webinar link and other information'));
    }
    catch (RequestException $exception) {
      // Zoom api already logs errors, but you could log more.
      \Drupal::messenger()->addWarning(t('There was a problem sending your information to the webinar provider. Please contact us to let us know.'));
    }
  }

}
