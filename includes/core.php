<?php

namespace OTWatchdog;

if (!defined('ABSPATH')) {
    exit;
}

class Core
{
    const OPTION_STATUS = 'ot_watchdog_status';
    const OPTION_SETTINGS = 'ot_watchdog_settings';

    /**
     * =========================
     * DATA OPSLAAN
     * =========================
     */
    public static function save_status(array $data): void
    {
        $payload = [
            'devices' => $data,
            'last_update' => time()
        ];

        update_option(self::OPTION_STATUS, $payload);
    }

    /**
     * =========================
     * DATA OPHALEN
     * =========================
     */
    public static function get_status(): array
    {
        return get_option(self::OPTION_STATUS, []);
    }

    public static function get_settings(): array
    {
        return get_option(self::OPTION_SETTINGS, []);
    }

    /**
     * =========================
     * DEVICE STATUS BEREKENEN
     * (5 min timeout logica)
     * =========================
     */
    public static function get_device_statuses(): array
    {
        $data = self::get_status();

        if (!$data || !isset($data['devices'])) {
            return [];
        }

        $devices = $data['devices'];
        $last_update = $data['last_update'] ?? 0;

        $timeout = 300; // 5 minuten
        $now = time();

        $result = [];

        foreach ($devices as $device => $status) {

            if (($now - $last_update) > $timeout) {
                $status = 'offline';
            }

            $result[$device] = $status;
        }

        return $result;
    }

    /**
     * =========================
     * CHECK: IS SYSTEM ONLINE?
     * =========================
     */
    public static function is_system_online(): bool
    {
        $data = self::get_status();

        if (!$data || !isset($data['last_update'])) {
            return false;
        }

        return (time() - $data['last_update']) <= 300;
    }

    /**
     * =========================
     * AANTAL ASSETS UIT SETTINGS
     * =========================
     */
    public static function get_asset_count(): int
    {
        $settings = self::get_settings();
        return (int) ($settings['asset_count'] ?? 1);
    }
}

