<?php
/**
 * Auth Middleware
 * Vérifie l'authentification des requêtes API
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Auth_Middleware {

    /**
     * Vérifie si l'utilisateur est authentifié
     */
    public static function is_authenticated() {
        return is_user_logged_in();
    }

    /**
     * Vérifie si l'utilisateur a accès à un contenu
     */
    public static function verify_content_access($user_id, $post_id) {
        if (!Malagasy_User_Service::has_access($user_id, $post_id)) {
            return new WP_Error(
                'no_access',
                'Accès refusé. Abonnez-vous ou achetez ce contenu.',
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Vérifie les paramètres de la requête
     */
    public static function validate_params($request, $required_params = []) {
        $params = $request->get_json_params();

        foreach ($required_params as $param) {
            if (empty($params[$param])) {
                return new WP_Error(
                    'missing_param',
                    sprintf('Paramètre manquant: %s', $param),
                    ['status' => 400]
                );
            }
        }

        return true;
    }
}
