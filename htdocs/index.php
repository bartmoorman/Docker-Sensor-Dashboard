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
    <nav class='navbar'>
      <button class='btn btn-sm btn-outline-success id-nav' data-href='<?php echo dirname($_SERVER['PHP_SELF']) ?>'>Home</button>
      <button class='btn btn-sm btn-outline-info ml-auto mr-2 id-nav' data-href='sensors.php'>Sensors</button>
      <button class='btn btn-sm btn-outline-info mr-2 id-nav' data-href='users.php'>Users</button>
      <button class='btn btn-sm btn-outline-info id-nav' data-href='events.php'>Events</button>
    </nav>
    <canvas id='chart'></canvas>
    <nav class='navbar text-center'>
      <select class='btn btn-sm btn-outline-success ml-auto mr-auto id-sensor-id'>
        <option value='0'>Sensor</option>
<?php
foreach ($dashboard->getSensors() as $sensor) {
  echo "        <option value='{$sensor['sensor_id']}'>{$sensor['name']}</option>";
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
        if (window.location.hash) {
          $('select.id-sensor-id').val(window.location.hash.replace('#',''));
        }

        function updateChart() {
          $.getJSON('src/action.php', {"func": "getReadings", "sensor_id": $('select.id-sensor-id').val()})
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

          timer = setTimeout(updateChart, 5000);
        };

        var timer;
        var config = {
          type: 'line',
          data: {
            datasets: [{
              label: 'Temperature',
              backgroundColor: 'rgba(255, 0, 0, 0.1)',
              borderColor: 'rgb(255, 0, 0)',
              borderWidth: 1,
              fill: false,
              yAxisID: 'temperature'
            }, {
              label: 'Humidity',
              backgroundColor: 'rgba(0, 0, 255, 0.1)',
              borderColor: 'rgb(0, 0, 255)',
              borderWidth: 1,
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

        $('select.id-sensor-id').change(function() {
          clearTimeout(timer);
          window.location.hash = $(this).val();
          updateChart();
        });

        $('button.id-nav').click(function() {
          location.href=$(this).data('href');
        });

        updateChart();
      });
    </script>
  </body>
</html>
