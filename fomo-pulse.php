<?php
/**
 * Plugin Name: Fomo Pulse Notifikasi
 * Plugin URI:  https://github.com/dhytalu
 * Description: Pulse notifikasi fake order atau fake booking untuk klien Buat Creative
 * Version:     1.0.0
 * Author:      Buat Creative
 * Text Domain: fomo-pulse
 */

defined( 'ABSPATH' ) || exit;

define( 'BLB_VERSION', '1.0.0' );
define( 'BLB_DIR', plugin_dir_path( __FILE__ ) );
define( 'BLB_URL', plugin_dir_url( __FILE__ ) );

require_once BLB_DIR . 'includes/admin-settings.php';
require_once BLB_DIR . 'includes/shortcode.php';
require_once BLB_DIR . 'includes/ajax.php';
require_once BLB_DIR . 'includes/fomo.php';
