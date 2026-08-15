<?php

namespace OTWatchdog;

if (!defined('ABSPATH')) {
    exit;
}

class Updater
{
    const UPDATE_CHECK_INTERVAL = 86400; // 24 hours
    const REMOTE_UPDATES_URL = 'https://incyi.github.io/ot-watchdog/update-info.json';

    public function __construct()
    {
        add_filter('plugins_api', [$this, 'plugin_api'], 10, 3);
        add_filter('site_transient_update_plugins', [$this, 'check_for_updates']);
        add_filter('transient_update_plugins', [$this, 'check_for_updates']);
    }

    /**
     * Check for plugin updates
     */
    public function check_for_updates($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $plugin_file = 'ot-watchdog/ot-watchdog.php';
        $current_version = $transient->checked[$plugin_file] ?? '0.0.0';

        $remote_data = $this->get_remote_updates();

        if (!$remote_data || empty($remote_data['version'])) {
            return $transient;
        }

        if (version_compare($current_version, $remote_data['version'], '<')) {
            $transient->response[$plugin_file] = (object) [
                'slug' => 'ot-watchdog',
                'plugin' => $plugin_file,
                'new_version' => $remote_data['version'],
                'url' => 'https://github.com/incyi/ot-watchdog',
                'package' => $remote_data['download_url'] ?? '',
                'tested' => $remote_data['tested_up_to'] ?? '6.0',
                'requires_php' => $remote_data['requires_php'] ?? '8.0',
                'last_updated' => $remote_data['last_updated'] ?? '',
            ];
        }

        return $transient;
    }

    /**
     * Return plugin info for install/update screens
     */
    public function plugin_api($result, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== 'ot-watchdog') {
            return $result;
        }

        $remote_data = $this->get_remote_updates();

        if (!$remote_data) {
            return $result;
        }

        return (object) [
            'name' => $remote_data['name'] ?? 'OT Watchdog',
            'slug' => 'ot-watchdog',
            'version' => $remote_data['version'] ?? '0.0.0',
            'author' => 'İnanç Yiğit',
            'author_profile' => 'https://github.com/incyi',
            'plugin_url' => 'https://github.com/incyi/ot-watchdog',
            'download_url' => $remote_data['download_url'] ?? '',
            'requires_php' => $remote_data['requires_php'] ?? '8.0',
            'tested' => $remote_data['tested_up_to'] ?? '6.0',
            'requires_at_least' => '5.9',
            'last_updated' => $remote_data['last_updated'] ?? '',
            'changelog' => $remote_data['changelog'] ?? '',
            'sections' => [
                'description' => 'Lightweight OT status monitoring plugin for WordPress (PLC, HMI, Switch, Modem)',
                'installation' => 'Upload the plugin file and activate it.',
            ],
        ];
    }

    /**
     * Fetch remote updates metadata
     */
    private function get_remote_updates()
    {
        $transient_key = 'ot_watchdog_remote_updates';
        $cached = get_transient($transient_key);

        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get(self::REMOTE_UPDATES_URL, [
            'timeout' => 5,
            'sslverify' => true,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data)) {
            return false;
        }

        set_transient($transient_key, $data, self::UPDATE_CHECK_INTERVAL);

        return $data;
    }
}
