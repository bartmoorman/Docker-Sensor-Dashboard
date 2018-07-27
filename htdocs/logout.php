<?php
require_once('inc/dashboard.class.php');
$dashboard = new Dashboard(true, true, false, false);

if ($dashboard->deauthenticateSession()) {
  header('Location: login.php');
} else {
  header('Location: index.php');
}
?>
