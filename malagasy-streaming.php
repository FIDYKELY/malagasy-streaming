<?php
/**
 * Plugin Name: Streaming Malagasy
 * Description: Plugin streaming Gasy.
 * Version: 1.0
 * Author: Fidy
 */

if (!defined('ABSPATH'))
    exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-films-cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-tantara-cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-woo-sync.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-auth.php';

add_action('rest_api_init', function () {
    register_rest_route('malagasy/v1', '/film/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'malagasy_get_film_secure',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ]);

    register_rest_route('malagasy/v1', '/tantara/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'malagasy_get_tantara_secure',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ]);

    register_rest_route('malagasy/v1', '/register', [
        'methods' => 'POST',
        'callback' => 'malagasy_register_user',
        'permission_callback' => '__return_true'
    ]);
});
add_action('woocommerce_order_status_completed', 'malagasy_handle_payment');

function malagasy_handle_payment($order_id)
{
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    if (!$user_id)
        return;

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        $sku = $product->get_sku();

        // Cas 1 : Abonnement premium
        if ($sku === 'premium_monthly') {
            update_user_meta($user_id, 'subscription_type', 'premium');
            continue;
        }

        // Cas 2 : Film ou tantara à l'acte
        $content_id = $product->get_meta('_linked_content_id');
        if ($content_id) {
            $purchased = get_user_meta($user_id, 'purchased_content', true);
            if (!is_array($purchased))
                $purchased = [];
            if (!in_array($content_id, $purchased)) {
                $purchased[] = (int) $content_id;
                update_user_meta($user_id, 'purchased_content', $purchased);
            }
        }
    }
}

function malagasy_register_user($request)
{
    $params = $request->get_json_params();
    $username = sanitize_user($params['username'] ?? '');
    $email = sanitize_email($params['email'] ?? '');
    $password = $params['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        return new WP_Error('missing_fields', 'Tous les champs sont requis', ['status' => 400]);
    }
    if (!is_email($email)) {
        return new WP_Error('invalid_email', 'Email invalide', ['status' => 400]);
    }
    if (username_exists($username)) {
        return new WP_Error('username_exists', 'Ce nom d\'utilisateur existe déjà', ['status' => 400]);
    }
    if (email_exists($email)) {
        return new WP_Error('email_exists', 'Cet email est déjà utilisé', ['status' => 400]);
    }
    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        return $user_id;
    }

    return rest_ensure_response([
        'success' => true,
        'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.'
    ]);
}

function malagasy_get_film_secure($request)
{
    $post_id = (int) $request['id'];
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'film_malagasy') {
        return new WP_Error('invalid_film', 'Film introuvable', ['status' => 404]);
    }

    $user_id = get_current_user_id();
    if (!malagasy_user_has_access($user_id, $post_id, $post->post_type)) {
        return new WP_Error('no_access', 'Accès refusé. Abonnez-vous ou achetez ce contenu.', ['status' => 403]);
    }
    $subscription = get_user_meta($user_id, 'subscription_type', true) ?: 'freemium';
    $data = [
        'id' => $post->ID,
        'title' => $post->post_title,
        'realisateur' => get_post_meta($post->ID, '_film_realisateur', true),
        'annee' => get_post_meta($post->ID, '_film_annee', true),
        'duree' => get_post_meta($post->ID, '_film_duree', true),
        'licence' => get_post_meta($post->ID, '_film_licence', true),
        'user_subscription' => $subscription,
    ];

    return rest_ensure_response($data);
}
;

function malagasy_get_tantara_secure($request)
{
    $post_id = (int) $request['id'];
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'tantara_malagasy') {
        return new WP_Error('invalid_tantara', 'Tantara introuvable', ['status' => 404]);
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('unauthorized', 'Authentification requise', ['status' => 401]);
    }

    $data = [
        'id' => $post->ID,
        'title' => $post->post_title,
        'conteur' => get_post_meta($post->ID, '_tantara_conteur', true),
        'duree' => get_post_meta($post->ID, '_tantara_duree', true),
        'langue' => get_post_meta($post->ID, '_tantara_langue', true)
    ];

    return rest_ensure_response($data);
}
;

function malagasy_user_has_access($user_id, $post_id, $post_type)
{
    $content_type = get_post_meta($post_id, '_content_access_type', true);

    if ($content_type === 'freemium' || !$content_type)
        return true;

    if ($content_type === 'premium') {
        $subscription = get_user_meta($user_id, 'subscription_type', true);
        return $subscription === 'premium';
    }

    if ($content_type === 'payperview') {
        $purchased = get_user_meta($user_id, 'purchased_content', true);
        return is_array($purchased) && in_array($post_id, $purchased);
    }

    return false;
}
;

new Films_CPT();
new Films_Metabox();
new Tantara_CPT();
new Tantara_Metabox();
new Malagasy_Woo_Sync();
new Malagasy_Auth();