#!/usr/bin/php
<?php
require_once('/var/www/html/inc/dashboard.class.php');
$dashboard = new Dashboard(false, false, false, false);

while (true) {
  foreach ($dashboard->getSensorNotifications() as $sensor) {
    if ($reading = $dashboard->getAverage($sensor['sensor_id'], 5)) {
      foreach (array('temperature', 'humidity') as $element) {
        if ($sensor['min_' . $element] && $reading[$element] < $sensor['min_' . $element] && !$sensor['notified_min_' . $element]) {
          $message = sprintf('%s too low (%.1f < %.1f) for %s' . PHP_EOL, ucfirst($element), $reading[$element], $sensor['min_' . $element], $sensor['name']);
          if ($dashboard->sendNotification($message)) {
            echo date('Y-m-d\TH:i:s') . " - notification sent - {$message}" . PHP_EOL;
            $dashboard->putSensorNotification($sensor['sensor_id'], 'notified_min_' . $element, true);
          }
        } elseif ($sensor['min_' . $element] && $reading[$element] > $sensor['min_' . $element] && $sensor['notified_min_' . $element]) {
          $message = sprintf('%s in range (%.1f > %.1f) for %s' . PHP_EOL, ucfirst($element), $reading[$element], $sensor['min_' . $element], $sensor['name']);
          if ($dashboard->sendNotification($message)) {
            echo date('Y-m-d\TH:i:s') . " - notification sent - {$message}" . PHP_EOL;
            $dashboard->putSensorNotification($sensor['sensor_id'], 'notified_min_' . $element, false);
          }
        }

        if ($sensor['max_' . $element] && $reading[$element] > $sensor['max_' . $element] && !$sensor['notified_max_' . $element]) {
          $message = sprintf('%s too high (%.1f > %.1f) for %s' . PHP_EOL, ucfirst($element), $reading[$element], $sensor['max_' . $element], $sensor['name']);
          if ($dashboard->sendNotification($message)) {
            echo date('Y-m-d\TH:i:s') . " - notification sent - {$message}" . PHP_EOL;
            $dashboard->putSensorNotification($sensor['sensor_id'], 'notified_max_' . $element, true);
          }
        } elseif ($sensor['max_' . $element] && $reading[$element] < $sensor['max_' . $element] && $sensor['notified_max_' . $element]) {
          $message = sprintf('%s in range (%.1f < %.1f) for %s' . PHP_EOL, ucfirst($element), $reading[$element], $sensor['max_' . $element], $sensor['name']);
          if ($dashboard->sendNotification($message)) {
            echo date('Y-m-d\TH:i:s') . " - notification sent - {$message}" . PHP_EOL;
            $dashboard->putSensorNotification($sensor['sensor_id'], 'notified_max_' . $element, false);
          }
        }
      }
    }
  }
  sleep(60);
}
?>
