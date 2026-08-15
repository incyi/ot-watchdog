<?php
/**
 * Plugin Name: OT Watchdog
 * Description: Lightweight OT status monitoring plugin (PLC, HMI, Switch, Modem)
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * =========================
 * CONFIG
 * =========================
 */
define('OT_WATCHDOG_VERSION', '0.0.4');
define('OT_OPTION_KEY', 'ot_watchdog_status');

/**
 * =========================
 * AUTOLOADER
 * =========================
 */
require_once plugin_dir_path(__FILE__) . 'includes/core.php';
require_once plugin_dir_path(__FILE__) . 'includes/api.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/updater.php';

/**
 * =========================
 * INITIALIZE PLUGIN
 * =========================
 */
add_action('plugins_loaded', function () {
    new OTWatchdog\Api();
    new OTWatchdog\Admin();
    new OTWatchdog\Updater();
});

/**
 * =========================
 * SHORTCODE DISPLAY
 * =========================
 */
add_shortcode('ot_status', function () {
    $data = OTWatchdog\Core::get_status();

    if (!$data) {
        return "<p>No OT data available</p>";
    }

    $devices = $data['devices'] ?? [];
    $last_update = $data['last_update'] ?? 0;
    $device_statuses = OTWatchdog\Core::get_device_statuses();

    $output = "<div style='font-family:Arial'>";
    $output .= "<h3>OT Watchdog Status</h3>";
    $output .= "<p><small>Last update: " . date('Y-m-d H:i:s', $last_update) . "</small></p>";
    $output .= "<ul>";

    foreach ($device_statuses as $device => $status) {
        $color = ($status === "online") ? "green" : "red";

        $output .= "<li>
            <strong>{$device}</strong>: 
            <span style='color:{$color}'>{$status}</span>
        </li>";
    }

    $output .= "</ul></div>";

    return $output;
});
