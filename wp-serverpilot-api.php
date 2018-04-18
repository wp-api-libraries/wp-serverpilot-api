<?php
/**
 * WP-ServerPilot-API (https://github.com/ServerPilot/API)
 *
 * @package WP-ServerPilotr-API
 */

/**
 * Plugin Name: WP ServerPilot API
 * Plugin URI: https://github.com/wp-api-libraries/wp-serverpilot-api
 * Description: Perform API requests to ServerPilot in WordPress.
 * Author: WP API Libraries
 * Version: 1.0.0
 * Author URI: https://wp-api-libraries.com
 * GitHub Plugin URI: https://github.com/wp-api-libraries/wp-serverpilot-api
 * GitHub Branch: master
 */

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Confirm that WpServerPilotAPIBase is included. */
if ( ! class_exists( 'WpServerPilotAPIBase' ) ) {
	require_once 'wp-libraries-base.php';
}

/* Check if class exists. */
if ( ! class_exists( 'ServerPilotAPI' ) ) {

	/**
	 * ServerPilot API Class.
	 */
	class ServerPilotAPI extends WpServerPilotAPIBase {

		/**
		 * BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://api.serverpilot.io/v1/';

		private $client_id = '';
		private $api_key = '';

		protected $is_debug = false;

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct( $client_id, $api_key, $debug = false ) {
			$this->client_id = $client_id;
			$this->api_key   = $api_key;
			$this->is_debug  = $debug;
		}

		protected function set_headers(){
			$this->args['headers'] = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->api_key )
			);
		}

		private function run( $route, $args = array(), $method = 'GET' ){
			return $this->build_request( $route, $args, $method )->fetch();
		}

		/* SERVERS. */

		/**
		 * list_servers function.
		 *
		 * @access public
		 * @return void
		 */
		public function list_servers() {
			return $this->run( 'servers' );
		}

		/**
		 * Tells ServerPilot that you plan to connect a new server. Returns ID and APIKEY.
		 *
		 * Then:
		 * NOTE: I'm not sure $SERVER_ID == $SERVERID, same for $SERVER_APIKEY
		 * 		export SERVERID=$SERVER_ID
		 * 		export SERVERAPIKEY=$SERVER_APIKEY
		 * 		sudo apt-get update && sudo apt-get -y install wget ca-certificates && \
		 * 		sudo wget -nv -O serverpilot-installer https://download.serverpilot.io/serverpilot-installer && \
		 * 		sudo sh serverpilot-installer \
		 * 		--server-id=$SERVERID \
		 * 		--server-apikey=$SERVERAPIKEY
		 *
		 * @param  string $name The nickname of the Server. Length must be between 1 and
		 *                      255 characters. Characters can be of lowercase ascii
		 *                      letters, digits, a period, or a dash
		 *                      ('abcdefghijklmnopqrstuvwxyz0123456789-'), but must start
		 *                      with a lowercase ascii letter and end with either a
		 *                      lowercase ascii letter or digit. www.store2 is a valid
		 *                      name, while .org.company nor www.blog- are.
		 * @return object       The created server. Note that this includes the one-time
		 *                      available API Key for the server.
		 */
		public function connect_new_server( string $name ) {
			return $this->run( 'servers', array( 'name' => $name ), 'POST' );
		}

		/**
		 * Show the details for an existing server.
		 *
		 * @param  string The ID of the server.
		 * @return object The server.
		 */
		public function show_existing_server( string $id ) {
			return $this->run( 'servers/'.$id );
		}

		/**
		 * Update a server.
		 *
		 * @param  string $id   The server ID.
		 * @param  array  $args An array of boolean arguments. Accepts:
		 *                        firewall
		 *                          Describes the "enabled" state of the server's
		 *                          firewall. false means the firewall is not enabled.
		 *                        autoupdates
		 *                          Describes the "enabled" state of automatic system
		 *                          updates. false means automatic system updates
		 *                          are not enabled.
		 *                        deny_unknown_domains
		 *                          Whether requests for domains not associated with
		 *                          any app are denied (true) or are sent to the
		 *                          default app (false).
		 * @return object       The updated server.
		 */
		public function update_server( string $id, $args = array() ) {
			$args = array_intersect_key( $args, array(
				'firewall'             => false,
				'autoupdates'          => false,
				'deny_unknown_domains' => false
			));

			return $this->run( 'servers/'.$id, $args, 'POST' );
		}

		/**
		 * Delete a server
		 *
		 * @param  string The ID of the server.
		 * @return object Null.
		 */
		public function delete_server( string $id ) {
			return $this->run( 'servers/'.$id, array(), 'DELETE' );
		}

		/* SYSTEM USERS. */

		/**
		 * Returns a list of serverpilot System Users
		 *
		 * @return array A list of system user objects (under response->data).
		 */
		public function list_system_users() {
			return $this->run( 'sysusers' );
		}

		/**
		 * Create a system user.
		 *
		 * Note: this requires at least the coach plan.
		 *
		 * @param  string $id   The ID of the server.
		 * @param  string $name The name of the System User. Length must be between 3
		 *                      and 32 characters. Characters can be of lowercase ascii
		 *                      letters, digits, or a dash
		 *                      ('abcdefghijklmnopqrstuvwxyz0123456789-'), but must
		 *                      start with a lowercase ascii letter. user-32 is a valid
		 *                      name, while 3po is not.
		 * @param  string $pass (Default: null) An optional password for the System
		 *                      User. If user has no password, they will not be able to
		 *                      log in with a password. No leading or trailing
		 *                      whitespace is allowed and the password must be at least
		 *                      8 and no more than 200 characters long.
		 * @return [type]       [description]
		 */
		public function create_system_user( string $id, string $name, string $pass = '' ) {
			$args = array(
				'serverid' => $id,
				'name'     => $name
			);

			if( $pass !== '' ){
				$args['password'] = $pass;
			}

			return $this->run( 'sysusers', $args, 'POST' );
		}

		/**
		 * Retrieve an existing system user.
		 *
		 * @param  string $id The system user's ID.
		 * @return object     An object containing the user's data.
		 */
		public function show_system_user( string $id ) {
			return $this->run( 'sysusers/'.$id );
		}

		/**
		 * Update a system user.
		 *
		 * Only allows you to update their password.
		 *
		 * @param  string $id       The system user's ID.
		 * @param  string $password The new password of the System User. If user has no
		 *                          password, they will not be able to log in with a
		 *                          password. No leading or trailing whitespace is
		 *                          allowed and the password must be at least 8 and
		 *                          no more than 200 characters long.
		 * @return object           The system user object associated.
		 */
		public function update_system_user( string $id, string $password ) {
			return $this->run( 'sysusers/'.$id, array( 'password' => $password ), 'POST' );
		}

		/**
		 * Delete a system user.
		 *
		 * WARNING: Deleting a system user will also delete all apps and databases
		 * associated with them.
		 *
		 * @param  string $id The ID.
		 * @return object     An object with the actionID associated.
		 */
		public function delete_system_user( string $id ) {
			return $this->run( 'sysusers/'.$id, array(), 'DELETE' );
		}

		/* APPS. */

		/**
		 * List all applications.
		 *
		 * @return object An object containing the data as an array of apps.
		 */
		public function list_apps() {
			return $this->run( 'apps' );
		}

		/**
		 * Create an application (website).
		 *
		 * Eg: creating an app without wordpress
		 *   create_app(
		 *     'gallery',
		 *     'abcd1234',
		 *     'php7.0',
		 *     array( 'example.com', 'www.example.com' )
		 *   )
		 *
		 * Eg: creating an app with wordpress
		 *   create_app(
		 *     'wordpress',
		 *     'abcd1234',
		 *     'php7.0',
		 *     array( 'example.com', 'www.example.com' )
		 *     array(
		 *       'site_title'     => 'My WordPress Site',
		 *       'admin_user'     => 'Bradley M',
		 *       'admin_password' => 'passtheword',
		 *       'admin_email'    => 'bradley.moore@imforza.com'
		 *     )
		 *   )
		 *
		 * @param  mixed  $name      Must be string-able. The nickname of the App.
		 *                           Length must be between 3 and 30 characters.
		 *                           Characters can be of lowercase ascii letters
		 *                           and digits.
		 * @param  string $sysuserid The System User that will "own" this App. Since
		 *                           every System User is specific to a Server, this
		 *                           implicitly determines on which Server the App
		 *                           will be created.
		 * @param  string $runtime   The PHP runtime for an App. Choose from php5.4,
		 *                           php5.5, php5.6, php7.0, php7.1, or php7.2.
		 * @param  array  $domains   An array of domains that will be used in the
		 *                           webserver's configuration. If you set your app's
		 *                           domain name to example.com, Nginx and Apache will
		 *                           be configured to listen for both example.com and
		 *                           www.example.com. Note: The complete list of domains
		 *                           must be included in every update to this field.
		 * @param  array  $wordpress If present, installs WordPress on the App. Value
		 *                           is a JSON object containing keys site_title,
		 *                           admin_user, admin_password, and admin_email, each
		 *                           with values that are strings. The admin_password
		 *                           value must be at least 8 and no more than 200
		 *                           characters long.
		 * @return [type]            [description]
		 */
		public function create_app( $name, string $sysuserid, string $runtime, $domains = array(), $wordpress = array() ) {
			$args = array(
				'name'      => "$name",
				'sysuserid' => $sysuserid,
				'runtime'   => $runtime,
				'domains'   => $domains
			);

			if ( ! empty( $wordpress ) ) {
				$args['wordpress'] = $wordpress;
			}

			return $this->run( 'apps', $args, 'POST' );
		}

		/**
		 * Get the details for an app.
		 *
		 * Note: The value of auto in the ssl object indicates whether AutoSSL is
		 * currently enabled for the app. In the example above, since auto is false
		 * and there is an SSL certificate, the SSL certificate is a custom
		 * certificate rather than an AutoSSL certificate.
		 *
		 * To know whether AutoSSL is available for an app, use the autossl key. The
		 * autossl key will only exist when the account is on a paid plan. If the
		 * value of available is true, there will also be a domains key with a list
		 * of the domains in the currently available AutoSSL certificate. Note that
		 * the autossl key is only available through this app details API, not through
		 * the apps list API that lists all apps of the account.
		 *
		 * @param  string $id The app's ID.
		 * @return object     The app and its data.
		 */
		public function show_app( string $id ) {
			return $this->run( 'apps/'.$id );
		}

		/**
		 * Update an app.
		 *
		 * @param  string $id      The app's ID.
		 * @param  string $runtime (Default: null) The PHP runtime for an App. Choose
		 *                         from php5.4, php5.5, php5.6, php7.0, php7.1, or php7.2.
		 * @param  array  $domains (Default: null) An array of domains that will be
		 *                         used in the webserver's configuration. If you set
		 *                         your app's domain name to example.com, Nginx and
		 *                         Apache will be configured to listen for both
		 *                         example.com and www.example.com. Note: The complete
		 *                         list of domains must be included in every update
		 *                         to this field.
		 * @return object          The updated object.
		 */
		public function update_app( string $id, string $runtime = null, array $domains = null ) {
			$args = array();

			if ( null !== $runtime ) {
				$args['runtime'] = $runtime;
			}

			if ( null !== $domains ) {
				$args['domains'] = $domains;
			}

			return $this->run( 'apps/'.$id, $args, 'POST' );
		}

		/**
		 * Delete an app.
		 *
		 * @param  string $id The app's ID.
		 * @return object     A confirmation object with an actionid.
		 */
		public function delete_app( string $id ) {
			return $this->run( 'apps/'.$id, array(), 'DELETE' );
		}

		/**
		 * Add a custom SSL certificate.
		 *
		 * A custom SSL cert cannot be added to an app that is using AutoSSL. To replace
		 * AutoSSL with a custom SSL certificate, you must first disable AutoSSL on the
		 * app before adding the custom SSL certificate.
		 *
		 * @param  string $id      The app's ID.
		 * @param  string $key     The contents of the private key.
		 * @param  string $cert    The contents of the certificate.
		 * @param  string $cacerts The contents of the CA certificate(s). If none, null is acceptable.
		 * @return object          An object containing the SSL's data.
		 */
		public function add_custom_ssl( string $id, string $key, string $cert, string $cacerts = null ) {
			$args = array(
				'key'     => $key,
				'cert'    => $cert,
				'cacerts' => $cacerts
			);

			return $this->run( "apps/$id/ssl", $args, 'POST' ); // I may need to set a content-length header here...
		}

		/**
		 * Enable AutoSSL.
		 *
		 * AutoSSL can only be enabled when an AutoSSL certificate is available for an
		 * app. To determine if an AutoSSL certificate is available for an app, use
		 * the app details API call.
		 *
		 * Additionally, AutoSSL cannot be enabled when an app currently has a custom
		 * SSL certificate. To enable AutoSSL when an app is already using a custom
		 * SSL, first delete the app's custom SSL certificate.
		 *
		 * Note that disabling AutoSSL is not done through this API call but instead
		 * is done by deleting SSL from the app.
		 *
		 * Note that this requires at least the coach plan.
		 *
		 * @param  string $id The app's ID.
		 * @return object     The updated certificate.
		 */
		public function enable_auto_ssl( string $id ) {
			return $this->run( "apps/$id/ssl", array( 'auto' => true ), 'POST' );
		}

		/**
		 * Delete a custom SSL certificate or disable AutoSSL.
		 *
		 * @param  string $id The app's ID.
		 * @return object     The response with an actionid.
		 */
		public function delete_custom_ssl( string $id ) {
			return $this->run( "apps/$id/ssl", array(), 'DELETE' );
		}

		/**
		 * Delete a custom SSL certificate or disable AutoSSL.
		 *
		 * Note that this is just a wrapper of delete_custom_ssl, and is included
		 * in case clarity is wanted in code elsewhere.
		 *
		 * @param  string $id The app's ID.
		 * @return object     The response with an actionid.
		 */
		public function disable_auto_ssl( string $id ) {
			return $this->delete_custom_ssl( $id );
		}

		/**
		 * Enable or disable ForceSSL.
		 *
		 * ForceSSL can only be enabled when an app already has SSL enabled.
		 *
		 * You cannot enable ForceSSL at the same time as adding a custom SSL
		 * certificate or enabling AutoSSL. You must make a separate API call to
		 * enable or disable ForceSSL.
		 *
		 * ForceSSL will be automatically disabled if SSL is deleted from an app.
		 *
		 * @param  string $id    The app's ID.
		 * @param  bool   $force Whether forced redirection from HHTP to HTTPS is enabled.
		 * @return object        The app's current SSL settings.
		 */
		public function toggle_force_ssl( string $id, bool $force ) {
			return $this->run( "apps/$id/ssl", array( 'force' => $force ), 'POST' );
		}

		/* DATABASES. */

		/**
		 * List all databases.
		 *
		 * @return object An object containing a data parameter that is an array of databases.
		 */
		public function list_databases() {
			return $this->run( 'dbs' );
		}

		/**
		 * Create a database.
		 *
		 * Eg:
		 *   create_database(
		 *     '1234abcd',
		 *     'Ye Olde Data',
		 *     array(
		 *       'name' => 'Johnny',
		 *       'password' => 'mumstheword'
		 *     )
		 *   )
		 *
		 * @param  string $appid    The app's ID to create the database under.
		 * @param  string $name     The name of the database. Length must be between 3
		 *                          and 64 characters. Characters can be of lowercase
		 *                          ascii letters, digits, or a dash
		 *                          ('abcdefghijklmnopqrstuvwxyz0123456789-').
		 * @param  string $username The name of the Database User. Length must be at
		 *                          most 16 characters.
		 * @param  string $password The password of the Database User. No leading or
		 *                          trailing whitespace is allowed and the password must
		 *                          be at least 8 and no more than 200 characters long.
		 * @return object           The database details.
		 */
		public function create_database( string $appid, $name, string $username, string $password ) {
			$args = array(
				'appid' => $appid,
				'name'  => $name,
				'user'  => array(
					'name'     => $username,
					'password' => trim( $password )
				)
			);

			return $this->run( 'dbs', $args, 'POST' );
		}

		/**
		 * Retrieve an existing database.
		 *
		 * @param  string $id The database's ID.
		 * @return object     The database's details.
		 */
		public function show_database( string $id ) {
			return $this->run( 'dbs/'.$id );
		}

		/**
		 * Update the database user password.
		 *
		 * @param  string $id       The database's ID.
		 * @param  string $user_id  The id of the database user.
		 * @param  string $password The _new_ password of the database user. The
		 *                          password must be at least 8 and no more than 200
		 *                          characters long.
		 * @return object           The updated server details.
		 */
		public function update_db_user_password( string $id, string $user_id, string $password ) {
			$args = array(
				'user' => array(
					'id'       => $user_id,
					'password' => trim( $password )
				)
			);

			return $this->run( 'dbs/'.$id, $args, 'POST' );
		}

		/**
		 * Delete a database.
		 *
		 * @param  string $id The database's ID.
		 * @return object     The confirmation object with an actionid.
		 */
		public function delete_database( string $id ) {
			return $this->run( 'dbs/'.$id, array(), 'DELETE' );
		}

		/* ACTIONS. */

		/**
		 * Used to check the status of an action, by actionid (which is returned after
		 * a lot of strenuous jobs, ie: database deletion/creation).
		 *
		 * Actions are a record of work done on ServerPilot resources. These can be things
		 * like the creation of an App, deploying SSL, deleting an old Database, etc.
		 *
		 * All methods that modify a resource will have an actionid top-level key in
		 * the JSON response if any server configuration was required. The actionid
		 * can be used to track the status of the Action.
		 *
		 * Possible values of the action status:
		 *   success	Action was completed successfully.
		 *   open	    Action has not completed yet.
		 *   error	  Action has completed but there were errors.
		 *
		 * @param  string $actionid The action ID.
		 * @return object           The status of the action.
		 */
		public function check_action_status( string $actionid ) {
			return $this->run( 'actions/'.$actionid );
		}

	} // Endif().
} // Endif().
