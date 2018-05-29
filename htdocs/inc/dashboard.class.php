<?php
class Dashboard {
  private $dbFile = '/config/dashboard.db';
  private $dbConn = null;
  public $pageLimit = 20;

  public function __construct($requireConfigured = true, $requireValidSession = true, $requireAdmin = true, $requireIndex = false) {
    session_start();

    if (is_writable($this->dbFile)) {
      $this->connectDb();
    } elseif (is_writable(dirname($this->dbFile))) {
      $this->connectDb();
      $this->initDb();
    }

    if ($this->isConfigured()) {
      if ($this->isValidSession()) {
        if (($requireAdmin && !$this->isAdmin()) || $requireIndex) {
          header('Location: index.php');
          exit;
        }
      } elseif ($requireValidSession) {
        header('Location: login.php');
        exit;
      }
    } elseif ($requireConfigured) {
      header('Location: setup.php');
      exit;
    }
  }

  private function connectDb() {
    $this->dbConn = new SQLite3($this->dbFile);
    $this->dbConn->busyTimeout(500);
    $this->dbConn->exec('PRAGMA journal_mode = WAL');
  }

  private function initDb() {
    $query = <<<EOQ
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `pincode` INTEGER NOT NULL UNIQUE,
  `first_name` TEXT NOT NULL,
  `last_name` TEXT,
  `email` TEXT,
  `pushover_user` TEXT,
  `pushover_token` TEXT,
  `role` TEXT NOT NULL,
  `disabled` INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `date` INTEGER DEFAULT (STRFTIME('%s', 'now')),
  `user_id` INTEGER,
  `action` TEXT,
  `message` BLOB,
  `remote_addr` INTEGER
);
CREATE TABLE IF NOT EXISTS `sensors` (
  `sensor_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `token` TEXT NOT NULL UNIQUE,
  `min_temperature` NUMERIC,
  `max_temperature` NUMERIC,
  `min_humidity` NUMERIC,
  `max_humidity` NUMERIC,
  `notified_min_temperature` INTEGER NOT NULL DEFAULT 0,
  `notified_max_temperature` INTEGER NOT NULL DEFAULT 0,
  `notified_min_humidity` INTEGER NOT NULL DEFAULT 0,
  `notified_max_humidity` INTEGER NOT NULL DEFAULT 0,
  `disabled` INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS `readings` (
  `reading_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `date` INTEGER DEFAULT (STRFTIME('%s', 'now')),
  `sensor_id` INTEGER NOT NULL,
  `temperature` NUMERIC,
  `humidity` NUMERIC
);
EOQ;
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  public function isConfigured() {
    $query = <<<EOQ
SELECT COUNT(*)
FROM `users`;
EOQ;
    if ($this->dbConn->querySingle($query)) {
      return true;
    }
    return false;
  }

  public function isValidSession() {
    if (array_key_exists('authenticated', $_SESSION) && $this->isValidUser('user_id', $_SESSION['user_id'])) {
      return true;
    }
    return false;
  }

  public function isAdmin() {
    $user_id = $_SESSION['user_id'];
    $query = <<<EOQ
SELECT COUNT(*)
FROM `users`
WHERE `user_id` = '{$user_id}'
AND `role` LIKE 'admin';
EOQ;
    if ($this->dbConn->querySingle($query)) {
      return true;
    }
    return false;
  }

  public function isValidUser($type, $value) {
    $type = $this->dbConn->escapeString($type);
    $value = $this->dbConn->escapeString($value);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `users`
WHERE `{$type}` = '{$value}'
AND NOT `disabled`;
EOQ;
    if ($this->dbConn->querySingle($query)) {
      return true;
    }
    return false;
  }

  public function authenticateSession($pincode) {
    if ($this->isValidUser('pincode', $pincode)) {
      $pincode = $this->dbConn->escapeString($pincode);
      $query = <<<EOQ
SELECT `user_id`
FROM `users`
WHERE `pincode` = '{$pincode}';
EOQ;
      if ($user_id = $this->dbConn->querySingle($query)) {
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = $user_id;
        return true;
      }
    }
    return false;
  }

  public function deauthenticateSession() {
    if (session_destroy()) {
      return true;
    }
    return false;
  }

  public function putSessionDetail($key, $value) {
    if ($_SESSION[$key] = $value) {
      return true;
    }
    return false;
  }

  public function getSessionDetails() {
    if (!empty($_SESSION)) {
      return $_SESSION;
    }
    return false;
  }

  public function createUser($pincode, $first_name, $last_name = null, $email = null, $pushover_user = null, $pushover_token = null, $role) {
    $pincode = $this->dbConn->escapeString($pincode);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `users`
WHERE `pincode` = '{$pincode}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $first_name = $this->dbConn->escapeString($first_name);
      $last_name = $this->dbConn->escapeString($last_name);
      $email = $this->dbConn->escapeString($email);
      $pushover_user = $this->dbConn->escapeString($pushover_user);
      $pushover_token = $this->dbConn->escapeString($pushover_token);
      $role = $this->dbConn->escapeString($role);
      $query = <<<EOQ
INSERT
INTO `users` (`pincode`, `first_name`, `last_name`, `email`, `pushover_user`, `pushover_token`, `role`)
VALUES ('{$pincode}', '{$first_name}', '{$last_name}', '{$email}', '{$pushover_user}', '{$pushover_token}', '{$role}');
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function updateUser($user_id, $pincode, $first_name, $last_name = null, $email = null, $pushover_user = null, $pushover_token = null, $role) {
    $user_id = $this->dbConn->escapeString($user_id);
    $pincode = $this->dbConn->escapeString($pincode);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `users`
WHERE `user_id` != '{$user_id}'
AND `pincode` = '{$pincode}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $first_name = $this->dbConn->escapeString($first_name);
      $last_name = $this->dbConn->escapeString($last_name);
      $email = $this->dbConn->escapeString($email);
      $pushover_user = $this->dbConn->escapeString($pushover_user);
      $pushover_token = $this->dbConn->escapeString($pushover_token);
      $role = $this->dbConn->escapeString($role);
      $query = <<<EOQ
UPDATE `users`
SET
  `pincode` = '{$pincode}',
  `first_name` = '{$first_name}',
  `last_name` = '{$last_name}',
  `email` = '{$email}',
  `pushover_user` = '{$pushover_user}',
  `pushover_token` = '{$pushover_token}',
  `role` = '{$role}'
WHERE `user_id` = '{$user_id}';
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function modifyUser($action, $user_id) {
    $user_id = $this->dbConn->escapeString($user_id);
    switch ($action) {
      case 'enable':
        $query = <<<EOQ
UPDATE `users`
SET `disabled` = '0'
WHERE `user_id` = '{$user_id}';
EOQ;
        break;
      case 'disable':
        $query = <<<EOQ
UPDATE `users`
SET `disabled` = '1'
WHERE `user_id` = '{$user_id}';
EOQ;
        break;
      case 'delete':
        $query = <<<EOQ
DELETE
FROM `users`
WHERE `user_id` = '{$user_id}';
DELETE
FROM `events`
WHERE `user_id` = '{$user_id}';
EOQ;
        break;
    }
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  public function isValidToken($token) {
    $token = $this->dbConn->escapeString($token);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `sensors`
WHERE `token` LIKE '{$token}'
AND NOT `disabled`;
EOQ;
    if ($this->dbConn->querySingle($query)) {
      return true;
    }
    return false;
  }

  public function createSensor($name, $min_temperature = null, $max_temperature = null, $min_humidity = null, $max_humidity = null) {
    $token = bin2hex(random_bytes(8));
    $query = <<<EOQ
SELECT COUNT(*)
FROM `sensors`
WHERE `token` LIKE '{$token}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $name = $this->dbConn->escapeString($name);
      $min_temperature = $this->dbConn->escapeString($min_temperature);
      $max_temperature = $this->dbConn->escapeString($max_temperature);
      $min_humidity = $this->dbConn->escapeString($min_humidity);
      $max_humidity = $this->dbConn->escapeString($max_humidity);
      $query = <<<EOQ
INSERT
INTO `sensors` (`name`, `token`, `min_temperature`, `max_temperature`, `min_humidity`, `max_humidity`)
VALUES ('{$name}', '{$token}', '{$min_temperature}', '{$max_temperature}', '{$min_humidity}', '{$max_humidity}');
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function updateSensor($sensor_id, $name, $token, $min_temperature = null, $max_temperature = null, $min_humidity = null, $max_humidity = null) {
    $sensor_id = $this->dbConn->escapeString($sensor_id);
    $token = $this->dbConn->escapeString($token);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `sensors`
WHERE `sensor_id` != '{$sensor_id}'
AND `token` LIKE '{$token}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $name = $this->dbConn->escapeString($name);
      $min_temperature = $this->dbConn->escapeString($min_temperature);
      $max_temperature = $this->dbConn->escapeString($max_temperature);
      $min_humidity = $this->dbConn->escapeString($min_humidity);
      $max_humidity = $this->dbConn->escapeString($max_humidity);
      $query = <<<EOQ
UPDATE `sensors`
SET
  `name` = '{$name}',
  `token` = '{$token}',
  `min_temperature` = '{$min_temperature}',
  `max_temperature` = '{$max_temperature}',
  `min_humidity` = '{$min_humidity}',
  `max_humidity` = '{$max_humidity}'
WHERE `sensor_id` = '{$sensor_id}';
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function modifySensor($action, $sensor_id) {
    $sensor_id = $this->dbConn->escapeString($sensor_id);
    switch ($action) {
      case 'enable':
        $query = <<<EOQ
UPDATE `sensors`
SET `disabled` = '0'
WHERE `sensor_id` = '{$sensor_id}';
EOQ;
        break;
      case 'disable':
        $query = <<<EOQ
UPDATE `sensors`
SET `disabled` = '1'
WHERE `sensor_id` = '{$sensor_id}';
EOQ;
        break;
      case 'delete':
        $query = <<<EOQ
DELETE
FROM `sensors`
WHERE `sensor_id` = '{$sensor_id}';
EOQ;
        break;
    }
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  public function getUsers() {
    $query = <<<EOQ
SELECT `user_id`, SUBSTR('000000'||`pincode`,-6) AS `pincode`, `first_name`, `last_name`, `email`, `pushover_user`, `pushover_token`, `role`, `disabled`
FROM `users`
ORDER BY `last_name`, `first_name`;
EOQ;
    if ($users = $this->dbConn->query($query)) {
      $output = array();
      while ($user = $users->fetchArray(SQLITE3_ASSOC)) {
        $output[] = $user;
      }
      return $output;
    }
    return false;
  }

  public function getUserDetails($user_id) {
    $user_id = $this->dbConn->escapeString($user_id);
    $query = <<<EOQ
SELECT `user_id`, SUBSTR('000000'||`pincode`,-6) AS `pincode`, `first_name`, `last_name`, `email`, `pushover_user`, `pushover_token`, `role`, `disabled`
FROM `users`
WHERE `user_id` = '{$user_id}';
EOQ;
    if ($user = $this->dbConn->querySingle($query, true)) {
      return $user;
    }
    return false;
  }

  public function getSensors() {
    $query = <<<EOQ
SELECT `sensor_id`, `name`, `token`, `min_temperature`, `max_temperature`, `min_humidity`, `max_humidity`, `disabled`
FROM `sensors`
ORDER BY `name`;
EOQ;
    if ($sensors = $this->dbConn->query($query)) {
      $output = array();
      while ($sensor = $sensors->fetchArray(SQLITE3_ASSOC)) {
        $output[] = $sensor;
      }
      return $output;
    }
    return false;
  }

  public function getSensorDetails($sensor_id) {
    $sensor_id = $this->dbConn->escapeString($sensor_id);
    $query = <<<EOQ
SELECT `sensor_id`, `name`, `token`, `min_temperature`, `max_temperature`, `min_humidity`, `max_humidity`
FROM `sensors`
WHERE `sensor_id` = '{$sensor_id}';
EOQ;
    if ($sensor = $this->dbConn->querySingle($query, true)) {
      return $sensor;
    }
    return false;
  }

  public function putReading($token, $temperature, $humidity) {
    $token = $this->dbConn->escapeString($token);
    if ($this->isValidToken($token)) {
      $query = <<<EOQ
SELECT `sensor_id`
FROM `sensors`
WHERE `token` LIKE '{$token}';
EOQ;
      if ($sensor_id = $this->dbConn->querySingle($query)) {
        $temperature = $this->dbConn->escapeString($temperature);
        $humidity = $this->dbConn->escapeString($humidity);
        $query = <<<EOQ
INSERT
INTO `readings` (`sensor_id`, `temperature`, `humidity`)
VALUES ('{$sensor_id}', '{$temperature}', '{$humidity}');
EOQ;
        if ($this->dbConn->exec($query)) {
          return true;
        }
      }
    }
    return false;
  }

  public function getReadings($sensor_id, $hours) {
    $sensor_id = $this->dbConn->escapeString($sensor_id);
    $hours = $this->dbConn->escapeString($hours);
    switch (true) {
      case $hours >= 24 * 30 * 12:
        $granule = '%Y-%m';
        break;
      case $hours >= 24 * 30:
        $granule = '%Y-%m-%d';
        break;
      case $hours >= 24:
        $granule = '%Y-%m-%dT%H';
        break;
      default:
        $granule = '%Y-%m-%dT%H:%M';
    }
    $query = <<<EOQ
SELECT STRFTIME('{$granule}', DATETIME(`date`, 'unixepoch'), 'localtime') AS `date`, ROUND(AVG(`temperature`), 1) AS `temperature`, ROUND(AVG(`humidity`), 1) AS `humidity`
FROM `readings`
WHERE `sensor_id` = '{$sensor_id}'
AND `date` > STRFTIME('%s', DATETIME('now', '-{$hours} hours'))
GROUP BY STRFTIME('{$granule}', DATETIME(`date`, 'unixepoch'))
ORDER BY `date`;
EOQ;
    if ($readings = $this->dbConn->query($query)) {
      $output = array('temperatureData' => array(), 'humidityData' => array());
      while ($reading = $readings->fetchArray(SQLITE3_ASSOC)) {
        $output['temperatureData'][] = array('x' => $reading['date'], 'y' => $reading['temperature']);
        $output['humidityData'][] = array('x' => $reading['date'], 'y' => $reading['humidity']);
      }
      return $output;
    }
    return false;
  }

  public function getMinMax($sensor_id, $hours) {
    $sensor_id = $this->dbConn->escapeString($sensor_id);
    $hours = $this->dbConn->escapeString($hours);
    $query = <<<EOQ
SELECT ROUND(MIN(`temperature`), 1) AS `min_temperature`, ROUND(MAX(`temperature`), 1) AS `max_temperature`, ROUND(MIN(`humidity`), 1) AS `min_humidity`, ROUND(MAX(`humidity`), 1) AS `max_humidity`
FROM `readings`
WHERE `sensor_id` = '${sensor_id}'
AND `date` > STRFTIME('%s', DATETIME('now', '-{$hours} hours'));
EOQ;
    if ($reading = $this->dbConn->querySingle($query, true)) {
      $output = array(
        'temperature' => array(
          'suggestedMin' => $reading['min_temperature'] < -38.8 ? $reading['min_temperature'] : $reading['min_temperature'] - 1.2,
          'suggestedMax' => $reading['max_temperature'] > 78.8 ? $reading['max_temperature'] : $reading['max_temperature'] + 1.2
        ),
        'humidity' => array(
          'suggestedMin' => $reading['min_humidity'] < 1 ? $reading['min_humidity'] : $reading['min_humidity'] - 1,
          'suggestedMax' => $reading['max_humidity'] > 99 ? $reading['max_humidity'] : $reading['max_humidity'] + 1
        )
      );
      return $output;
    }
    return false;
  }

  public function putSensorNotification($sensor_id, $type, $value) {
    $sensor_id = $this->dbConn->escapeString($sensor_id);
    $type = $this->dbConn->escapeString($type);
    $value = $this->dbConn->escapeString($value);
    $query = <<<EOQ
UPDATE `sensors`
SET `{$type}` = '{$value}'
WHERE `sensor_id` = '{$sensor_id}'
EOQ;
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  public function getSensorNotifications() {
    $query = <<<EOQ
SELECT `sensor_id`, `name`, `min_temperature`, `max_temperature`, `min_humidity`, `max_humidity`, `notified_min_temperature`, `notified_max_temperature`, `notified_min_humidity`, `notified_max_humidity`
FROM `sensors`
WHERE `min_temperature` OR `max_temperature` OR `min_humidity` OR `max_humidity`
AND NOT `disabled`;
EOQ;
    if ($sensors = $this->dbConn->query($query)) {
      $output = array();
      while ($sensor = $sensors->fetchArray(SQLITE3_ASSOC)) {
        $output[] = $sensor;
      }
      return $output;
    }
    return false;
  }

  public function getAverage($sensor_id, $minutes) {
    $sensor_id = $this->dbConn->escapeString($sensor_id);
    $minutes = $this->dbConn->escapeString($minutes);
    $query = <<<EOQ
SELECT ROUND(AVG(`temperature`), 1) AS `temperature`, ROUND(AVG(`humidity`), 1) AS `humidity`
FROM `readings`
WHERE `sensor_id` = '{$sensor_id}'
AND `date` > STRFTIME('%s', DATETIME('now', '-{$minutes} minutes'));
EOQ;
    if ($reading = $this->dbConn->querySingle($query, true)) {
      return $reading;
    }
    return false;
  }

  public function logEvent($action, $message = array()) {
    $user_id = array_key_exists('authenticated', $_SESSION) ? $_SESSION['user_id'] : null;
    $action = $this->dbConn->escapeString($action);
    $message = $this->dbConn->escapeString(json_encode($message));
    $remote_addr = ip2long(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $query = <<<EOQ
INSERT
INTO `events` (`user_id`, `action`, `message`, `remote_addr`)
VALUES ('{$user_id}', '{$action}', '{$message}', '{$remote_addr}');
EOQ;
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  public function getCount($type) {
    $type = $this->dbConn->escapeString($type);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `{$type}`;
EOQ;
    if ($count = $this->dbConn->querySingle($query)) {
      return $count;
    }
    return false;
  }

  public function getEvents($page = 1) {
    $start = ($page - 1) * $this->pageLimit;
    $query = <<<EOQ
SELECT `event_id`, STRFTIME('%s', DATETIME(`date`, 'unixepoch', 'localtime')) AS `date`, `user_id`, `first_name`, `last_name`, `action`, `message`, `remote_addr`, `disabled`
FROM `events`
LEFT JOIN `users` USING (`user_id`)
ORDER BY `date` DESC
LIMIT {$start}, {$this->pageLimit};
EOQ;
    if ($events = $this->dbConn->query($query)) {
      $output = array();
      while ($event = $events->fetchArray(SQLITE3_ASSOC)) {
        $output[] = $event;
      }
      return $output;
    }
    return false;
  }
}
?>
