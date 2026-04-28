<?php

namespace OTWatchdog;

if (!defined('ABSPATH')) {
    exit;
}

class Api
{
    /**
     * =========================
     * BOOTSTRAP
     * =========================
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * =========================
     * ROUTES REGISTREREN
     * =========================
     */
    public function register_routes()
    {
        register_rest_route('ot/v1', '/update', [
            'methods'  => 'POST',
            'callback' => [$this, 'handle_update'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * =========================
     * API HANDLER
     * =========================
     */
    public function handle_update($request)
    {
        $params = $request->get_json_params();

        if (!$params) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'No data received'
            ], 400);
        }

        // 🔐 API KEY CHECK
        if (!$this->validate_key($request)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // 📦 DATA OPSLAAN VIA CORE
        Core::save_status($params);

        return new \WP_REST_Response([
            'success' => true,
            'message' => 'Status updated',
            'received' => $params
        ], 200);
    }

    /**
     * =========================
     * SIMPLE API KEY VALIDATIE
     * =========================
     */
    private function validate_key($request): bool
    {
        $expected_key = defined('OT_WATCHDOG_API_KEY')
            ? OT_WATCHDOG_API_KEY
            : 'change-me';

        $headers = $request->get_headers();

        $provided_key = $headers['x_api_key'][0] ?? ($request->get_param('key') ?? null);

        if (!$provided_key) {
            return false;
        }

        return hash_equals($expected_key, $provided_key);
    }
}