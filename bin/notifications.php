#!/usr/bin/php
<?php
require_once('/var/www/html/inc/dashboard.class.php');
$dashboard = new Dashboard(false, false, false, false);

while (true) {
  $messages = [];
  while (msg_receive($dashboard->queueConn, 0, $msgtype, $dashboard->queueSize, $message, true, MSG_IPC_NOWAIT)) {
    $messages[] = $message;
  }
  $dashboard->sendNotifications($messages);
  sleep(5);
}
?>
