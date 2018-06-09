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
      <select class='btn btn-sm btn-outline-success ml-auto mr-2 id-sensor_id' data-key='sensor_id'>
        <option value='0'>Sensor</option>
<?php
foreach ($dashboard->getObjects('sensors') as $sensor) {
  echo "        <option value='{$sensor['sensor_id']}'>{$sensor['name']}</option>" . PHP_EOL;
}
?>
      </select>
      <select class='btn btn-sm btn-outline-success mr-auto id-hours' data-key='hours'>
        <option value='0'>Period</option>
<?php
$periods = array(
  1 => '1 hour',
  3 => '3 hours',
  6 => '6 hours',
  12 => '12 hours',
  24 => '24 hours',
  24 * 7 => '7 days',
  24 * 14 => '14 days',
  24 * 30 => '30 days',
  24 * 90 => '90 days',
  24 * 180 => '180 days',
  24 * 365 => '1 year'
);
foreach ($periods as $hours => $period) {
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
        var cookies = ['sensor_id', 'hours'];
        var timer;
        var chart;
        var config = {
          type: 'line',
          data: {
            datasets: [{
              label: 'Temperature',
              backgroundColor: 'rgba(255, 0, 0, 0.3)',
              borderColor: 'rgb(255, 0, 0)',
              borderWidth: 1,
              pointRadius: 2,
              fill: false,
              yAxisID: 'temperature'
            }, {
              label: 'Humidity',
              backgroundColor: 'rgba(0, 0, 255, 0.3)',
              borderColor: 'rgb(0, 0, 255)',
              borderWidth: 1,
              pointRadius: 2,
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
                scaleLabel: {display: true, labelString: 'Temperature (<?php echo $dashboard->temperature['key'] ?>)'}
              }, {
                display: true,
                id: 'humidity',
                position: 'right',
                scaleLabel: {display: true, labelString: 'Humidity (%)'},
                gridLines: {display: false}
              }]
            }
          }
        };

        function getReadingsMinMax() {
          $.getJSON('src/action.php', {"func": "getReadingsMinMax", "sensor_id": $('select.id-sensor_id').val(), "hours": $('select.id-hours').val()})
            .done(function(data) {
              if (data.success) {
                config.options.scales.yAxes[0].ticks = data.data.temperature;
                config.options.scales.yAxes[1].ticks = data.data.humidity;
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`getReadingsMinMax failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            })
            .always(function() {
              chart = new Chart($('#chart'), config);
              getReadings();
            });
        }

        function getReadings() {
          $.getJSON('src/action.php', {"func": "getReadings", "sensor_id": $('select.id-sensor_id').val(), "hours": $('select.id-hours').val()})
            .done(function(data) {
              if (data.success) {
                config.data.datasets[0].data = data.data.temperatureData;
                config.data.datasets[1].data = data.data.humidityData;
                chart.update();
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`getReadings failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            })
            .always(function() {
              timer = setTimeout(getReadings, 30 * 1000);
            });
        };

        $.each(document.cookie.split(';'), function() {
          var [key, value] = $.trim(this).split('=');
          if (cookies.includes(key)) {
            $(`select.id-${key}`).val(value);
          }
        });

        if ($('select.id-sensor_id').val() != 0 && $('select.id-hours').val() != 0) {
          getReadingsMinMax();
        } else {
          chart = new Chart($('#chart'), config);
        }

        $('select.id-sensor_id, select.id-hours').change(function() {
          clearTimeout(timer);
          if ($('select.id-sensor_id').val() != 0 && $('select.id-hours').val() != 0) {
            getReadingsMinMax();
          }
          document.cookie = `${$(this).data('key')}=${$(this).val()}`;
        });

        $('button.id-nav').click(function() {
          location.href=$(this).data('href');
        });
      });
    </script>
  </body>
</html>
