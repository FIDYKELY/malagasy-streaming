<?php
/**
 * Plugin Loader
 * Charge automatiquement les classes et initialise le plugin
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Plugin_Loader {

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
        $this->load_classes();
        $this->initialize_infrastructure();
        $this->initialize_application();
        $this->setup_hooks();
    }

    /**
     * Charge les classes du plugin
     */
    private function load_classes() {
        $includes_dir = plugin_dir_path(__FILE__) . '..';

        // Infrastructure - WordPress
        $this->load_file($includes_dir . '/Infrastructure/WordPress/class-cpt-manager.php');
        $this->load_file($includes_dir . '/Infrastructure/WordPress/class-films-cpt-manager.php');
        $this->load_file($includes_dir . '/Infrastructure/WordPress/class-tantara-cpt-manager.php');
        $this->load_file($includes_dir . '/Infrastructure/WordPress/class-metabox-manager.php');
        $this->load_file($includes_dir . '/Infrastructure/WordPress/class-films-metabox.php');
        $this->load_file($includes_dir . '/Infrastructure/WordPress/class-tantara-metabox.php');

        // Infrastructure - Payment
        $this->load_file($includes_dir . '/Infrastructure/Payment/class-woo-sync.php');
        $this->load_file($includes_dir . '/Infrastructure/Payment/class-payment-handler.php');

        // Domain - Films
        $this->load_file($includes_dir . '/Domain/Films/class-film.php');
        $this->load_file($includes_dir . '/Domain/Films/class-film-repository.php');

        // Domain - Tantara
        $this->load_file($includes_dir . '/Domain/Tantara/class-tantara.php');
        $this->load_file($includes_dir . '/Domain/Tantara/class-tantara-repository.php');

        // Domain - User
        $this->load_file($includes_dir . '/Domain/User/class-user-service.php');

        // Application - Middleware
        $this->load_file($includes_dir . '/Application/Middleware/class-auth-middleware.php');

        // Application - Controllers
        $this->load_file($includes_dir . '/Application/Controllers/class-auth-controller.php');
        $this->load_file($includes_dir . '/Application/Controllers/class-catalogue-controller.php');
        $this->load_file($includes_dir . '/Application/Controllers/class-streaming-controller.php');
    }

    /**
     * Initialise la couche Infrastructure
     */
    private function initialize_infrastructure() {
        // CPT
        new Malagasy_Films_CPT_Manager();
        new Malagasy_Tantara_CPT_Manager();

        // Metabox
        new Malagasy_Films_Metabox();
        new Malagasy_Tantara_Metabox();

        // WooCommerce Sync
        new Malagasy_Woo_Sync();
    }

    /**
     * Initialise la couche Application (API)
     */
    private function initialize_application() {
        // Controllers
        new Malagasy_Auth_Controller();
        new Malagasy_Catalogue_Controller();
        new Malagasy_Streaming_Controller();
    }

    /**
     * Configure les hooks WordPress
     */
    private function setup_hooks() {
        // Webhook pour les paiements WooCommerce
        add_action('woocommerce_order_status_completed', [
            'Malagasy_Payment_Handler',
            'handle_order_completed'
        ]);
    }

    /**
     * Charge un fichier s'il existe
     */
    private function load_file($file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
