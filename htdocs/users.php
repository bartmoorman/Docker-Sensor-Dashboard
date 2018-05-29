<?php
require_once('inc/dashboard.class.php');
$dashboard = new Dashboard(true, true, true, false);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title>Dashboard - Users</title>
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
            <th>Pin Code</th>
            <th>User Name</th>
            <th>Email</th>
            <th>Pushover User</th>
            <th>Pushover Token</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
<?php
foreach ($dashboard->getUsers() as $user) {
  $user_name = !empty($user['last_name']) ? sprintf('%2$s, %1$s', $user['first_name'], $user['last_name']) : $user['first_name'];
  $tableClass = $user['disabled'] ? 'text-danger' : 'table-default';
  echo "          <tr class='{$tableClass}'>" . PHP_EOL;
  if ($user['disabled']) {
    echo "            <td><button type='button' class='btn btn-sm btn-outline-warning id-modify' data-action='enable' data-user_id='{$user['user_id']}'>Enable</button></td>" . PHP_EOL;
  } else {
    echo "            <td><button type='button' class='btn btn-sm btn-outline-info id-edit' data-user_id='{$user['user_id']}'>Edit</button></td>" . PHP_EOL;
  }
  echo "            <td>{$user['pincode']}</td>" . PHP_EOL;
  echo "            <td>{$user_name}</td>" . PHP_EOL;
  echo "            <td>{$user['email']}</td>" . PHP_EOL;
  echo "            <td>{$user['pushover_user']}</td>" . PHP_EOL;
  echo "            <td>{$user['pushover_token']}</td>" . PHP_EOL;
  echo "            <td>{$user['role']}</td>" . PHP_EOL;
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
              <div class='form-row justify-content-center'>
                <div class='col-auto'>
                  <input class='form-control' id='pincode' type='tel' name='pincode' placeholder='Numeric Pin Code' minlegth='6' maxlength='6' pattern='[0-9]{6}' required>
                  <input class='form-control' id='first_name' type='text' name='first_name' placeholder='First Name' required>
                  <input class='form-control' id='last_name' type='text' name='last_name' placeholder='Last Name (optional)'>
                  <input class='form-control' id='email' type='email' name='email' placeholder='Email (optional)'>
                  <input class='form-control' id='pushover_user' type='text' name='pushover_user' placeholder='Pushover User (optional)' minlegth='30' maxlength='30' pattern='[a-z0-9]{30}'>
                  <input class='form-control' id='pushover_token' type='text' name='pushover_token' placeholder='Pushover Token (optional)' minlegth='30' maxlength='30' pattern='[a-z0-9]{30}'>
                  <select class='form-control' id='role' name='role' required>
                    <option disabled>Role</option>
                    <option value='user'>user</option>
                    <option value='admin'>admin</option>
                  </select>
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
    <script>
      $(document).ready(function() {
        $('button.id-add').click(function() {
          $('h5.modal-title').text('Add User');
          $('form').removeData('user_id').data('func', 'createUser').trigger('reset');
          $('button.id-modify.id-volatile').addClass('d-none').removeData('user_id');
          $('button.id-submit').removeClass('btn-info').addClass('btn-success').text('Add');
          $('div.id-modal').modal('toggle');
        });

        $('button.id-edit').click(function() {
          $('h5.modal-title').text('Edit User');
          $('form').removeData('user_id').data('func', 'updateUser').trigger('reset');
          $('button.id-modify.id-volatile').removeClass('d-none').removeData('user_id');
          $('button.id-submit').removeClass('btn-success').addClass('btn-info').text('Save');
          $.getJSON('src/action.php', {"func": "userDetails", "user_id": $(this).data('user_id')})
            .done(function(data) {
              if (data.success) {
                user = data.data;
                $('form').data('user_id', user.user_id);
                $('#pincode').val(user.pincode);
                $('#first_name').val(user.first_name);
                $('#last_name').val(user.last_name);
                $('#email').val(user.email);
                $('#pushover_user').val(user.pushover_user);
                $('#pushover_token').val(user.pushover_token);
                $('#role').val(user.role);
                $('button.id-modify.id-volatile').data('user_id', user.user_id);
                $('div.id-modal').modal('toggle');
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`userDetails failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            });
        });

        $('button.id-modify').click(function() {
          if (confirm(`Want to ${$(this).data('action').toUpperCase()} user ${$(this).data('user_id')}?`)) {
            $.getJSON('src/action.php', {"func": "modifyUser", "action": $(this).data('action'), "user_id": $(this).data('user_id')})
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
          $.getJSON('src/action.php', {"func": $(this).data('func'), "user_id": $(this).data('user_id'), "pincode": $('#pincode').val(), "first_name": $('#first_name').val(), "last_name": $('#last_name').val(), "email": $('#email').val(), "pushover_user": $('#pushover_user').val(), "pushover_token": $('#pushover_token').val(), "role": $('#role').val()})
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
