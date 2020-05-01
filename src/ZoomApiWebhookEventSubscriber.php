<?php

namespace Drupal\zoomapi_example;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\zoomapi\Event\ZoomApiEvents;
use Drupal\zoomapi\Event\ZoomApiWebhookEvent;

/**
 * Class ZoomApiWebhookEventSubscriber.
 *
 * @package Drupal\zoomapi_example\ZoomApiWebhookEventSubscriber
 */
class ZoomApiWebhookEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ZoomApiEvents::WEBHOOK_POST][] = ['updateNode'];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function updateNode(ZoomApiWebhookEvent $event) {
    // Only look for the 'recording.completed' event from Zoom.
    // See https://marketplace.zoom.us/docs/api-reference/webhook-reference.
    if ($event->getEvent() !== 'recording.completed') {
      return;
    }

    // This gets the payload from zoom.
    $payload = $event->getPayload();
    if (empty($payload['object']['id'])) {
      return;
    }

    $recordingData = $payload['object'];
    $webinarId = $recordingData['id'];
    $storage = \Drupal::getContainer()->get('entity_type.manager')->getStorage('node');

    // Load the webinar by the saved webinar Zoom ID.
    $webinars = $storage->loadByProperties([
      'field_zoom_webinar_id' => $webinarId,
    ]);
    if (empty($webinars)) {
      return;
    }

    // Just get the node object.
    $webinar = reset($webinars);

    // Make sure the field exists.
    if (!$webinar->hasField('field_webinar_mp4')) {
      return;
    }

    // Make sure the mp4 field is empty.
    if (!$webinar->field_webinar_mp4->isEmpty()) {
      return;
    }

    // Get the MP4 from the webhook payload.
    foreach ($recordingData['recording_files'] as $file) {
      if ($file['file_type'] === 'MP4') {
        $downloadUrl = $file['download_url'];
        break;
      }
    }

    // Attach the file to the webinar node.
    $fileName = 'private://webinar-mp4s/zoom-' . $webinarId . '.mp4';
    $fileData = file_get_contents($downloadUrl);
    $fileEntity = file_save_data($fileData, $fileName, FILE_EXISTS_REPLACE);

    // Save the .mp4 to the webinar node.
    $webinar->field_webinar_mp4->target_id = $fileEntity->id();
    $webinar->save();
  }

}
