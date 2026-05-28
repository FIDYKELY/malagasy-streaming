<?php
/**
 * Auth Controller
 * Gère l'authentification des utilisateurs
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Auth_Controller {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Enregistre les routes API
     */
    public function register_routes() {
        // Route de connexion
        register_rest_route('malagasy/v1', '/login', [
            'methods' => 'POST',
            'callback' => [$this, 'login'],
            'permission_callback' => '__return_true'
        ]);

        // Route d'inscription
        register_rest_route('malagasy/v1', '/register', [
            'methods' => 'POST',
            'callback' => [$this, 'register'],
            'permission_callback' => '__return_true'
        ]);

        // Route de profil
        register_rest_route('malagasy/v1', '/profile', [
            'methods' => 'GET',
            'callback' => [$this, 'get_profile'],
            'permission_callback' => [Malagasy_Auth_Middleware::class, 'is_authenticated']
        ]);
    }

    /**
     * Authentifie un utilisateur
     */
    public function login($request) {
        $params = $request->get_json_params();
        $username = sanitize_user($params['username'] ?? '');
        $password = $params['password'] ?? '';

        if (empty($username) || empty($password)) {
            return new WP_Error(
                'missing_fields',
                'Nom d\'utilisateur et mot de passe requis.',
                ['status' => 400]
            );
        }

        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            return new WP_Error(
                'invalid_credentials',
                'Identifiants incorrects.',
                ['status' => 401]
            );
        }

        $subscription = Malagasy_User_Service::get_subscription($user->ID);

        return rest_ensure_response([
            'success' => true,
            'user_id' => $user->ID,
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'subscription' => $subscription,
            'message' => 'Connexion réussie. Utilisez /jwt-auth/v1/token pour obtenir votre JWT.'
        ]);
    }

    /**
     * Enregistre un nouvel utilisateur
     */
    public function register($request) {
        $params = $request->get_json_params();
        $username = sanitize_user($params['username'] ?? '');
        $email = sanitize_email($params['email'] ?? '');
        $password = $params['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            return new WP_Error(
                'missing_fields',
                'Tous les champs sont requis.',
                ['status' => 400]
            );
        }

        if (!is_email($email)) {
            return new WP_Error(
                'invalid_email',
                'Email invalide.',
                ['status' => 400]
            );
        }

        if (username_exists($username)) {
            return new WP_Error(
                'username_exists',
                'Ce nom d\'utilisateur existe déjà.',
                ['status' => 400]
            );
        }

        if (email_exists($email)) {
            return new WP_Error(
                'email_exists',
                'Cet email est déjà utilisé.',
                ['status' => 400]
            );
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Initialiser l'abonnement freemium par défaut
        Malagasy_User_Service::upgrade_subscription($user_id, 'freemium');

        return rest_ensure_response([
            'success' => true,
            'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.',
            'user_id' => $user_id
        ]);
    }

    /**
     * Récupère le profil de l'utilisateur
     */
    public function get_profile($request) {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $subscription = Malagasy_User_Service::get_subscription($user_id);

        return rest_ensure_response([
            'id' => $user->ID,
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'subscription' => $subscription
        ]);
    }
}
