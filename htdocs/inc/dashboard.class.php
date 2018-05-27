<?php
class Dashboard {
  private $dbFile = '/config/dashboard.db';
  private $dbConn = null;

  public function __construct() {
    session_start();

    if (is_writable($this->dbFile)) {
      $this->connectDb();
    } elseif (is_writable(dirname($this->dbFile))) {
      $this->connectDb();
      $this->initDb();
    }
  }

  private function connectDb() {
    $this->dbConn = new SQLite3($this->dbFile);
    $this->dbConn->busyTimeout(500);
    $this->dbConn->exec('PRAGMA journal_mode = WAL');
  }

  private function initDb() {
    $query = <<<EOQ
CREATE TABLE IF NOT EXISTS `readings` (
  `reading_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `sensor_id` INTEGER NOT NULL,
  `date` INTEGER DEFAULT (STRFTIME('%s', 'now')),
  `temperature` NUMERIC,
  `humidity` NUMERIC
);
CREATE TABLE IF NOT EXISTS `sensors` (
  `sensor_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `token` TEXT NOT NULL UNIQUE,
  `disabled` INTEGER NOT NULL DEFAULT 0
);
EOQ;
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
VALUES ('{$name}', {$token});
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

  public function sensorReading($token, $temperature, $humidity) {
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
}
?>
