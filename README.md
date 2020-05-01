# Zoom API Example Module

This modules demonstrates three examples of using the Zoom API Drupal Module.
https://www.drupal.org/project/zoomapi

1. Posting to the Webinar Create endpoint when a node is saved.
  See zoomapi_example.module
2. Posting to the Webinar Registrant endpoint implementing a Webform Handler.
  See src/Plugin/WebformHandler/ZoomRegistrationWebformHandler.php
3. Receiving a webhook from Zoom. See src/ZoomApiWebhookEventSubscriber.php

These examples assume you have followed the configuration tasks found in the
Zoom API module README.
