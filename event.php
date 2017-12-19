<?php

// Nexmo send a JSON payload to your event endpoint, so read and decode it
$request = json_decode(file_get_contents('php://input'), true);

// Work with the call status
if (isset($request['status'])) {
    switch ($request['status']) {
    case 'ringing':
        record_steps("UUID: {$request['conversation_uuid']} - ringing.");
        break;
    case 'answered':
        record_steps("UUID: {$request['conversation_uuid']} - was answered.");
        break;
    case 'machine':
        record_steps("UUID: {$request['conversation_uuid']} - answering machine.");
        break;
    case 'complete':
        // If you set eventUrl in your NCCO. The recording download URL
        // is returned in recording_url. It has the following format
        // https://api.nexmo.com/media/download?id=52343cf0-342c-45b3-a23b-ca6ccfe234b0
        //
        // Make a GET request to this URL using JWT authentication to download
        // the recording. For more information, see
        // https://developer.nexmo.com/voice/voice-api/guides/record-calls-and-conversations
        record_steps("UUID: {$request['conversation_uuid']} - complete.");
        break;
    default:
        break;
    }
}

if (isset($request['dtmf'])) {
  switch ($request['dtmf']) {
      case '1':
          record_steps("UUID: {$request['conversation_uuid']} - confirmed receipt.");
          break;
      default:
          record_steps("UUID: {$request['conversation_uuid']} - other button pressed ({$request['dtmf']}).");
          break;
  }
}

function record_steps($message) {
    file_put_contents('./call_log.txt', $message.PHP_EOL, FILE_APPEND | LOCK_EX);
}
