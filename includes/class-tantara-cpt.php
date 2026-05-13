<?php

if(!defined('ABSPATH')) exit;

class Tantara_CPT{
    public function __construct(){
        add_action('init', [$this, 'register_tantara_cpt']);
    }

    public function register_tantara_cpt(){
        $labels = [
            'name' => 'Tantara',
            'singular_name' => 'Tantara',
            'menu_name' => 'Tantara',
            'add_new' => 'Ajouter',
            'add_new_item' => 'Ajouter un nouveau tantara',
            'edit_item' => 'Modifier un tantara',
            'new_item' => 'Nouveau tantara',
            'view_item' => 'Voir le tantara',
            'all_items' => 'Tous les tantara'
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-format-audio',
            'has_archive' => true,
            'rewrite' => ['slug'=>'tantara'],
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true
        ];
        register_post_type('tantara_malagasy', $args);

        register_taxonomy('tantara_conteur', 'tantara_malagasy', [
            'label' => 'Conteurs',
            'hierachical' => false,
            'show_in_rest' => true,
            'show_admin_column' => true
        ]);

        register_taxonomy('tantara_theme', 'tantara_malagasy', [
            'label' => 'Thèmes',
            'hierarchical' => true,
            'show_in_rest' => true,
            'show_admin_column' => true
        ]);

        add_filter('rest_prepare_tantara_malagasy', [$this, 'add_custom_field_to_rest'], 10,3);
        }
        public function add_custom_field_to_rest($response, $post, $request){
            $fields = [
                'conteur',
                'url_audio',
                'duree',
                'langue'
            ];
            foreach($fields as $field){
                $meta_key = '_tantara_' . $field;
                $value = get_post_meta($post->ID, $meta_key, true);
                if($field === "url_audio"){
                    $value = $value ? esc_url_raw($value) : '';
                }
            $response->data['_meta_' . $field] = $value;
            }
            return $response;
        }
}

class Tantara_Metabox{
    public function __construct(){
        add_action('add_meta_boxes', [$this, 'register_metabox']);
        add_action('save_post', [$this, 'save_metabox']);
    }

    public function register_metabox(){
        add_meta_box(
            'tantara_details',
            'Détails du tantara',
            [$this, 'render_metabox'],
            'tantara_malagasy',
            'normal',
            'default'
        );
    }

    public function render_metabox($post){
        wp_nonce_field('save_tantara_meta', 'tantara_meta_nonce');

        $conteur = get_post_meta($post->ID, '_tantara_conteur', true);
        $url_audio = get_post_meta($post->ID, '_tantara_url_audio', true);
        $duree = get_post_meta($post->ID, '_tantara_duree', true);
        $langue = get_post_meta($post->ID, '_tantara_langue', true);
        $prix = get_post_meta($post->ID, '_tantara_prix', true);
        $access_type = get_post_meta($post->ID, '_content_access_type', true);
        ?>

         <p>
            <label>Conteur</label><br>
            <input type="text" name="tantara_conteur"
                   value="<?php echo esc_attr($conteur); ?>"
                   style="width:100%;">
        </p>

        <p>
            <label>URL HLS (.m3u8)</label><br>
            <input type="url" name="tantara_url_audio"
                   value="<?php echo esc_attr($url_audio); ?>">
        </p>

        <p>
            <label>Durée (minutes)</label><br>
            <input type="number" name="tantara_duree"
                   value="<?php echo esc_attr($duree); ?>">
        </p>

        <p>
            <label>Langue</label><br>
            <input type="text" name="tantara_langue"
                   value="<?php echo esc_attr($langue); ?>"
                   style="width:100%;">
        </p>

        <p>
            <label>Type d'accès</label><br>
            <select name="content_access_type" style="width:100%;">
                <option value="freemium" <?php selected($access_type, 'freemium'); ?>>Gratuit (avec pub)</option>
                <option value="premium" <?php selected($access_type, 'premium'); ?>>Réservé aux abonnés</option>
                <option value="payperview" <?php selected($access_type, 'payperview'); ?>>À l'acte</option>
            </select>
        </p>

        <p>
            <label>Prix (Ar) – Pay-per-view uniquement</label><br>
            <input type="number" name="tantara_prix"
                   value="<?php echo esc_attr($prix); ?>"
                   style="width:100%;">
        </p>

        <?php
    }
    public function save_metabox($post_id){
        if(!isset($_POST['tantara_meta_nonce']) || !wp_verify_nonce($_POST['tantara_meta_nonce'], 'save_tantara_meta')){
            return;
        }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = [
            'tantara_conteur',
            'tantara_url_audio',
            'tantara_duree',
            'tantara_langue'
        ];

        if (isset($_POST['content_access_type'])) {
            update_post_meta($post_id, '_content_access_type', sanitize_text_field($_POST['content_access_type']));
        }

        if (isset($_POST['tantara_prix'])) {
            update_post_meta($post_id, '_tantara_prix', absint($_POST['tantara_prix']));
        }

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                if ($field === 'tantara_url_audio') {
                    $value = sanitize_url($value);
                } elseif (in_array($field, ['tantara_duree'])) {
                    $value = absint($value);
                } else {
                    $value = sanitize_text_field($value);
                }
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
    }
}
