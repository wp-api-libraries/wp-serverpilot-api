<?php
/**
 * WP-ServerPilot-API (https://github.com/ServerPilot/API)
 *
 * @package WP-ServerPilotr-API
 */
/*
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
\
/* Check if class exists. */
if ( ! class_exists( 'ServerPilotAPI' ) ) {
	
	/**
	 * ServerPilot API Class.
	 */
	class ServerPilotAPI {
		
		/**
		 * BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://api.serverpilot.io/v1';
		
		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct( $client_id, $api_key ) {
			
		}
		
		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @param mixed $request Request URL.
		 * @return $body Body.
		 */
		private function fetch( $request ) {
			$response = wp_remote_get( $request );
			$code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $code ) {
				return new WP_Error( 'response-error', sprintf( __( 'Server response code: %d', 'wp-serverpilot-api' ), $code ) );
			}
			$body = wp_remote_retrieve_body( $response );
			return json_decode( $body );
		}
		
		/* SERVERS. */
		
		/**
		 * list_servers function.
		 * 
		 * @access public
		 * @return void
		 */
		public function list_servers() {
			
			$request = $this->base_uri . '/servers';
			return $this->fetch( $request );
			
		}

		public function connect_new_server() {
			
		}
		
		public function retrieve_existing_server() {
			
		}
		
		public function delete_server() {
			
		}
		
		public function update_server() {
			
		}
		
		/* SYSTEM USERS. */
		
		public function list_system_users() {
			
		}
		
		public function create_system_user() {
			
		}
		
		public function retrieve_existing_system_user() {
			
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
		
		public function get_app() {
			
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
		
		public disable_auto_ssl() {
			
		}
		
		public function toggle_force_ssl() {
			
		}
		
		/* DATABASES. */
		
		public function list_databases() {
			
		}
		
		public function create_database() {
			
		}
		
		public function retrieve_database() {
			
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