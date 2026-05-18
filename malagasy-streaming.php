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
require_once plugin_dir_path(__FILE__) . 'includes/class-catalogue.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-streaming.php';


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
new Malagasy_Catalogue();
new Malagasy_Streaming();