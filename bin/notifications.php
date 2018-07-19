#!/usr/bin/php
<?php
require_once('/var/www/html/inc/dashboard.class.php');
$dashboard = new Dashboard(false, false, false, false);

while (true) {
  $messages = [];
  foreach ($dashboard->getSensorNotifications() as $sensor) {
    if ($reading = $dashboard->getReadingsAverage($sensor['sensor_id'], 5)) {
      foreach (['temperature' => $dashboard->temperature['key'], 'humidity' => '%'] as $element => $key) {
        if (strlen($sensor['min_' . $element])) {
          if ($reading[$element] < $sensor['min_' . $element] && !$sensor['notified_min_' . $element]) {
            $messages[] = sprintf('%s (sensor_id: %u) %s is too low (%0.2f %s < %0.2f %s)', $sensor['name'], $sensor['sensor_id'], $element, $reading[$element], $key, $sensor['min_' . $element], $key);
            $dashboard->modifyObject('notified', 'sensor_id', $sensor['sensor_id'], 'min_' . $element, 1);
          } elseif ($reading[$element] > $sensor['min_' . $element] && $sensor['notified_min_' . $element]) {
            $messages[] = sprintf('%s (sensor_id: %u) %s is within range (%0.2f %s > %0.2f %s)', $sensor['name'], $sensor['sensor_id'], $element, $reading[$element], $key, $sensor['min_' . $element], $key);
            $dashboard->modifyObject('notified', 'sensor_id', $sensor['sensor_id'], 'min_' . $element, 0);
          }
        }

        if (strlen($sensor['max_' . $element])) {
          if ($reading[$element] > $sensor['max_' . $element] && !$sensor['notified_max_' . $element]) {
            $messages[] = sprintf('%s (sensor_id: %u) %s is too high (%0.2f %s > %0.2f %s)', $sensor['name'], $sensor['sensor_id'], $element, $reading[$element], $key, $sensor['max_' . $element], $key);
            $dashboard->modifyObject('notified', 'sensor_id', $sensor['sensor_id'], 'max_' . $element, 1);
          } elseif ($reading[$element] < $sensor['max_' . $element] && $sensor['notified_max_' . $element]) {
            $messages[] = sprintf('%s (sensor_id: %u) %s is within range (%0.2f %s < %0.2f %s)', $sensor['name'], $sensor['sensor_id'], $element, $reading[$element], $key, $sensor['max_' . $element], $key);
            $dashboard->modifyObject('notified', 'sensor_id', $sensor['sensor_id'], 'max_' . $element, 0);
          }
        }
      }
    }
  }
  $dashboard->sendNotifications($messages);
  sleep(60);
}
?>
