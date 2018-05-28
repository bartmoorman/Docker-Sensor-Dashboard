<?php
require_once('../inc/dashboard.class.php');
$dashboard = new Dashboard(false, false, false, false);

$output = $logFields = array('success' => null, 'message' => null);
$log = array();
$logEvent = true;

switch ($_REQUEST['func']) {
  case 'validatePinCode':
    if (!empty($_REQUEST['pincode'])) {
      $output['success'] = $dashboard->authenticateSession($_REQUEST['pincode']);
      $log['pincode'] = $_REQUEST['pincode'];
      usleep(rand(1000000, 1250000));
    } else {
      $output['success'] = false;
      $output['message'] = 'No pincode supplied';
    }
    break;
  case 'createUser':
    if (!$dashboard->isConfigured() || ($dashboard->isValidSession() && $dashboard->isAdmin())) {
      if (!empty($_REQUEST['pincode']) && !empty($_REQUEST['first_name']) && !empty($_REQUEST['role'])) {
        $last_name = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : null;
        $email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : null;
        $pushover_user = !empty($_REQUEST['pushover_user']) ? $_REQUEST['pushover_user'] : null;
        $pushover_token = !empty($_REQUEST['pushover_token']) ? $_REQUEST['pushover_token'] : null;
        $output['success'] = $dashboard->createUser($_REQUEST['pincode'], $_REQUEST['first_name'], $last_name, $email, $pushover_user, $pushover_token, $_REQUEST['role']);
      } else {
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'updateUser':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['user_id']) && !empty($_REQUEST['pincode']) && !empty($_REQUEST['first_name']) && !empty($_REQUEST['role'])) {
        $last_name = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : null;
        $email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : null;
        $pushover_user = !empty($_REQUEST['pushover_user']) ? $_REQUEST['pushover_user'] : null;
        $pushover_token = !empty($_REQUEST['pushover_token']) ? $_REQUEST['pushover_token'] : null;
        $output['success'] = $dashboard->updateUser($_REQUEST['user_id'], $_REQUEST['pincode'], $_REQUEST['first_name'], $last_name, $email, $pushover_user, $pushover_token, $_REQUEST['role']);
        $log['user_id'] = $_REQUEST['user_id'];
      } else {
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'modifyUser':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['action']) && !empty($_REQUEST['user_id'])) {
        $output['success'] = $dashboard->modifyUser($_REQUEST['action'], $_REQUEST['user_id']);
        $log['action'] = $_REQUEST['action'];
        $log['user_id'] = $_REQUEST['user_id'];
      } else {
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'createSensor':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['name'])) {
        $min_temperature = isset($_REQUEST['min_temperature']) ? $_REQUEST['min_temperature'] : null;
        $max_temperature = isset($_REQUEST['max_temperature']) ? $_REQUEST['max_temperature'] : null;
        $min_humidity = isset($_REQUEST['min_humidity']) ? $_REQUEST['min_humidity'] : null;
        $max_humidity = isset($_REQUEST['max_humidity']) ? $_REQUEST['max_humidity'] : null;
        $output['success'] = $dashboard->createSensor($_REQUEST['name'], $min_temperature, $max_temperature, $min_humidity, $max_humidity);
      } else {
        $output['success'] = false;
        $output['message'] = 'No name supplied';
      }
    } else {
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'updateSensor':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['sensor_id']) && !empty($_REQUEST['name']) && !empty($_REQUEST['token'])) {
        $min_temperature = isset($_REQUEST['min_temperature']) ? $_REQUEST['min_temperature'] : null;
        $max_temperature = isset($_REQUEST['max_temperature']) ? $_REQUEST['max_temperature'] : null;
        $min_humidity = isset($_REQUEST['min_humidity']) ? $_REQUEST['min_humidity'] : null;
        $max_humidity = isset($_REQUEST['max_humidity']) ? $_REQUEST['max_humidity'] : null;
        $output['success'] = $dashboard->updateSensor($_REQUEST['sensor_id'], $_REQUEST['name'], $_REQUEST['token'], $min_temperature, $max_temperature, $min_humidity, $max_humidity);
        $log['sensor_id'] = $_REQUEST['sensor_id'];
      } else {
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'modifySensor':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['action']) && !empty($_REQUEST['sensor_id'])) {
        $output['success'] = $dashboard->modifySensor($_REQUEST['action'], $_REQUEST['sensor_id']);
        $log['action'] = $_REQUEST['action'];
        $log['sensor_id'] = $_REQUEST['sensor_id'];
      } else {
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'userDetails':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['user_id'])) {
        if ($output['data'] = $dashboard->getUserDetails($_REQUEST['user_id'])) {
          $output['success'] = true;
          $logEvent = false;
        } else {
          $output['success'] = false;
          $log['user_id'] = $_REQUEST['user_id'];
        }
      } else {
        $output['success'] = false;
        $output['message'] = 'No user id supplied';
      }
    } else {
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'sensorDetails':
    if ($dashboard->isValidSession() && $dashboard->isAdmin()) {
      if (!empty($_REQUEST['sensor_id'])) {
        if ($output['data'] = $dashboard->getSensorDetails($_REQUEST['sensor_id'])) {
          $output['success'] = true;
          $logEvent = false;
        } else {
          $output['success'] = false;
          $log['sensor_id'] = $_REQUEST['sensor_id'];
        }
      } else {
        $output['success'] = false;
        $output['message'] = 'No sensor id supplied';
      }
    } else {
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'putReading':
    if (!empty($_REQUEST['token']) && !empty($_REQUEST['temperature']) && !empty($_REQUEST['humidity'])) {
      $output['success'] = $dashboard->putReading($_REQUEST['token'], $_REQUEST['temperature'], $_REQUEST['humidity']);
      $logEvent = false;
    } else {
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
    }
    break;
  case 'getReadings':
    if ($dashboard->isValidSession()) {
      if (!empty($_REQUEST['sensor_id'])) {
        $days = !empty($_REQUEST['days']) ? $_REQUEST['days'] : 1;
        $granularity = !empty($_REQUEST['granularity']) ? $_REQUEST['granularity'] : null;
        if ($output['data'] = $dashboard->getReadings($_REQUEST['sensor_id'], $days, $granularity)) {
          $output['success'] = true;
          $logEvent = false;
        } else {
          $output['success'] = false;
          $log['sensor_id'] = $_REQUEST['sensor_id'];
        }
      } else {
        $output['success'] = false;
        $output['message'] = 'No sensor id supplied';
      }
    } else {
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
}

if ($logEvent) {
  $dashboard->logEvent($_REQUEST['func'], array_merge(array_intersect_key($output, $logFields), $log));
}

echo json_encode($output);
?>
