<?php
require_once('inc/dashboard.class.php');
$dashboard = new Dashboard(true, true, false, false);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title>Sensor Dashboard - Index</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css' integrity='sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm' crossorigin='anonymous'>
    <link rel='stylesheet' href='//bootswatch.com/4/darkly/bootstrap.min.css'>
    <link rel='stylesheet' href='//use.fontawesome.com/releases/v5.0.12/css/all.css' integrity='sha384-G0fIWCsCzJIMAVNQPfjH08cyYaUtMwjJwqiRKxxE/rx96Uroj1BtIQ6MLJuheaO9' crossorigin='anonymous'>
  </head>
  <body>
<?php
if ($dashboard->isAdmin()) {
  $homeLoc = dirname($_SERVER['PHP_SELF']);
  echo "    <nav class='navbar'>" . PHP_EOL;
  echo "      <button class='btn btn-sm btn-outline-success id-nav' data-href='{$homeLoc}'>Home</button>" . PHP_EOL;
  echo "      <button class='btn btn-sm btn-outline-info ml-auto mr-2 id-nav' data-href='sensors.php'>Sensors</button>" . PHP_EOL;
  echo "      <button class='btn btn-sm btn-outline-info mr-2 id-nav' data-href='users.php'>Users</button>" . PHP_EOL;
  echo "      <button class='btn btn-sm btn-outline-info id-nav' data-href='events.php'>Events</button>" . PHP_EOL;
  echo "    </nav>" . PHP_EOL;
}
?>
    <canvas id='chart'></canvas>
    <nav class='navbar text-center'>
      <select class='btn btn-sm btn-outline-success ml-auto mr-2 id-sensor-id' data-key='sensor_id'>
        <option value='0'>Sensor</option>
<?php
foreach ($dashboard->getSensors() as $sensor) {
  echo "        <option value='{$sensor['sensor_id']}'>{$sensor['name']}</option>" . PHP_EOL;
}
?>
      </select>
      <select class='btn btn-sm btn-outline-success mr-auto id-hours' data-key='hours'>
        <option value='0'>Period</option>
<?php
foreach (array(1 => '1 hour', 12 => '12 hours', 24 => '1 day', 24 * 7 => '7 days', 24 * 30 => '1 month', 24 * 365 => '12 months') as $hours => $period) {
  echo "        <option value='{$hours}'>{$period}</option>" . PHP_EOL;
}
?>
      </select>
    </nav>
    <script src='//code.jquery.com/jquery-3.2.1.min.js' integrity='sha384-xBuQ/xzmlsLoJpyjoggmTEz8OWUFM0/RC5BsqQBDX2v5cMvDHcMakNTNrHIW2I5f' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js' integrity='sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q' crossorigin='anonymous'></script>
    <script src='//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js' integrity='sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js' integrity='sha384-F13mJAeqdsVJS5kJv7MZ4PzYmJ+yXXZkt/gEnamJGTXZFzYgAcVtNg5wBDrRgLg9' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js' integrity='sha384-0saKbDOWtYAw5aP4czPUm6ByY5JojfQ9Co6wDgkuM7Zn+anp+4Rj92oGK8cbV91S' crossorigin='anonymous'></script>
    <script>
      $(document).ready(function() {
        var timer;
        var config = {
          type: 'line',
          data: {
            datasets: [{
              label: 'Temperature',
              backgroundColor: 'rgba(255, 0, 0, 0.3)',
              borderColor: 'rgb(255, 0, 0)',
              borderWidth: 1,
              pointRadius: 0,
              fill: false,
              yAxisID: 'temperature'
            }, {
              label: 'Humidity',
              backgroundColor: 'rgba(0, 0, 255, 0.3)',
              borderColor: 'rgb(0, 0, 255)',
              borderWidth: 1,
              pointRadius: 0,
              fill: false,
              yAxisID: 'humidity'
            }]
          },
          options: {
            legend: {position: 'bottom'},
            scales: {
              xAxes: [{display: true, type: 'time'}],
              yAxes: [{
                display: true,
                id: 'temperature',
                position: 'left',
                scaleLabel: {display: true, labelString: 'Temperature'}
              }, {
                display: true,
                id: 'humidity',
                position: 'right',
                scaleLabel: {display: true, labelString: 'Humidity'},
                gridLines: {display: false}
              }]
            }
          }
        };
        var chart = new Chart($('#chart'), config);

        function getMinMax() {
          $.getJSON('src/action.php', {"func": "getMinMax", "sensor_id": $('select.id-sensor-id').val(), "hours": $('select.id-hours').val()})
            .done(function(data) {
              if (data.success) {
                config.options.scales.yAxes[0].ticks = data.data.temperature;
                config.options.scales.yAxes[1].ticks = data.data.humidity;
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`getMinMax failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            })
            .always(function() {
            });
        }

        function updateChart() {
          $.getJSON('src/action.php', {"func": "getReadings", "sensor_id": $('select.id-sensor-id').val(), "hours": $('select.id-hours').val()})
            .done(function(data) {
              if (data.success) {
                config.data.datasets[0].data = data.data.temperatureData;
                config.data.datasets[1].data = data.data.humidityData;
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`getReadings failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            })
            .always(function() {
              chart.update();
            });

          timer = setTimeout(updateChart, 60 * 1000);
        };

        $.getJSON('src/action.php', {"func": "getSessionDetails"})
          .done(function(data) {
            if (data.success) {
              $('select.id-sensor-id').val(data.data.sensor_id && data.data.sensor_id || 0);
              $('select.id-hours').val(data.data.hours && data.data.hours || 0);
            }
          })
          .fail(function(jqxhr, textStatus, errorThrown) {
            console.log(`getSessionDetails failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
          })
          .always(function() {
            if ($('select.id-sensor-id').val() != 0 && $('select.id-hours').val() != 0) {
              getMinMax();
              updateChart();
            }
          });

        $('select.id-sensor-id, select.id-hours').change(function() {
          clearTimeout(timer);
          if ($('select.id-sensor-id').val() != 0 && $('select.id-hours').val() != 0) {
            getMinMax();
            updateChart();
          }
          $.getJSON('src/action.php', {"func": "putSessionDetail", "key": $(this).data('key'), "value": $(this).val()})
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`putSessionDetailo failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            });
        });

        $('button.id-nav').click(function() {
          location.href=$(this).data('href');
        });
      });
    </script>
  </body>
</html>
