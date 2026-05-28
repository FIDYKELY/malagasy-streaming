<?php
/**
 * Payment Handler
 * Gestion des paiements et activation de contenu
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Payment_Handler {

    public static function handle_order_completed($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        if (!$user_id) {
            return;
        }

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $sku = $product->get_sku();

            // Cas 1 : Abonnement premium
            if ($sku === 'premium_monthly') {
                Malagasy_User_Service::upgrade_subscription($user_id, 'premium');
                continue;
            }

            // Cas 2 : Film ou tantara à l'acte
            $content_id = $product->get_meta('_linked_content_id');
            if ($content_id) {
                Malagasy_User_Service::purchase_content($user_id, $content_id);
            }
        }
    }
}
