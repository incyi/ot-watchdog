<?php
/**
 * Plugin Name: OT Watchdog
 * Description: Lightweight OT status monitoring plugin (PLC, HMI, Switch, Modem)
 * Version: 0.0.1
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * =========================
 * CONFIG
 * =========================
 */
define('OT_WATCHDOG_VERSION', '0.0.1');
define('OT_OPTION_KEY', 'ot_watchdog_status');

/**
 * =========================
 * REST API ENDPOINT
 * Raspberry Pi / agents push data here
 * =========================
 */
add_action('rest_api_init', function () {
    register_rest_route('ot/v1', '/update', array(
        'methods'  => 'POST',
        'callback' => 'ot_watchdog_update',
        'permission_callback' => '__return_true'
    ));
});

function ot_watchdog_update($request) {

    $data = $request->get_json_params();

    if (!$data) {
        return new WP_REST_Response(['error' => 'No data'], 400);
    }

    $payload = [
        'devices' => $data,
        'last_update' => time(),
        'version' => OT_WATCHDOG_VERSION
    ];

    update_option(OT_OPTION_KEY, $payload);

    return [
        'success' => true,
        'saved_at' => $payload['last_update']
    ];
}

/**
 * =========================
 * SHORTCODE DISPLAY
 * =========================
 */
add_shortcode('ot_status', function () {

    $data = get_option(OT_OPTION_KEY);

    if (!$data) {
        return "<p>No OT data available</p>";
    }

    $devices = $data['devices'] ?? [];
    $last_update = $data['last_update'] ?? 0;

    $timeout = 300; // 5 min
    $now = time();

    $output = "<div style='font-family:Arial'>";
    $output .= "<h3>OT Watchdog Status</h3>";
    $output .= "<p><small>Last update: " . date('Y-m-d H:i:s', $last_update) . "</small></p>";
    $output .= "<ul>";

    foreach ($devices as $device => $status) {

        // Force offline if stale
        if (($now - $last_update) > $timeout) {
            $status = "offline";
        }

        $color = ($status === "online") ? "green" : "red";

        $output .= "<li>
            <strong>{$device}</strong>: 
            <span style='color:{$color}'>{$status}</span>
        </li>";
    }

    $output .= "</ul></div>";

    return $output;
});

/**
 * =========================
 * ADMIN MENU (simple view)
 * =========================
 */
add_action('admin_menu', function () {
    add_menu_page(
        'OT Watchdog',
        'OT Watchdog',
        'manage_options',
        'ot-watchdog',
        'ot_watchdog_admin_page',
        'dashicons-visibility',
        80
    );
});

function ot_watchdog_admin_page() {

    $data = get_option(OT_OPTION_KEY);

    echo "<div class='wrap'>";
    echo "<h1>OT Watchdog</h1>";

    if (!$data) {
        echo "<p>No data received yet.</p>";
        echo "</div>";
        return;
    }

    echo "<p><strong>Plugin version:</strong> " . OT_WATCHDOG_VERSION . "</p>";
    echo "<p><strong>Last update:</strong> " . date('Y-m-d H:i:s', $data['last_update']) . "</p>";

    echo "<h2>Devices</h2>";
    echo "<ul>";

    foreach ($data['devices'] as $device => $status) {
        echo "<li><strong>{$device}</strong>: {$status}</li>";
    }

    echo "</ul>";
    echo "</div>";
}