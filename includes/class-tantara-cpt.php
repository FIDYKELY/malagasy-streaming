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
