<?php
/**
 * Plugin Name: Streaming Malagasy
 * Description: Plugin streaming Gasy - Architecture professionnelle
 * Version: 1.0
 * Author: Fidy
 */

if (!defined('ABSPATH')) {
    exit;
}

// Définir le fichier du plugin
define('MALAGASY_STREAMING_FILE', __FILE__);

// Charger la classe principale du plugin
require_once plugin_dir_path(__FILE__) . 'includes/Core/class-plugin.php';

// Hooks d'activation/désactivation
register_activation_hook(__FILE__, [Malagasy_Streaming_Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [Malagasy_Streaming_Plugin::class, 'deactivate']);