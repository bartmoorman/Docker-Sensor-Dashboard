<?php
require_once('../inc/dashboard.class.php');
$dashboard = new Dashboard(false, false, false, false);

$output = $logFields = ['success' => null, 'message' => null];
$log = [];
$putEvent = true;

switch ($_REQUEST['func']) {
  case 'authenticateSession':
    if (!empty($_REQUEST['username']) && !empty($_REQUEST['password'])) {
      $output['success'] = $dashboard->authenticateSession($_REQUEST['username'], $_REQUEST['password']);
      $log['username'] = $_REQUEST['username'];
    } else {
      header('HTTP/1.1 400 Bad Request');
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
    }
    break;
  case 'createUser':
    if (!$dashboard->isConfigured() || ($dashboard->isValidSession() && $dashboard->isAdmin())) {
      if (!empty($_REQUEST['username']) && !empty($_REQUEST['password']) && !empty($_REQUEST['first_name']) && !empty($_REQUEST['role'])) {
        $last_name = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : null;
        $pushover_user = !empty($_REQUEST['pushover_user']) ? $_REQUEST['pushover_user'] : null;
        $pushover_token = !empty($_REQUEST['pushover_token']) ? $_REQUEST['pushover_token'] : null;
        $pushover_priority = isset($_REQUEST['pushover_priority']) ? $_REQUEST['pushover_priority'] : null;
        $pushover_retry = isset($_REQUEST['pushover_retry']) ? $_REQUEST['pushover_retry'] : null;
        $pushover_expire = isset($_REQUEST['pushover_expire']) ? $_REQUEST['pushover_expire'] : null;
        $pushover_sound = !empty($_REQUEST['pushover_sound']) ? $_REQUEST['pushover_sound'] : null;
        $begin = !empty($_REQUEST['begin']) ? $_REQUEST['begin'] : null;
        $end = !empty($_REQUEST['end']) ? $_REQUEST['end'] : null;
        $output['success'] = $dashboard->createUser($_REQUEST['username'], $_REQUEST['password'], $_REQUEST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_REQUEST['role'], $begin, $end);
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'createSensor':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['name'])) {
        $key = isset($_REQUEST['key']) ? $_REQUEST['key'] : null;
        $min_temperature = isset($_REQUEST['min_temperature']) ? $_REQUEST['min_temperature'] : null;
        $max_temperature = isset($_REQUEST['max_temperature']) ? $_REQUEST['max_temperature'] : null;
        $min_humidity = isset($_REQUEST['min_humidity']) ? $_REQUEST['min_humidity'] : null;
        $max_humidity = isset($_REQUEST['max_humidity']) ? $_REQUEST['max_humidity'] : null;
        $output['success'] = $dashboard->createSensor($_REQUEST['name'], $key, $min_temperature, $max_temperature, $min_humidity, $max_humidity);
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'No name supplied';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'createApp':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['name'])) {
        $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;
        $begin = !empty($_REQUEST['begin']) ? $_REQUEST['begin'] : null;
        $end = !empty($_REQUEST['end']) ? $_REQUEST['end'] : null;
        $output['success'] = $dashboard->createApp($_REQUEST['name'], $token, $begin, $end);
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'No name supplied';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'updateUser':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['user_id']) && !empty($_REQUEST['username']) && !empty($_REQUEST['first_name']) && !empty($_REQUEST['role'])) {
        $password = !empty($_REQUEST['password']) ? $_REQUEST['password'] : null;
        $last_name = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : null;
        $pushover_user = !empty($_REQUEST['pushover_user']) ? $_REQUEST['pushover_user'] : null;
        $pushover_token = !empty($_REQUEST['pushover_token']) ? $_REQUEST['pushover_token'] : null;
        $pushover_priority = isset($_REQUEST['pushover_priority']) ? $_REQUEST['pushover_priority'] : null;
        $pushover_retry = isset($_REQUEST['pushover_retry']) ? $_REQUEST['pushover_retry'] : null;
        $pushover_expire = isset($_REQUEST['pushover_expire']) ? $_REQUEST['pushover_expire'] : null;
        $pushover_sound = !empty($_REQUEST['pushover_sound']) ? $_REQUEST['pushover_sound'] : null;
        $begin = !empty($_REQUEST['begin']) ? $_REQUEST['begin'] : null;
        $end = !empty($_REQUEST['end']) ? $_REQUEST['end'] : null;
        $output['success'] = $dashboard->updateUser($_REQUEST['user_id'], $_REQUEST['username'], $password, $_REQUEST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_REQUEST['role'], $begin, $end);
        $log['user_id'] = $_REQUEST['user_id'];
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'updateSensor':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['sensor_id']) && !empty($_REQUEST['name']) && !empty($_REQUEST['key'])) {
        $min_temperature = isset($_REQUEST['min_temperature']) ? $_REQUEST['min_temperature'] : null;
        $max_temperature = isset($_REQUEST['max_temperature']) ? $_REQUEST['max_temperature'] : null;
        $min_humidity = isset($_REQUEST['min_humidity']) ? $_REQUEST['min_humidity'] : null;
        $max_humidity = isset($_REQUEST['max_humidity']) ? $_REQUEST['max_humidity'] : null;
        $output['success'] = $dashboard->updateSensor($_REQUEST['sensor_id'], $_REQUEST['name'], $_REQUEST['key'], $min_temperature, $max_temperature, $min_humidity, $max_humidity);
        $log['sensor_id'] = $_REQUEST['sensor_id'];
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'updateApp':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['app_id']) && !empty($_REQUEST['name']) && !empty($_REQUEST['token'])) {
        $begin = !empty($_REQUEST['begin']) ? $_REQUEST['begin'] : null;
        $end = !empty($_REQUEST['end']) ? $_REQUEST['end'] : null;
        $output['success'] = $dashboard->updateApp($_REQUEST['app_id'], $_REQUEST['name'], $_REQUEST['token'], $begin, $end);
        $log['app_id'] = $_REQUEST['app_id'];
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'modifyObject':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['action']) && !empty($_REQUEST['type']) && !empty($_REQUEST['value'])) {
        $output['success'] = $dashboard->modifyObject($_REQUEST['action'], $_REQUEST['type'], $_REQUEST['value']);
        $log['action'] = $_REQUEST['action'];
        $log['type'] = $_REQUEST['type'];
        $log['value'] = $_REQUEST['value'];
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'getObjectDetails':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['type']) && !empty($_REQUEST['value'])) {
        if ($output['data'] = $dashboard->getObjectDetails($_REQUEST['type'], $_REQUEST['value'])) {
          $output['success'] = true;
          $putEvent = false;
        } else {
          $output['success'] = false;
          $log['type'] = $_REQUEST['type'];
          $log['value'] = $_REQUEST['value'];
        }
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'putReading':
    if (!empty($_REQUEST['key']) && !empty($_REQUEST['temperature']) && !empty($_REQUEST['humidity'])) {
      $output['success'] = $dashboard->putReading($_REQUEST['key'], $_REQUEST['temperature'], $_REQUEST['humidity']);
      $putEvent = false;
    } else {
      header('HTTP/1.1 400 Bad Request');
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
    }
    break;
  case 'getReadings':
    if ($dashboard->isValidSession() || (array_key_exists('token', $_REQUEST) && $dashboard->isValidObject('token', $_REQUEST['token']))) {
      if (!empty($_REQUEST['sensor_id']) && !empty($_REQUEST['hours'])) {
        if ($output['data'] = $dashboard->getReadings($_REQUEST['sensor_id'], $_REQUEST['hours'])) {
          $output['success'] = true;
          $putEvent = false;
        } else {
          $output['success'] = false;
          $log['sensor_id'] = $_REQUEST['sensor_id'];
          $log['hours'] = $_REQUEST['hours'];
        }
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'suppressNotifications':
    if (array_key_exists('nonce', $_REQUEST) && $dashboard->isValidNonce('suppressNotifications', $_REQUEST['nonce'])) {
      if (!empty($_REQUEST['range']) && !empty($_REQUEST['element']) && !empty($_REQUEST['sensor_id'])) {
        $output['success'] = $dashboard->suppressNotifications($_REQUEST['range'], $_REQUEST['element'], $_REQUEST['sensor_id']);
        $dashboard->expireNonce('suppressNotifications', $_REQUEST['nonce']);
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 401 Unauthorized');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
}

if ($putEvent) {
  $user_id = array_key_exists('authenticated', $_SESSION) ? $_SESSION['user_id'] : null;
  $dashboard->putEvent($user_id, $_REQUEST['func'], array_merge(array_intersect_key($output, $logFields), $log));
}

header('Content-Type: application/json');
echo json_encode($output);
?>
