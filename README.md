# WordPress ServerPilot.io API
A WordPress php library for interacting with the [ServerPilot API](https://github.com/ServerPilot/API).

More details can be found at [serverpilot.io](https://serverpilot.io).

## Example Usage

Below is an example of how to use the ServerPilot API to create a new application.
~~~~
$sp_api = new ServerPilotAPI( 'cid_AbCdEfGhIjKlM', 'a0B1c2D3e4F5g6H7i8J9k0L1m2N3o4P5q6R7s8T9u0V1w2X3' );

// Test authentication.
$response = $sp_api->list_servers();

if ( is_wp_error( $response ) ) {
  echo 'Error authenticating with provided credentials.';
  return;
}

// Authentication was successful.
$servers = $response->data;

// Check if we have any servers
if ( ! count( $servers ) ) {
  // If not, create one.
  $server_id = $sp_api->connect_new_server( 'wp-api-libraries' )->data->id;
}else{
  // You could perform more complicated stuff here (or skip to this step).
  $server_id = $servers[0]->id;
}

// Add an app (or website).
$app = $sp_api->create_app(
  'WordPress',
  server_id,
  'php7.0',
  array( 'wp-api-libraries.com', 'www.wp-api-libraries.com' )
  array(
    'site_title'     => 'My WordPress Site',
    'admin_user'     => 'Bradley M',
    'admin_password' => 'passtheword',
    'admin_email'    => 'bradley.moore@imforza.com'
  )
); // Note that the actual app details are stored under the ->data property.
~~~~
