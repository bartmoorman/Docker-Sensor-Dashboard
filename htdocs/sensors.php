<?php
require_once('inc/dashboard.class.php');
$dashboard = new Dashboard();
$currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title>Dashboard - Sensors</title>
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
            <th>Name</th>
            <th>Token</th>
          </tr>
        </thead>
        <tbody>
<?php
foreach ($dashboard->getSensors() as $sensor) {
  $tableClass = $sensor['disabled'] ? 'text-danger' : 'table-default';
  echo "          <tr class='{$tableClass}'>" . PHP_EOL;
  if ($sensor['disabled']) {
    echo "            <td><button type='button' class='btn btn-sm btn-outline-warning id-modify' data-action='enable' data-sensor_id='{$sensor['sensor_id']}'>Enable</button></td>" . PHP_EOL;
  } else {
    echo "            <td><button type='button' class='btn btn-sm btn-outline-info id-edit' data-sensor_id='{$sensor['sensor_id']}'>Edit</button></td>" . PHP_EOL;
  }
  echo "            <td>{$sensor['name']}</td>" . PHP_EOL;
  echo "            <td>{$sensor['token']}</td>" . PHP_EOL;
  echo "          </tr>" . PHP_EOL;
}
?>
        </tbody>
      </table>
    </div>
    <nav>
      <ul class='pagination justify-content-center'>
<?php
?>
      </ul>
    </nav>
    <div class='modal fade id-modal'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <form>
            <div class='modal-header'>
              <h5 class='modal-title'></h5>
            </div>
            <div class='modal-body'>
              <div class='form-row justify-content-center'>
                <div class='col-auto'>
                  <input class='form-control' id='name' type='text' name='name' placeholder='Sensor Name' required>
                  <input class='form-control' id='token' type='text' name='token' placeholder='Access Token' minlength='10' required>
                </div>
              </div>
            </div>
            <div class='modal-footer'>
              <button type='button' class='btn btn-outline-warning id-modify id-volatile' data-action='disable'>Disable</button>
              <button type='button' class='btn btn-outline-danger mr-auto id-modify id-volatile' data-action='delete'>Delete</button>
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
    <script src='//cdnjs.cloudflare.com/ajax/libs/URI.js/1.19.1/URI.min.js' integrity='sha384-p+MfR+v7kwvUVHmsjMiBK3x45fpY3zmJ5X2FICvDqhVP5YJHjfbFDc9f5U1Eba88' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/URI.js/1.19.1/jquery.URI.min.js' integrity='sha384-zdBrwYVf1Tu1JfO1GKzBAmCOduwha4jbqoCt2886bKrIFyAslJauxsn9JUKj6col' crossorigin='anonymous'></script>
    <script>
      $(document).ready(function() {
        $('button.id-add').click(function() {
          $('h5.modal-title').text('Add Sensor');
          $('form').removeData('sensor_id').data('func', 'createSensor').trigger('reset');
          $('button.id-modify.id-volatile').addClass('d-none').removeData('sensor_id');
          $('button.id-submit').removeClass('btn-info').addClass('btn-success').text('Add');
          $('div.id-modal').modal('toggle');
        });

        $('button.id-edit').click(function() {
          $('h5.modal-title').text('Edit Sensor');
          $('form').removeData('sensor_id').data('func', 'updateSensor').trigger('reset');
          $('button.id-modify.id-volatile').removeClass('d-none').removeData('sensor_id');
          $('button.id-submit').removeClass('btn-success').addClass('btn-info').text('Save');
          $.getJSON('src/action.php', {"func": "sensorDetails", "sensor_id": $(this).data('sensor_id')})
            .done(function(data) {
              if (data.success) {
                sensor = data.data;
                $('form').data('sensor_id', sensor.sensor_id);
                $('#name').val(sensor.name);
                $('#token').val(sensor.token);
                $('button.id-modify.id-volatile').data('sensor_id', sensor.sensor_id);
                $('div.id-modal').modal('toggle');
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`sensorDetails failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            });
        });

       $('button.id-modify').click(function() {
          if (confirm(`Want to ${$(this).data('action').toUpperCase()} sensor ${$(this).data('sensor_id')}?`)) {
            $.getJSON('src/action.php', {"func": "modifySensor", "action": $(this).data('action'), "sensor_id": $(this).data('sensor_id')})
              .done(function(data) {
                if (data.success) {
                  location.reload();
                }
              })
              .fail(function(jqxhr, textStatus, errorThrown) {
                console.log(`removeUser failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
              });
          }
        });

        $('form').submit(function(e) {
          e.preventDefault();
          $.getJSON('src/action.php', {"func": $(this).data('func'), "sensor_id": $(this).data('sensor_id'), "name": $('#name').val(), "token": $('#token').val()})
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

        $('a.id-page').click(function() {
          location.href=URI().removeQuery('page').addQuery('page', $(this).data('page'));
        });
      });
    </script>
  </body>
</html>
