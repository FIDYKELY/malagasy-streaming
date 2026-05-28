<?php
/**
 * User Service
 * Logique métier pour les utilisateurs
 */

if (!defined('ABSPATH')) exit;

class Malagasy_User_Service {

    /**
     * Vérifie si un utilisateur a accès à un contenu
     */
    public static function has_access($user_id, $post_id, $post_type = '') {
        $content_type = get_post_meta($post_id, '_content_access_type', true);

        // Contenu gratuit
        if ($content_type === 'freemium' || !$content_type) {
            return true;
        }

        // Contenu premium
        if ($content_type === 'premium') {
            $subscription = get_user_meta($user_id, 'subscription_type', true);
            return $subscription === 'premium';
        }

        // Contenu pay-per-view
        if ($content_type === 'payperview') {
            $purchased = get_user_meta($user_id, 'purchased_content', true);
            return is_array($purchased) && in_array($post_id, $purchased);
        }

        return false;
    }

    /**
     * Ajoute du contenu aux achats de l'utilisateur
     */
    public static function purchase_content($user_id, $content_id) {
        $purchased = get_user_meta($user_id, 'purchased_content', true);
        if (!is_array($purchased)) {
            $purchased = [];
        }

        if (!in_array($content_id, $purchased)) {
            $purchased[] = (int) $content_id;
            update_user_meta($user_id, 'purchased_content', $purchased);
        }
    }

    /**
     * Élève l'abonnement de l'utilisateur
     */
    public static function upgrade_subscription($user_id, $type = 'premium') {
        update_user_meta($user_id, 'subscription_type', $type);
    }

    /**
     * Récupère l'abonnement de l'utilisateur
     */
    public static function get_subscription($user_id) {
        return get_user_meta($user_id, 'subscription_type', true) ?: 'freemium';
    }
}
