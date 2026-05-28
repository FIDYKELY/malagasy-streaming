<?php
/**
 * Base Metabox Manager
 * Classe abstraite pour la gestion centralisée des metabox
 */

if (!defined('ABSPATH')) exit;

abstract class Malagasy_Metabox_Manager {

    protected $post_type;
    protected $metabox_id;
    protected $fields = [];

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_metabox']);
        add_action('save_post', [$this, 'save_metabox']);
    }

    /**
     * Enregistre la metabox
     */
    public function register_metabox() {
        add_meta_box(
            $this->metabox_id,
            $this->get_metabox_title(),
            [$this, 'render_metabox'],
            $this->post_type,
            'normal',
            'default'
        );
    }

    /**
     * Rend la metabox
     */
    public function render_metabox($post) {
        wp_nonce_field('save_' . $this->post_type . '_meta', $this->post_type . '_meta_nonce');

        foreach ($this->fields as $field_key => $field_config) {
            $meta_value = get_post_meta($post->ID, '_' . $this->post_type . '_' . $field_key, true);
            $this->render_field($field_key, $field_config, $meta_value);
        }

        $access_type = get_post_meta($post->ID, '_content_access_type', true);
        $prix = get_post_meta($post->ID, '_' . $this->post_type . '_prix', true);
        ?>
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
            <input type="number" name="<?php echo $this->post_type; ?>_prix"
                   value="<?php echo esc_attr($prix); ?>"
                   style="width:100%;">
        </p>
        <?php
    }

    /**
     * Sauvegarde la metabox
     */
    public function save_metabox($post_id) {
        if (!isset($_POST[$this->post_type . '_meta_nonce']) ||
            !wp_verify_nonce($_POST[$this->post_type . '_meta_nonce'], 'save_' . $this->post_type . '_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sauvegarder les champs personnalisés
        foreach ($this->fields as $field_key => $field_config) {
            $field_name = $this->post_type . '_' . $field_key;
            if (isset($_POST[$field_name])) {
                $value = $this->sanitize_field($field_key, $_POST[$field_name]);
                update_post_meta($post_id, '_' . $field_name, $value);
            }
        }

        // Sauvegarder le type d'accès
        if (isset($_POST['content_access_type'])) {
            update_post_meta($post_id, '_content_access_type', sanitize_text_field($_POST['content_access_type']));
        }

        // Sauvegarder le prix
        if (isset($_POST[$this->post_type . '_prix'])) {
            update_post_meta($post_id, '_' . $this->post_type . '_prix', absint($_POST[$this->post_type . '_prix']));
        }
    }

    /**
     * Rend un champ
     */
    protected function render_field($key, $config, $value) {
        $field_name = $this->post_type . '_' . $key;
        $label = $config['label'] ?? ucfirst($key);
        $type = $config['type'] ?? 'text';

        echo '<p>';
        echo '<label>' . esc_html($label) . '</label><br>';

        switch ($type) {
            case 'text':
                echo '<input type="text" name="' . esc_attr($field_name) . '"
                       value="' . esc_attr($value) . '"
                       style="width:100%;">';
                break;

            case 'url':
                echo '<input type="url" name="' . esc_attr($field_name) . '"
                       value="' . esc_url($value) . '"
                       style="width:100%;">';
                break;

            case 'number':
                echo '<input type="number" name="' . esc_attr($field_name) . '"
                       value="' . esc_attr($value) . '">';
                break;

            case 'textarea':
                echo '<textarea name="' . esc_attr($field_name) . '"
                        style="width:100%; height:100px;">' . esc_textarea($value) . '</textarea>';
                break;
        }

        echo '</p>';
    }

    /**
     * Nettoie un champ selon son type
     */
    protected function sanitize_field($key, $value) {
        $config = $this->fields[$key] ?? [];
        $type = $config['type'] ?? 'text';

        switch ($type) {
            case 'url':
                return sanitize_url($value);
            case 'number':
                return absint($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Méthodes à implémenter
     */
    abstract protected function get_metabox_title();
}
