<?php
require_once('../inc/dashboard.class.php');
$dashboard = new Dashboard(false, false, false, false);

$output = $logFields = ['success' => null, 'message' => null];
$log = [];
$putEvent = true;

switch ($_REQUEST['func']) {
  case 'authenticateSession':
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
      $output['success'] = $dashboard->authenticateSession($_POST['username'], $_POST['password']);
      $log['username'] = $_POST['username'];
      usleep(rand(750000, 1000000));
    } else {
      header('HTTP/1.1 400 Bad Request');
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
    }
    break;
  case 'createUser':
    if (!$dashboard->isConfigured() || ($dashboard->isValidSession() && $dashboard->isAdmin())) {
      if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['first_name']) && !empty($_POST['role'])) {
        $last_name = !empty($_POST['last_name']) ? $_POST['last_name'] : null;
        $pushover_user = !empty($_POST['pushover_user']) ? $_POST['pushover_user'] : null;
        $pushover_token = !empty($_POST['pushover_token']) ? $_POST['pushover_token'] : null;
        $pushover_priority = isset($_POST['pushover_priority']) ? $_POST['pushover_priority'] : null;
        $pushover_retry = isset($_POST['pushover_retry']) ? $_POST['pushover_retry'] : null;
        $pushover_expire = isset($_POST['pushover_expire']) ? $_POST['pushover_expire'] : null;
        $pushover_sound = !empty($_POST['pushover_sound']) ? $_POST['pushover_sound'] : null;
        $begin = !empty($_POST['begin']) ? $_POST['begin'] : null;
        $end = !empty($_POST['end']) ? $_POST['end'] : null;
        $output['success'] = $dashboard->createUser($_POST['username'], $_POST['password'], $_POST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_POST['role'], $begin, $end);
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
      if (!empty($_POST['name'])) {
        $key = isset($_POST['key']) ? $_POST['key'] : null;
        $min_temperature = isset($_POST['min_temperature']) ? $_POST['min_temperature'] : null;
        $max_temperature = isset($_POST['max_temperature']) ? $_POST['max_temperature'] : null;
        $min_humidity = isset($_POST['min_humidity']) ? $_POST['min_humidity'] : null;
        $max_humidity = isset($_POST['max_humidity']) ? $_POST['max_humidity'] : null;
        $output['success'] = $dashboard->createSensor($_POST['name'], $key, $min_temperature, $max_temperature, $min_humidity, $max_humidity);
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
      if (!empty($_POST['name'])) {
        $token = isset($_POST['token']) ? $_POST['token'] : null;
        $begin = !empty($_POST['begin']) ? $_POST['begin'] : null;
        $end = !empty($_POST['end']) ? $_POST['end'] : null;
        $output['success'] = $dashboard->createApp($_POST['name'], $token, $begin, $end);
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
      if (!empty($_POST['user_id']) && !empty($_POST['username']) && !empty($_POST['first_name']) && !empty($_POST['role'])) {
        $password = !empty($_POST['password']) ? $_POST['password'] : null;
        $last_name = !empty($_POST['last_name']) ? $_POST['last_name'] : null;
        $pushover_user = !empty($_POST['pushover_user']) ? $_POST['pushover_user'] : null;
        $pushover_token = !empty($_POST['pushover_token']) ? $_POST['pushover_token'] : null;
        $pushover_priority = isset($_POST['pushover_priority']) ? $_POST['pushover_priority'] : null;
        $pushover_retry = isset($_POST['pushover_retry']) ? $_POST['pushover_retry'] : null;
        $pushover_expire = isset($_POST['pushover_expire']) ? $_POST['pushover_expire'] : null;
        $pushover_sound = !empty($_POST['pushover_sound']) ? $_POST['pushover_sound'] : null;
        $begin = !empty($_POST['begin']) ? $_POST['begin'] : null;
        $end = !empty($_POST['end']) ? $_POST['end'] : null;
        $output['success'] = $dashboard->updateUser($_POST['user_id'], $_POST['username'], $password, $_POST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_POST['role'], $begin, $end);
        $log['user_id'] = $_POST['user_id'];
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
      if (!empty($_POST['sensor_id']) && !empty($_POST['name']) && !empty($_POST['key'])) {
        $min_temperature = isset($_POST['min_temperature']) ? $_POST['min_temperature'] : null;
        $max_temperature = isset($_POST['max_temperature']) ? $_POST['max_temperature'] : null;
        $min_humidity = isset($_POST['min_humidity']) ? $_POST['min_humidity'] : null;
        $max_humidity = isset($_POST['max_humidity']) ? $_POST['max_humidity'] : null;
        $output['success'] = $dashboard->updateSensor($_POST['sensor_id'], $_POST['name'], $_POST['key'], $min_temperature, $max_temperature, $min_humidity, $max_humidity);
        $log['sensor_id'] = $_POST['sensor_id'];
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
      if (!empty($_POST['app_id']) && !empty($_POST['name']) && !empty($_POST['token'])) {
        $begin = !empty($_POST['begin']) ? $_POST['begin'] : null;
        $end = !empty($_POST['end']) ? $_POST['end'] : null;
        $output['success'] = $dashboard->updateApp($_POST['app_id'], $_POST['name'], $_POST['token'], $begin, $end);
        $log['app_id'] = $_POST['app_id'];
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
      if (!empty($_POST['action']) && !empty($_POST['type']) && !empty($_POST['value'])) {
        $output['success'] = $dashboard->modifyObject($_POST['action'], $_POST['type'], $_POST['value']);
        $log['action'] = $_POST['action'];
        $log['type'] = $_POST['type'];
        $log['value'] = $_POST['value'];
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
    if (!empty($_POST['key']) && !empty($_POST['temperature']) && !empty($_POST['humidity'])) {
      $output['success'] = $dashboard->putReading($_POST['key'], $_POST['temperature'], $_POST['humidity']);
      $putEvent = false;
    } else {
      header('HTTP/1.1 400 Bad Request');
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
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
}

if ($putEvent) {
  $user_id = array_key_exists('authenticated', $_SESSION) ? $_SESSION['user_id'] : null;
  $dashboard->putEvent($user_id, $_REQUEST['func'], array_merge(array_intersect_key($output, $logFields), $log));
}

header('Content-Type: application/json');
echo json_encode($output);
?>
