<?php
class Dashboard {
  private $dbFile = '/config/dashboard.db';
  private $dbConn = null;

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
  `disabled` INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS `readings` (
  `reading_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `sensor_id` INTEGER NOT NULL,
  `date` INTEGER DEFAULT (STRFTIME('%s', 'now')),
  `temperature` NUMERIC,
  `humidity` NUMERIC
);
EOQ;
    return $this->dbConn->exec($query);
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

  public function createUser($pincode, $first_name, $last_name = null, $email = null, $role) {
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
      $role = $this->dbConn->escapeString($role);
      $query = <<<EOQ
INSERT
INTO `users` (`pincode`, `first_name`, `last_name`, `email`, `role`)
VALUES ('{$pincode}', '{$first_name}', '{$last_name}', '{$email}', '{$role}');
EOQ;
      return $this->dbConn->exec($query);
    }
    return false;
  }

  public function updateUser($user_id, $pincode, $first_name, $last_name = null, $email = null, $role) {
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
      $role = $this->dbConn->escapeString($role);
      $query = <<<EOQ
UPDATE `users`
SET (`pincode`, `first_name`, `last_name`, `email`, `role`) = ('{$pincode}', '{$first_name}', '{$last_name}', '{$email}', '{$role}')
WHERE `user_id` = '{$user_id}';
EOQ;
      return $this->dbConn->exec($query);
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
    return $this->dbConn->exec($query);
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

  public function createSensor($name, $length = 16) {
    $token = bin2hex(random_bytes($length / 2));
    $query = <<<EOQ
SELECT COUNT(*)
FROM `sensors`
WHERE `token` LIKE '{$token}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $name = $this->dbConn->escapeString($name);
      $query = <<<EOQ
INSERT
INTO `sensors` (`name`, `token`)
VALUES ('{$name}', '{$token}');
EOQ;
      return $this->dbConn->exec($query);
    }
    return false;
  }

  public function updateSensor($sensor_id, $name, $token) {
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
      $query = <<<EOQ
UPDATE `sensors`
SET (`name`, `token`) = ('{$name}', '{$token}')
WHERE `sensor_id` = '{$sensor_id}';
EOQ;
      return $this->dbConn->exec($query);
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
    return $this->dbConn->exec($query);
  }

  public function getUsers() {
    $query = <<<EOQ
SELECT `user_id`, SUBSTR('000000'||`pincode`,-6) AS `pincode`, `first_name`, `last_name`, `email`, `role`, `disabled`
FROM `users`
ORDER BY `last_name`, `first_name`
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
SELECT `user_id`, SUBSTR('000000'||`pincode`,-6) AS `pincode`, `first_name`, `last_name`, `email`, `role`, `disabled`
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
SELECT `sensor_id`, `name`, `token`, `disabled`
FROM `sensors`
ORDER BY `name`
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
SELECT `sensor_id`, `name`, `token`
FROM `sensors`
WHERE `sensor_id` = '{$sensor_id}'
EOQ;
    if ($sensor = $this->dbConn->querySingle($query, true)) {
      return $sensor;
    }
    return false;
  }

  public function putReading($token, $temperature, $humidity) {
    $token = $this->dbConn->escapeString($token);
    if ($this->isValidToken($token)) {
      $temperature = $this->dbConn->escapeString($temperature);
      $humidity = $this->dbConn->escapeString($humidity);
      $query = <<<EOQ
INSERT
INTO `readings` (`sensor_id`, `temperature`, `humidity`)
VALUES ((SELECT `sensor_id` FROM `sensors` WHERE `token` LIKE '{$token}'), '{$temperature}', '{$humidity}');
EOQ;
      return $this->dbConn->exec($query);
    }
    return false;
  }

  public function getReadings($sensor_id, $days = null, $granularity = null) {
    $sensor_id = $this->dbConn->escapeString($sensor_id);
    $days = !empty($days) ? $this->dbConn->escapeString($days) : 1;
    switch ($granularity) {
      case 'y':
      case 'year':
        $granule = '%Y';
        break;
      case 'm':
      case 'month':
        $granule = '%Y-%m';
        break;
      case 'd':
      case 'day':
        $granule = '%Y-%m-%d';
        break;
      case 'h':
      case 'hour':
        $granule = '%Y-%m-%dT%H';
        break;
      default:
        $granule = '%Y-%m-%dT%H:%M';
    }
    $query = <<<EOQ
SELECT STRFTIME('{$granule}', DATETIME(`date`, 'unixepoch'), 'localtime') AS `date`, ROUND(AVG(`temperature`), 1) AS `temperature`, ROUND(AVG(`humidity`), 1) AS `humidity`
FROM `readings`
WHERE `sensor_id` = '{$sensor_id}'
AND `date` > STRFTIME('%s', DATETIME('now', '-{$days} days'))
GROUP BY STRFTIME('{$granule}', DATETIME(`date`, 'unixepoch'))
ORDER BY `date`
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
    return $this->dbConn->exec($query);
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
