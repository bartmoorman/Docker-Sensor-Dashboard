#!/usr/bin/php
<?php
require_once('/var/www/html/inc/dashboard.class.php');
$dashboard = new Dashboard(false, false, false, false);

while (true) {
  foreach ($dashboard->getSensorNotifications() as $sensor) {
    if ($reading = $dashboard->getReadingsAverage($sensor['sensor_id'], 5)) {
      if ($reading['count']) {
        if ($dashboard->memcacheConn->get(sprintf('notifiedInsufficientData-%u', $sensor['sensor_id']))) {
          $message = sprintf('%s (sensor_id: %u) has sufficient data - %u reading(s)', $sensor['name'], $sensor['sensor_id'], $reading['count']);
          msg_send($dashboard->queueConn, 2, $message);
          $dashboard->memcacheConn->delete(sprintf('notifiedInsufficientData-%u', $sensor['sensor_id']));
        }
        foreach (['temperature' => $dashboard->temperature['key'], 'humidity' => '%'] as $element => $key) {
          if (strlen($sensor['min_' . $element])) {
            if ($reading[$element] < $sensor['min_' . $element] && !$dashboard->memcacheConn->get(sprintf('notifiedMin%s-%u', ucfirst($element), $sensor['sensor_id']))) {
              $message = sprintf('%s (sensor_id: %u) %s is too low - %0.2f%s < %0.2f%s', $sensor['name'], $sensor['sensor_id'], $element, $reading[$element], $key, $sensor['min_' . $element], $key);
              msg_send($dashboard->queueConn, 2, $message);
              $dashboard->memcacheConn->set(sprintf('notifiedMin%s-%u', ucfirst($element), $sensor['sensor_id']), time(), 60 * 30);
            } elseif ($reading[$element] > $sensor['min_' . $element] && $dashboard->memcacheConn->get(sprintf('notifiedMin%s-%u', ucfirst($element), $sensor['sensor_id']))) {
              $message = sprintf('%s (sensor_id: %u) %s is within range - %0.2f%s > %0.2f%s', $sensor['name'], $sensor['sensor_id'], $element, $reading[$element], $key, $sensor['min_' . $element], $key);
              msg_send($dashboard->queueConn, 2, $message);
              $dashboard->memcacheConn->delete(sprintf('notifiedMin%s-%u', ucfirst($element), $sensor['sensor_id']));
            }
          }
          if (strlen($sensor['max_' . $element])) {
            if ($reading[$element] > $sensor['max_' . $element] && !$dashboard->memcacheConn->get(sprintf('notifiedMax%s-%u', ucfirst($element), $sensor['sensor_id']))) {
              $message = sprintf('%s (sensor_id: %u) %s is too high - %0.2f%s > %0.2f%s', $sensor['name'], $sensor['sensor_id'], $element, $reading[$element], $key, $sensor['max_' . $element], $key);
              msg_send($dashboard->queueConn, 2, $message);
              $dashboard->memcacheConn->set(sprintf('notifiedMax%s-%u', ucfirst($element), $sensor['sensor_id']), time(), 60 * 30);
            } elseif ($reading[$element] < $sensor['max_' . $element] && $dashboard->memcacheConn->get(sprintf('notifiedMax%s-%u', ucfirst($element), $sensor['sensor_id']))) {
              $message = sprintf('%s (sensor_id: %u) %s is within range - %0.2f%s < %0.2f%s', $sensor['name'], $sensor['sensor_id'], $element, $reading[$element], $key, $sensor['max_' . $element], $key);
              msg_send($dashboard->queueConn, 2, $message);
              $dashboard->memcacheConn->delete(sprintf('notifiedMax%s-%u', ucfirst($element), $sensor['sensor_id']));
            }
          }
        }
      } elseif (!$dashboard->memcacheConn->get(sprintf('notifiedInsufficientData-%u', $sensor['sensor_id']))) {
        $message = sprintf('%s (sensor_id: %u) has insufficient data - %u reading(s)', $sensor['name'], $sensor['sensor_id'], $reading['count']);
        msg_send($dashboard->queueConn, 2, $message);
        $dashboard->memcacheConn->set(sprintf('notifiedInsufficientData-%u', $sensor['sensor_id']), time(), 60 * 30);
      }
    }
  }
  sleep(60);
}
?>
