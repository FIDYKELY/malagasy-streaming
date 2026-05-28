<?php
/**
 * WooCommerce Sync Manager
 * Synchronisation entre les CPT et WooCommerce
 */

if (!defined('ABSPATH')) exit;

class Malagasy_Woo_Sync {

    public function __construct() {
        add_action('save_post_film_malagasy', [$this, 'sync_product'], 20, 2);
        add_action('save_post_tantara_malagasy', [$this, 'sync_product'], 20, 2);
    }

    /**
     * Synchronise le contenu avec un produit WooCommerce
     */
    public function sync_product($post_id, $post) {
        // Ignorer autosave, révisions, brouillons
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if ($post->post_status === 'auto-draft') {
            return;
        }
        if (!class_exists('WooCommerce')) {
            return;
        }

        $access_type = get_post_meta($post_id, '_content_access_type', true);

        // Seulement pour les contenus pay-per-view
        if ($access_type !== 'payperview') {
            $this->unpublish_product($post_id);
            return;
        }

        $post_type = $post->post_type;
        $prix = get_post_meta($post_id, '_' . $post_type . '_prix', true) ?: 0;

        $existing_product_id = get_post_meta($post_id, '_linked_woo_product_id', true);

        if ($existing_product_id && get_post($existing_product_id)) {
            $this->update_product($existing_product_id, $post->post_title, $prix);
        } else {
            $product_id = $this->create_product($post, $prix);
            if (!is_wp_error($product_id)) {
                update_post_meta($post_id, '_linked_woo_product_id', $product_id);
                update_post_meta($product_id, '_linked_content_id', $post_id);
            }
        }
    }

    /**
     * Crée un produit WooCommerce
     */
    private function create_product($post, $prix) {
        $product_id = wp_insert_post([
            'post_title' => $post->post_title,
            'post_type' => 'product',
            'post_status' => 'publish',
        ]);

        if (is_wp_error($product_id)) {
            return $product_id;
        }

        $this->set_product_meta($product_id, $prix);
        wp_set_object_terms($product_id, 'simple', 'product_type');

        return $product_id;
    }

    /**
     * Met à jour un produit WooCommerce
     */
    private function update_product($product_id, $title, $prix) {
        wp_update_post([
            'ID' => $product_id,
            'post_title' => $title,
            'post_status' => 'publish',
        ]);

        $this->set_product_meta($product_id, $prix);
    }

    /**
     * Définit les métadonnées du produit
     */
    private function set_product_meta($product_id, $prix) {
        update_post_meta($product_id, '_price', $prix);
        update_post_meta($product_id, '_regular_price', $prix);
        update_post_meta($product_id, '_sold_individually', 'yes');
        update_post_meta($product_id, '_virtual', 'yes');
        update_post_meta($product_id, '_manage_stock', 'no');
        update_post_meta($product_id, '_stock_status', 'instock');
    }

    /**
     * Dépublie un produit WooCommerce
     */
    private function unpublish_product($post_id) {
        $existing = get_post_meta($post_id, '_linked_woo_product_id', true);
        if ($existing) {
            wp_update_post(['ID' => $existing, 'post_status' => 'draft']);
        }
    }
}
