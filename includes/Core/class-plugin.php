<?php
/**
 * Main Plugin Class
 * Point d'entrée du plugin
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Streaming_Plugin {

    private static $instance;

    /**
     * Singleton
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->setup();
    }

    /**
     * Configure le plugin
     */
    private function setup() {
        // Version du plugin
        define('MALAGASY_STREAMING_VERSION', '1.0');
        define('MALAGASY_STREAMING_DIR', plugin_dir_path(MALAGASY_STREAMING_FILE));
        define('MALAGASY_STREAMING_URL', plugin_dir_url(MALAGASY_STREAMING_FILE));

        // Charger le loader
        require_once MALAGASY_STREAMING_DIR . 'includes/Core/class-plugin-loader.php';
        Malagasy_Plugin_Loader::get_instance();
    }

    /**
     * Active le plugin
     */
    public static function activate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Désactive le plugin
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialiser le plugin
Malagasy_Streaming_Plugin::get_instance();
