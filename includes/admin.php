<?php

namespace OTWatchdog;

if (!defined('ABSPATH')) {
    exit;
}

class Admin
{
    const OPTION_KEY = 'ot_watchdog_settings';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * =========================
     * ADMIN MENU
     * =========================
     */
    public function register_menu()
    {
        add_menu_page(
            'OT Watchdog',
            'OT Watchdog',
            'manage_options',
            'ot-watchdog',
            [$this, 'render_page'],
            'dashicons-visibility',
            80
        );
    }

    /**
     * =========================
     * SETTINGS REGISTRATIE
     * =========================
     */
    public function register_settings()
    {
        register_setting('ot_watchdog_group', self::OPTION_KEY);

        add_settings_section(
            'ot_watchdog_main',
            'General Settings',
            null,
            'ot-watchdog'
        );

        add_settings_field(
            'asset_count',
            'Aantal assets',
            [$this, 'field_asset_count'],
            'ot-watchdog',
            'ot_watchdog_main'
        );
    }

    /**
     * =========================
     * INPUT FIELD
     * =========================
     */
    public function field_asset_count()
    {
        $options = get_option(self::OPTION_KEY);
        $value = $options['asset_count'] ?? 1;

        echo '<input type="number" name="' . self::OPTION_KEY . '[asset_count]" value="' . esc_attr($value) . '" min="1" max="100" />';
    }

    /**
     * =========================
     * ADMIN PAGINA
     * =========================
     */
    public function render_page()
    {
        $options = get_option(self::OPTION_KEY);
        $count = $options['asset_count'] ?? 1;

        echo '<div class="wrap">';
        echo '<h1>OT Watchdog</h1>';

        echo '<p><strong>Configured assets:</strong> ' . esc_html($count) . '</p>';

        echo '<form method="post" action="options.php">';
        settings_fields('ot_watchdog_group');
        do_settings_sections('ot-watchdog');
        submit_button();
        echo '</form>';

        echo '</div>';
    }
}