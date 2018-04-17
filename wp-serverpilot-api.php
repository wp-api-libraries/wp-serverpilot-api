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

		private function run( $route, $args = array(), $method = 'GET' ){
			return $this->build_request( $route, $args, $method )->fetch();
		}

		protected function set_headers(){
			$this->args['headers'] = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->api_key )
			);
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
		 * NOTE: I'm not sure SERVER_ID == SERVERID, same for SERVER_APIKEY
		 * 		export SERVERID=SERVER_ID
		 * 		export SERVERAPIKEY=SERVER_APIKEY
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
		 * @return object       The created server.
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
		 * Delete a server
		 *
		 * @param  string The ID of the server.
		 * @return object Null.
		 */
		public function delete_server( string $id ) {
			return $this->run( 'servers/'.$id, array(), 'DELETE' );
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

		public function show_existing_system_user() {

		}

		public function delete_system_user() {

		}

		public function update_system_user() {

		}

		/* APPS. */

		public function list_apps() {

		}

		public function create_app() {

		}

		public function show_app() {

		}

		public function delete_app() {

		}

		public function update_app() {

		}

		public function add_custom_ssl() {

		}

		public function enable_auto_ssl() {

		}

		public function delete_custom_ssl() {

		}

		public function disable_auto_ssl() {

		}

		public function toggle_force_ssl() {

		}

		/* DATABASES. */

		public function list_databases() {

		}

		public function create_database() {

		}

		public function show_database() {

		}

		public function delete_database() {

		}

		public function update_db_user_password() {

		}

		/* ACTIONS. */

		public function check_action_status() {

		}

	} // Endif().
} // Endif().
