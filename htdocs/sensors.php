<?php
require_once('inc/dashboard.class.php');
$dashboard = new Dashboard(true, true, true, false);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title><?php echo $dashboard->appName ?> - Sensors</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
<?php require_once('include.css'); ?>
  </head>
  <body>
<?php require_once('header.php'); ?>
    <div class='container'>
      <table class='table table-striped table-hover table-sm'>
        <thead>
          <tr>
            <th><button type='button' class='btn btn-sm btn-outline-success id-add'>Add</button></th>
            <th>Sensor ID</th>
            <th>Sensor Name</th>
            <th>Min. Temperature</th>
            <th>Max. Temperature</th>
            <th>Min. Humidity</th>
            <th>Max. Humidity</th>
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
  echo "            <td>{$sensor['min_temperature']}{$dashboard->temperature['key']}</td>" . PHP_EOL;
  echo "            <td>{$sensor['max_temperature']}{$dashboard->temperature['key']}</td>" . PHP_EOL;
  echo "            <td>{$sensor['min_humidity']}%</td>" . PHP_EOL;
  echo "            <td>{$sensor['max_humidity']}%</td>" . PHP_EOL;
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
                  <label>Sensor Name <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='name' type='text' name='name' required>
                </div>
                <div class='form-group col'>
                  <label>Access Key <sup class='text-danger id-required' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control id-key' id='key' type='text' name='key' minlength='16' maxlength='16' pattern='[A-Za-z0-9]{16}' required>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Min. Temperature</label>
                  <div class='input-group'>
                    <input class='form-control' id='min_temperature' type='number' name='min_temperature' min='<?php echo $dashboard->temperature['min'] ?>' max='<?php echo $dashboard->temperature['max'] ?>' step='0.01'>
                    <div class='input-group-append'>
                      <span class='input-group-text'><?php echo $dashboard->temperature['key'] ?></span>
                    </div>
                  </div>
                </div>
                <div class='form-group col'>
                  <label>Max. Temperature</label>
                  <div class='input-group'>
                    <input class='form-control' id='max_temperature' type='number' name='max_temperature' min='<?php echo $dashboard->temperature['min'] ?>' max='<?php echo $dashboard->temperature['max'] ?>' step='0.01'>
                    <div class='input-group-append'>
                      <span class='input-group-text'><?php echo $dashboard->temperature['key'] ?></span>
                    </div>
                  </div>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Min. Humidity</label>
                  <div class='input-group'>
                    <input class='form-control' id='min_humidity' type='number' name='min_humidity' min='0' max='100' step='0.1'>
                    <div class='input-group-append'>
                      <span class='input-group-text'>%</span>
                    </div>
                  </div>
                </div>
                <div class='form-group col'>
                  <label>Max. Humidity</label>
                  <div class='input-group'>
                    <input class='form-control' id='max_humidity' type='number' name='max_humidity' min='0' max='100' step='0.1'>
                    <div class='input-group-append'>
                      <span class='input-group-text'>%</span>
                    </div>
                  </div>
                </div>
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
<?php require_once('include.js'); ?>
    <script>
      $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        $('button.id-add').click(function() {
          $('h5.modal-title').text('Add Sensor');
          $('form').removeData('sensor_id').data('func', 'createSensor').trigger('reset');
          $('sup.id-required').addClass('d-none');
          $('input.id-key').prop('required', false).attr('placeholder', 'Will be generated if empty');
          $('button.id-modify').addClass('d-none').removeData('sensor_id');
          $('button.id-submit').removeClass('btn-info').addClass('btn-success').text('Add');
          $('div.id-modal').modal('toggle');
        });

        $('button.id-details').click(function() {
          $('h5.modal-title').text('Sensor Details');
          $('form').removeData('sensor_id').data('func', 'updateSensor').trigger('reset');
          $('sup.id-required').removeClass('d-none');
          $('input.id-key').removeAttr('placeholder').prop('required', true);
          $('button.id-modify').removeClass('d-none').removeData('sensor_id');
          $('button.id-submit').removeClass('btn-success').addClass('btn-info').text('Save');
          $.get('src/action.php', {"func": "getObjectDetails", "type": "sensor", "value": $(this).data('sensor_id')})
            .done(function(data) {
              if (data.success) {
                sensor = data.data;
                $('form').data('sensor_id', sensor.sensor_id);
                $('#name').val(sensor.name);
                $('#key').val(sensor.key);
                $('#min_temperature').val(sensor.min_temperature);
                $('#max_temperature').val(sensor.max_temperature);
                $('#min_humidity').val(sensor.min_humidity);
                $('#max_humidity').val(sensor.max_humidity);
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
                console.log(`modifyObject failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
              });
          }
        });

        $('form').submit(function(e) {
          e.preventDefault();
          $.post('src/action.php', {"func": $(this).data('func'), "sensor_id": $(this).data('sensor_id'), "name": $('#name').val(), "key": $('#key').val(), "min_temperature": $('#min_temperature').val(), "max_temperature": $('#max_temperature').val(), "min_humidity": $('#min_humidity').val(), "max_humidity": $('#max_humidity').val()})
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
