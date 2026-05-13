<?php
if (!defined('ABSPATH')) exit;

class Malagasy_Woo_Sync {

    public function __construct() {
        add_action('save_post_film_malagasy',    [$this, 'sync_product'], 20, 2);
        add_action('save_post_tantara_malagasy', [$this, 'sync_product'], 20, 2);
    }

    public function sync_product($post_id, $post) {

        // Ignorer autosave, révisions, brouillons
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_status === 'auto-draft') return;
        if (!class_exists('WooCommerce')) return;

        $access_type = get_post_meta($post_id, '_content_access_type', true);

        // Seulement pour les contenus pay-per-view
        if ($access_type !== 'payperview') {
            // Si un produit existait et qu'on change de type, on peut le dépublier
            $existing = get_post_meta($post_id, '_linked_woo_product_id', true);
            if ($existing) {
                wp_update_post(['ID' => $existing, 'post_status' => 'draft']);
            }
            return;
        }

        $prix = get_post_meta($post_id, '_film_prix', true)
             ?: get_post_meta($post_id, '_tantara_prix', true)
             ?: 0;

        $existing_product_id = get_post_meta($post_id, '_linked_woo_product_id', true);

        if ($existing_product_id && get_post($existing_product_id)) {
            // Mettre à jour le produit existant
            wp_update_post([
                'ID'          => $existing_product_id,
                'post_title'  => $post->post_title,
                'post_status' => 'publish',
            ]);
            update_post_meta($existing_product_id, '_price',         $prix);
            update_post_meta($existing_product_id, '_regular_price', $prix);
        } else {
            // Créer un nouveau produit simple WooCommerce
            $product_id = wp_insert_post([
                'post_title'  => $post->post_title,
                'post_type'   => 'product',
                'post_status' => 'publish',
            ]);

            if (is_wp_error($product_id)) return;

            update_post_meta($product_id, '_price',              $prix);
            update_post_meta($product_id, '_regular_price',      $prix);
            update_post_meta($product_id, '_sold_individually',  'yes');
            update_post_meta($product_id, '_virtual',            'yes');
            update_post_meta($product_id, '_manage_stock',       'no');
            update_post_meta($product_id, '_stock_status',       'instock');
            wp_set_object_terms($product_id, 'simple', 'product_type');

            // Stocker l'ID du produit dans le film/tantara (et vice versa)
            update_post_meta($post_id,      '_linked_woo_product_id', $product_id);
            update_post_meta($product_id,   '_linked_content_id',     $post_id);
        }
    }
}
