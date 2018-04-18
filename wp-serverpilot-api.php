<?php
/**
 * WP-ServerPilot-API (https://github.com/ServerPilot/API)
 *
 * @package WP-ServerPilot-API
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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'includes/class-wpserverpilotapibase.php';
require_once 'includes/class-serverpilotapi.php';
