<?php
defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------
 * Helper
 * ----------------------------------------------------- */
function blb_fomo_settings() {
    $defaults = [
        'enabled'       => true,
        'style'         => 'popup',
        'position'      => 'bottom-left',
        'interval'      => 8,
        'duration'      => 5,
        'max_per_page'  => 10,
        'delay_first'   => 3,
        'exclude_pages' => '',
        'nowa'          => '',
    ];
    return wp_parse_args( get_option( 'blb_fomo_settings', [] ), $defaults );
}

/* -------------------------------------------------------
 * Custom Post Type: blb_booking
 * ----------------------------------------------------- */
add_action( 'init', 'blb_register_booking_cpt' );
function blb_register_booking_cpt() {
    register_post_type( 'blb_booking', [
        'labels' => [
            'name'          => 'Fake Booking',
            'singular_name' => 'Booking',
            'add_new'       => 'Tambah Booking',
            'add_new_item'  => 'Tambah Booking Baru',
            'edit_item'     => 'Edit Booking',
            'all_items'     => 'Fake Booking',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => false,
        'supports'     => [ 'title' ],
        'capabilities' => [ 'create_posts' => 'manage_options' ],
        'map_meta_cap' => true,
    ] );
}

/* -------------------------------------------------------
 * Meta boxes untuk detail booking
 * ----------------------------------------------------- */
add_action( 'add_meta_boxes', 'blb_booking_meta_boxes' );
function blb_booking_meta_boxes() {
    add_meta_box(
        'blb_booking_details',
        'Detail Booking',
        'blb_booking_meta_box_cb',
        'blb_booking',
        'normal',
        'high'
    );
}

function blb_booking_meta_box_cb( $post ) {
    wp_nonce_field( 'blb_save_booking_meta', 'blb_booking_nonce' );
    $service  = get_post_meta( $post->ID, '_blb_service',  true );
    $location = get_post_meta( $post->ID, '_blb_location', true );
    $time     = get_post_meta( $post->ID, '_blb_time',     true );
     $services = ['EXPRESS 12 HOUR','EXPRESS 6 HOUR','EXPRESS 3 HOUR','EXPRESS 2 HOUR'];
    ?>
    <table class="form-table" style="margin:0;">
        <tr>
            <th style="width:120px;padding:8px 0;"><label for="blb_service">Layanan</label></th>
            <td style="padding:8px 0;">
                <select name="blb_service" id="blb_service" style="min-width:200px;">
                    <option>Pilih Layanan</option>
                    <?php foreach ( $services as $svc ) : ?>
                        <option value="<?= esc_attr($svc) ?>" <?= selected($service,$svc,false) ?>><?= esc_html($svc) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th style="padding:8px 0;"><label for="blb_location">Lokasi</label></th>
            <td style="padding:8px 0;">
                <input type="text" name="blb_location" id="blb_location" value="<?= esc_attr($location) ?>" class="regular-text" placeholder="The Layar Resort">
            </td>
        </tr>
        <tr>
            <th style="padding:8px 0;"><label for="blb_time">Waktu pickup</label></th>
            <td style="padding:8px 0;">
                <input type="text" name="blb_time" id="blb_time" value="<?= esc_attr($time) ?>" class="small-text" placeholder="10:30–11:00">
            </td>
        </tr>
    </table>
    <?php
}

add_action( 'save_post_blb_booking', 'blb_save_booking_meta' );
function blb_save_booking_meta( $post_id ) {
    if ( ! isset( $_POST['blb_booking_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['blb_booking_nonce'], 'blb_save_booking_meta' ) ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    update_post_meta( $post_id, '_blb_service',  sanitize_text_field( $_POST['blb_service']  ?? '' ) );
    update_post_meta( $post_id, '_blb_location', sanitize_text_field( $_POST['blb_location'] ?? '' ) );
    update_post_meta( $post_id, '_blb_time',     sanitize_text_field( $_POST['blb_time']     ?? '' ) );
}

/* -------------------------------------------------------
 * Kolom tambahan di list CPT
 * ----------------------------------------------------- */
add_filter( 'manage_blb_booking_posts_columns', 'blb_booking_columns' );
function blb_booking_columns( $cols ) {
    return [
        'cb'       => $cols['cb'],
        'title'    => 'Nama Tamu',
        'service'  => 'Layanan',
        'location' => 'Lokasi',
        'time'     => 'Waktu Pickup',
        'date'     => 'Waktu Booking',
    ];
}

add_action( 'manage_blb_booking_posts_custom_column', 'blb_booking_column_values', 10, 2 );
function blb_booking_column_values( $col, $post_id ) {
    if ( $col === 'service'  ) echo esc_html( get_post_meta( $post_id, '_blb_service',  true ) );
    if ( $col === 'location' ) echo esc_html( get_post_meta( $post_id, '_blb_location', true ) );
    if ( $col === 'time'     ) echo esc_html( get_post_meta( $post_id, '_blb_time',     true ) );
}

/* -------------------------------------------------------
 * Admin menu: FOMO settings
 * ----------------------------------------------------- */
add_action( 'admin_menu', 'blb_fomo_menu', 20 );
function blb_fomo_menu() {
    add_menu_page(
        'Fomo Pulse',
        'Fomo Pulse',
        'manage_options',
        'fomo-pulse',
        'blb_fomo_admin_page',
        'dashicons-bell',
        56
    );
    
    add_submenu_page(
        'fomo-pulse',
        'Fake Booking',
        'Fake Booking',
        'manage_options',
        'edit.php?post_type=blb_booking',
        null
    );
}

function blb_fomo_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    if ( isset( $_POST['blb_save_fomo'] ) && check_admin_referer( 'blb_save_fomo' ) ) {
        update_option( 'blb_fomo_settings', [
            'enabled'       => ! empty( $_POST['enabled'] ),
            'style'         => sanitize_text_field( $_POST['style']        ?? 'popup' ),
            'position'      => sanitize_text_field( $_POST['position']     ?? 'bottom-left' ),
            'interval'      => absint( $_POST['interval']    ?? 8 ),
            'duration'      => absint( $_POST['duration']    ?? 5 ),
            'max_per_page'  => absint( $_POST['max_per_page']?? 10 ),
            'delay_first'   => absint( $_POST['delay_first'] ?? 3 ),
            'nowa'          => sanitize_text_field( $_POST['nowa'] ?? '' ),
            'exclude_pages' => sanitize_text_field( $_POST['exclude_pages'] ?? '' ),
        ] );
        echo '<div class="notice notice-success"><p>Pengaturan FOMO disimpan.</p></div>';
    }

    $s = blb_fomo_settings();
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-megaphone"></span> FOMO Notifikasi</h1>
        <form method="post" style="max-width:640px;margin-top:20px;">
            <?php wp_nonce_field( 'blb_save_fomo' ); ?>
            <table class="form-table">
                <tr>
                    <th>Aktifkan FOMO</th>
                    <td><label><input type="checkbox" name="enabled" value="1" <?= $s['enabled']?'checked':'' ?>> Tampilkan notifikasi di seluruh situs</label></td>
                </tr>
                <tr>
                    <th><label for="style">Gaya tampilan</label></th>
                    <td>
                        <select name="style" id="style">
                            <option value="popup" <?= $s['style']==='popup'?'selected':'' ?>>Popup pojok</option>
                            <option value="bar"   <?= $s['style']==='bar'  ?'selected':'' ?>>Bar bawah</option>
                            <option value="bar-atas"   <?= $s['style']==='bar-atas'  ?'selected':'' ?>>Bar atas</option>
                            <option value="both"  <?= $s['style']==='both' ?'selected':'' ?>>Keduanya</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="position">Posisi popup</label></th>
                    <td>
                        <select name="position" id="position">
                            <option value="bottom-left"  <?= $s['position']==='bottom-left' ?'selected':'' ?>>Kiri bawah</option>
                            <option value="bottom-right" <?= $s['position']==='bottom-right'?'selected':'' ?>>Kanan bawah</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="delay_first">Jeda notif pertama</label></th>
                    <td><input type="number" name="delay_first" value="<?= esc_attr($s['delay_first']) ?>" min="1" max="30" class="small-text"> detik</td>
                </tr>
                <tr>
                    <th><label for="duration">Durasi tampil</label></th>
                    <td><input type="number" name="duration" value="<?= esc_attr($s['duration']) ?>" min="2" max="30" class="small-text"> detik</td>
                </tr>
                <tr>
                    <th><label for="interval">Interval antar notif</label></th>
                    <td><input type="number" name="interval" value="<?= esc_attr($s['interval']) ?>" min="3" max="60" class="small-text"> detik</td>
                </tr>
                <tr>
                    <th><label for="max_per_page">Maks notif per sesi</label></th>
                    <td><input type="number" name="max_per_page" value="<?= esc_attr($s['max_per_page']) ?>" min="1" max="50" class="small-text"></td>
                </tr>
                <tr>
                    <th><label for="nowa">Nomor WhatsApp</label></th>
                    <td>
                        <input type="text" name="nowa" value="<?= esc_attr($s['nowa']) ?>" class="regular-text" placeholder="62....">
                        <small class="description">Contoh : 6281234567890</small>
                    </td>
                </tr>
                <tr>
                    <th><label for="exclude_pages">Nonaktifkan di halaman</label></th>
                    <td>
                        <input type="text" name="exclude_pages" value="<?= esc_attr($s['exclude_pages']) ?>" class="regular-text" placeholder="12, 45, 78">
                        <p class="description">Post/Page ID dipisah koma</p>
                    </td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="blb_save_fomo" class="button button-primary" value="Simpan Pengaturan"></p>
        </form>
    </div>
    <?php
}

/* -------------------------------------------------------
 * AJAX: ambil data booking dari CPT untuk FOMO
 * ----------------------------------------------------- */
add_action( 'wp_ajax_blb_fomo_data',        'blb_ajax_fomo_data' );
add_action( 'wp_ajax_nopriv_blb_fomo_data', 'blb_ajax_fomo_data' );
function blb_ajax_fomo_data() {
    $posts = get_posts( [
        'post_type'      => 'blb_booking',
        'posts_per_page' => 50,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    $bookings = [];
    $now = time();
    foreach ( $posts as $p ) {
        $diff = $now - strtotime( $p->post_date );
        if ( $diff < 3600 )      $ago = max(1, round($diff/60)) . ' menit lalu';
        elseif ( $diff < 86400 ) $ago = round($diff/3600) . ' jam lalu';
        else                     $ago = round($diff/86400) . ' hari lalu';

        $bookings[] = [
            'name'        => $p->post_title,
            'service'     => get_post_meta( $p->ID, '_blb_service',  true ),
            'location'    => get_post_meta( $p->ID, '_blb_location', true ),
            'pickup_time' => get_post_meta( $p->ID, '_blb_time',     true ),
            'ago'         => $ago,
        ];
    }

    if ( $bookings ) shuffle( $bookings );
    wp_send_json_success( $bookings );
}

/* -------------------------------------------------------
 * AJAX: simpan booking baru dari form ke CPT
 * ----------------------------------------------------- */
add_action( 'wp_ajax_blb_save_booking',        'blb_ajax_save_booking' );
add_action( 'wp_ajax_nopriv_blb_save_booking', 'blb_ajax_save_booking' );
function blb_ajax_save_booking() {
    $name     = sanitize_text_field( $_POST['name']     ?? '' );
    $service  = sanitize_text_field( $_POST['service']  ?? '' );
    $location = sanitize_text_field( $_POST['location'] ?? '' );
    $time     = sanitize_text_field( $_POST['time']     ?? '' );

    if ( ! $name || ! $service ) wp_send_json_error( 'Data tidak lengkap' );

    $post_id = wp_insert_post( [
        'post_type'   => 'blb_booking',
        'post_title'  => $name,
        'post_status' => 'publish',
    ] );

    if ( $post_id ) {
        update_post_meta( $post_id, '_blb_service',  $service );
        update_post_meta( $post_id, '_blb_location', $location );
        update_post_meta( $post_id, '_blb_time',     $time );
    }

    wp_send_json_success();
}

/* -------------------------------------------------------
 * Frontend: inject FOMO ke semua halaman
 * ----------------------------------------------------- */
 add_action( 'wp_enqueue_scripts', 'blb_fomo_enqueue' );
function blb_fomo_enqueue() {
    $s = blb_fomo_settings();
    if ( ! $s['enabled'] ) return;

    if ( $s['exclude_pages'] ) {
        $excluded = array_map( 'absint', explode( ',', $s['exclude_pages'] ) );
        if ( in_array( get_the_ID(), $excluded ) ) return;
    }
    
    //CUSTOM JS & CSS
    wp_enqueue_style(  'custom-fomo', BLB_URL . 'assets/custom.css', [], BLB_VERSION );

    wp_enqueue_style(  'blb-fomo', BLB_URL . 'assets/fomo.css', [], BLB_VERSION );
    wp_enqueue_script( 'blb-fomo', BLB_URL . 'assets/fomo.js',  [], BLB_VERSION, true );
    wp_localize_script( 'blb-fomo', 'blbFomoConfig', [
        'ajaxurl'      => admin_url( 'admin-ajax.php' ),
        'style'        => $s['style'],
        'position'     => $s['position'],
        'interval'     => intval( $s['interval'] )    * 1000,
        'duration'     => intval( $s['duration'] )    * 1000,
        'max_per_page' => intval( $s['max_per_page'] ),
        'delay_first'  => intval( $s['delay_first'] ) * 1000,
        'nowa'         => $s['nowa'],
    ] );
}


add_action( 'wp_footer', 'blb_fomo_inject' );
function blb_fomo_inject() {
}