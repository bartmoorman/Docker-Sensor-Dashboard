<?php
require_once('../inc/dashboard.class.php');
$dashboard = new Dashboard();

$output = $logFields = array('success' => null, 'message' => null);
$log = array();

switch ($_REQUEST['func']) {
  case 'createSensor':
    if (!empty($_REQUEST['name'])) {
      $output['success'] = $dashboard->createSensor($_REQUEST['name']);
    } else {
      $output['success'] = false;
      $output['message'] = 'No name supplied';
    }
    break;
  case 'updateSensor':
    if (!empty($_REQUEST['sensor_id']) && !empty($_REQUEST['name']) && !empty($_REQUEST['token'])) {
      $output['success'] = $dashboard->updateSensor($_REQUEST['sensor_id'], $_REQUEST['name'], $_REQUEST['token']);
      $log['sensor_id'] = $_REQUEST['sensor_id'];
    } else {
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
    }
    break;
  case 'modifySensor':
    if (!empty($_REQUEST['action']) && !empty($_REQUEST['sensor_id'])) {
      $output['success'] = $dashboard->modifySensor($_REQUEST['action'], $_REQUEST['sensor_id']);
      $log['action'] = $_REQUEST['action'];
      $log['sensor_id'] = $_REQUEST['sensor_id'];
    } else {
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
    }
    break;
  case 'sensorDetails':
    if (!empty($_REQUEST['sensor_id'])) {
      if ($output['data'] = $dashboard->getSensorDetails($_REQUEST['sensor_id'])) {
        $output['success'] = true;
      } else {
        $output['success'] = false;
      }
      $log['sensor_id'] = $_REQUEST['sensor_id'];
    } else {
      $output['success'] = false;
      $output['message'] = 'No sensor id supplied';
    }
    break;
  case 'sensorReading':
    if (!empty($_REQUEST['token']) && !empty($_REQUEST['temperature']) && !empty($_REQUEST['humidity'])) {
      $output['success'] = $dashboard->sensorReading($_REQUEST['token'], $_REQUEST['temperature'], $_REQUEST['humidity']);
    } else {
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
    }
    break;
  case 'getReadings':
    if (!empty($_REQUEST['sensor_id'])) {
      $days = !empty($_REQUEST['days']) ? $_REQUEST['days'] : null;
      $granularity = !empty($_REQUEST['granularity']) ? $_REQUEST['granularity'] : null;
      if ($output['data'] = $dashboard->getReadings($_REQUEST['sensor_id'], $days, $granularity)) {
        $output['success'] = true;
      } else {
        $output['success'] = false;
      }
      $log['sensor_id'] = $_REQUEST['sensor_id'];
    } else {
      $output['success'] = false;
      $output['message'] = 'No sensor id supplied';
    }
    break;
}

echo json_encode($output);
?>
