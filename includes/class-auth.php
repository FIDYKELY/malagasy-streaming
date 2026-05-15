<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Malagasy_Auth {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( 'malagasy/v1', '/login', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'handle_login' ],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route( 'malagasy/v1', '/profile', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'handle_profile' ],
            'permission_callback' => function() {
                return is_user_logged_in();
            }
        ]);
    }

    public function handle_login( $request ) {
        $params   = $request->get_json_params();
        $username = sanitize_user( $params['username'] ?? '' );
        $password = $params['password'] ?? '';

        if ( empty( $username ) || empty( $password ) ) {
            return new WP_Error( 'missing_fields', 'Nom d\'utilisateur et mot de passe requis.', ['status' => 400] );
        }

        $user = wp_authenticate( $username, $password );

        if ( is_wp_error( $user ) ) {
            return new WP_Error( 'invalid_credentials', 'Identifiants incorrects.', ['status' => 401] );
        }

        $subscription = get_user_meta( $user->ID, 'subscription_type', true );
        if ( empty( $subscription ) ) {
            $subscription = 'freemium';
        }

        return rest_ensure_response( [
            'success'      => true,
            'user_id'      => $user->ID,
            'display_name' => $user->display_name,
            'email'        => $user->user_email,
            'subscription' => $subscription,
            'message'      => 'Connexion réussie. Utilisez /jwt-auth/v1/token pour obtenir votre JWT.'
        ] );
    }

    public function handle_profile( $request ) {
        $user_id = get_current_user_id();
        $user    = get_userdata( $user_id );
        
        $subscription = get_user_meta( $user_id, 'subscription_type', true ) ?: 'freemium';

        return rest_ensure_response( [
            'id'           => $user->ID,
            'display_name' => $user->display_name,
            'email'        => $user->user_email,
            'subscription' => $subscription
        ] );
    }
}