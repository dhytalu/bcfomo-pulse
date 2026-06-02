<?php
defined( 'ABSPATH' ) || exit;

// add_action( 'admin_menu', 'blb_admin_menu' );
// function blb_admin_menu() {
//     add_menu_page(
//         'Fake Notifikasi',
//         'Fake Notifikasi',
//         'manage_options',
//         'fake-notifikasi',
//         'blb_settings_page',
//         'dashicons-bell',
//         56
//     );
// }

add_action( 'admin_enqueue_scripts', 'blb_admin_scripts' );
function blb_admin_scripts( $hook ) {
    if ( strpos( $hook, 'fomo-pulse' ) === false ) return;
    wp_enqueue_style( 'blb-admin', BLB_URL . 'assets/admin.css', [], BLB_VERSION );
    wp_enqueue_script( 'blb-admin', BLB_URL . 'assets/admin.js', [ 'jquery' ], BLB_VERSION, true );
    wp_localize_script( 'blb-admin', 'blbAdmin', [
        'nonce'    => wp_create_nonce( 'blb_admin_nonce' ),
        'ajaxurl'  => admin_url( 'admin-ajax.php' ),
        // 'services' => blb_get_services(),
    ] );
}