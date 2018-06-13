<?php
require_once('inc/dashboard.class.php');
$dashboard = new Dashboard(true, true, true, false);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title>Sensor Dashboard - Sensors</title>
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
    <div class='container'>
      <table class='table table-striped table-hover table-sm'>
        <thead>
          <tr>
            <th><button type='button' class='btn btn-sm btn-outline-success id-add'>Add</button></th>
            <th>Sensor ID</th>
            <th>Sensor Name</th>
            <th>Min. Temp. (<?php echo $dashboard->temperature['key'] ?>)</th>
            <th>Max. Temp. (<?php echo $dashboard->temperature['key'] ?>)</th>
            <th>Min. Hum. (%)</th>
            <th>Max. Hum. (%)</th>
          </tr>
        </thead>
        <tbody>
<?php
foreach ($dashboard->getObjects('sensors') as $sensor) {
  $tableClass = $sensor['disabled'] ? 'text-warning' : 'table-default';
  echo "          <tr class='{$tableClass}'>" . PHP_EOL;
  echo "            <td><button type='button' class='btn btn-sm btn-outline-info id-details' data-sensor_id='{$sensor['sensor_id']}'>Details</button></td>" . PHP_EOL;
  echo "            <td>{$sensor['sensor_id']}</td>" . PHP_EOL;
  echo "            <td>{$sensor['name']}</td>" . PHP_EOL;
  echo "            <td>{$sensor['min_temperature']}</td>" . PHP_EOL;
  echo "            <td>{$sensor['max_temperature']}</td>" . PHP_EOL;
  echo "            <td>{$sensor['min_humidity']}</td>" . PHP_EOL;
  echo "            <td>{$sensor['max_humidity']}</td>" . PHP_EOL;
  echo "          </tr>" . PHP_EOL;
}
?>
        </tbody>
      </table>
    </div>
    <div class='modal fade id-modal'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <form>
            <div class='modal-header'>
              <h5 class='modal-title'></h5>
            </div>
            <div class='modal-body'>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Sensor Name <sup class='text-danger'>*</sup></label>
                  <input class='form-control' id='name' type='text' name='name' required>
                </div>
                <div class='form-group col'>
                  <label>Access Token <sup class='text-danger id-required'>*</sup></label>
                  <input class='form-control id-token' id='token' type='text' name='token' minlength='16' maxlength='16' pattern='[a-z0-9]{16}' required>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Min. Temp. (<?php echo $dashboard->temperature['key'] ?>)</label>
                  <input class='form-control' id='min_temperature' type='number' name='min_temperature' min='<?php echo $dashboard->temperature['min'] ?>' max='<?php echo $dashboard->temperature['max'] ?>' step='0.01'>
                </div>
                <div class='form-group col'>
                  <label>Max. Temp. (<?php echo $dashboard->temperature['key'] ?>)</label>
                  <input class='form-control' id='max_temperature' type='number' name='max_temperature' min='<?php echo $dashboard->temperature['min'] ?>' max='<?php echo $dashboard->temperature['max'] ?>' step='0.01'>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Min. Hum. (%)</label>
                  <input class='form-control' id='min_humidity' type='number' name='min_humidity' min='0' max='100' step='0.1'>
                </div>
                <div class='form-group col'>
                  <label>Max. Hum. (%)</label>
                  <input class='form-control' id='max_humidity' type='number' name='max_humidity' min='0' max='100' step='0.1'>
                </div>
              </div>
              <div class='form-check id-notified'>
                <input class='form-check-input' id='notified_min_temperature' type='checkbox' disabled>
                <label class='form-check-label'>Notified Min. Temperature</label>
              </div>
              <div class='form-check id-notified'>
                <input class='form-check-input' id='notified_max_temperature' type='checkbox' disabled>
                <label class='form-check-label'>Notified Max. Temperature</label>
              </div>
              <div class='form-check id-notified'>
                <input class='form-check-input' id='notified_min_humidity' type='checkbox' disabled>
                <label class='form-check-label'>Notified Min. Humidity</label>
              </div>
              <div class='form-check id-notified'>
                <input class='form-check-input' id='notified_max_humidity' type='checkbox' disabled>
                <label class='form-check-label'>Notified Max. Humidity</label>
              </div>
            </div>
            <div class='modal-footer'>
              <button type='button' class='btn btn-outline-warning id-modify id-volatile'></button>
              <button type='button' class='btn btn-outline-danger mr-auto id-modify' data-action='delete'>Delete</button>
              <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
              <button type='submit' class='btn id-submit'></button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src='//code.jquery.com/jquery-3.2.1.min.js' integrity='sha384-xBuQ/xzmlsLoJpyjoggmTEz8OWUFM0/RC5BsqQBDX2v5cMvDHcMakNTNrHIW2I5f' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js' integrity='sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q' crossorigin='anonymous'></script>
    <script src='//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js' integrity='sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl' crossorigin='anonymous'></script>
    <script>
      $(document).ready(function() {
        $('button.id-add').click(function() {
          $('h5.modal-title').text('Add Sensor');
          $('form').removeData('sensor_id').data('func', 'createSensor').trigger('reset');
          $('sup.id-required').addClass('d-none');
          $('input.id-token').prop('required', false).prop('disabled', true).val('will be generated');
          $('div.id-notified').addClass('d-none');
          $('button.id-modify').addClass('d-none').removeData('sensor_id');
          $('button.id-submit').removeClass('btn-info').addClass('btn-success').text('Add');
          $('div.id-modal').modal('toggle');
        });

        $('button.id-details').click(function() {
          $('h5.modal-title').text('Sensor Details');
          $('form').removeData('sensor_id').data('func', 'updateSensor').trigger('reset');
          $('sup.id-required').removeClass('d-none');
          $('input.id-token').prop('disabled', false).prop('required', true);
          $('div.id-notified').removeClass('d-none');
          $('button.id-modify').removeClass('d-none').removeData('sensor_id');
          $('button.id-submit').removeClass('btn-success').addClass('btn-info').text('Save');
          $.get('src/action.php', {"func": "getObjectDetails", "type": "sensor", "value": $(this).data('sensor_id')})
            .done(function(data) {
              if (data.success) {
                sensor = data.data;
                $('form').data('sensor_id', sensor.sensor_id);
                $('#name').val(sensor.name);
                $('#token').val(sensor.token);
                $('#min_temperature').val(sensor.min_temperature);
                $('#max_temperature').val(sensor.max_temperature);
                $('#min_humidity').val(sensor.min_humidity);
                $('#max_humidity').val(sensor.max_humidity);
                $('#notified_min_temperature').prop('checked', sensor.notified_min_temperature);
                $('#notified_max_temperature').prop('checked', sensor.notified_max_temperature);
                $('#notified_min_humidity').prop('checked', sensor.notified_min_humidity);
                $('#notified_max_humidity').prop('checked', sensor.notified_max_humidity);
                $('button.id-modify.id-volatile').data('action', sensor.disabled ? 'enable' : 'disable').text(sensor.disabled ? 'Enable' : 'Disable');
                $('button.id-modify').data('sensor_id', sensor.sensor_id);
                $('div.id-modal').modal('toggle');
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`getObjectDetails failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            });
        });

       $('button.id-modify').click(function() {
          if (confirm(`Want to ${$(this).data('action').toUpperCase()} sensor ${$(this).data('sensor_id')}?`)) {
            $.get('src/action.php', {"func": "modifyObject", "action": $(this).data('action'), "type": "sensor_id", "value": $(this).data('sensor_id')})
              .done(function(data) {
                if (data.success) {
                  location.reload();
                }
              })
              .fail(function(jqxhr, textStatus, errorThrown) {
                console.log(`modifySensor failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
              });
          }
        });

        $('form').submit(function(e) {
          e.preventDefault();
          $.post('src/action.php', {"func": $(this).data('func'), "sensor_id": $(this).data('sensor_id'), "name": $('#name').val(), "token": $('#token').val(), "min_temperature": $('#min_temperature').val(), "max_temperature": $('#max_temperature').val(), "min_humidity": $('#min_humidity').val(), "max_humidity": $('#max_humidity').val()})
            .done(function(data) {
              if (data.success) {
                location.reload();
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`${$(this).data('func')} failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            });
        });

        $('button.id-nav').click(function() {
          location.href=$(this).data('href');
        });
      });
    </script>
  </body>
</html>
