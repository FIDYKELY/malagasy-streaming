# 🚀 Guide de Vérification Post-Migration

## ✅ Checklist de Validation

### 1. Structure des Répertoires

```bash
✅ Vérifier que tous les répertoires existent:
├── includes/Core/
├── includes/Domain/Films/
├── includes/Domain/Tantara/
├── includes/Domain/User/
├── includes/Infrastructure/WordPress/
├── includes/Infrastructure/Payment/
├── includes/Application/Controllers/
├── includes/Application/Middleware/
└── docs/

✅ Vérifier qu'aucun fichier n'a disparu
```

### 2. Routes API

Tester les endpoints suivants avec curl ou Postman:

```bash
# Auth
POST /wp-json/malagasy/v1/login
Body: { "username": "test", "password": "pass" }
Expected: 200 ou 401 (correct)

POST /wp-json/malagasy/v1/register
Body: { "username": "newuser", "email": "user@test.com", "password": "pass" }
Expected: 200 ou 400 (correct)

GET /wp-json/malagasy/v1/profile
Headers: Authorization: Bearer <token>
Expected: 200 (avec profil utilisateur)

# Catalogue
GET /wp-json/malagasy/v1/films?page=1&per_page=10
Expected: 200 (liste films)

GET /wp-json/malagasy/v1/tantara?page=1&per_page=10
Expected: 200 (liste tantara)

# Streaming
GET /wp-json/malagasy/v1/film/123
Headers: Authorization: Bearer <token>
Expected: 200 (détails film) ou 403 (pas d'accès)

GET /wp-json/malagasy/v1/film/123/stream
Headers: Authorization: Bearer <token>
Expected: 200 (URL streaming) ou 403 (pas d'accès)
```

### 3. CPT et Metabox WordPress

```bash
✅ Admin → Films
   - Vérifier que CPT "Films" existe
   - Vérifier metabox "Détails du film" existe
   - Tester ajout d'un film
   - Vérifier les champs: Réalisateur, Année, URL HLS, Durée, Licence, Type d'accès, Prix

✅ Admin → Tantara
   - Vérifier que CPT "Tantara" existe
   - Vérifier metabox "Détails du tantara" existe
   - Tester ajout d'un tantara
   - Vérifier les champs: Conteur, URL Audio, Durée, Langue, Type d'accès, Prix
```

### 4. Taxonomies

```bash
✅ Admin → Films → Genres
   - Vérifier que taxonomie existe
   - Tester ajout/édition de genre

✅ Admin → Films → Réalisateurs
   - Vérifier que taxonomie existe

✅ Admin → Tantara → Conteurs
   - Vérifier que taxonomie existe

✅ Admin → Tantara → Thèmes
   - Vérifier que taxonomie existe
```

### 5. WooCommerce Sync

```bash
✅ Créer un film en pay-per-view (access_type = payperview)
   - Vérifier qu'un produit WooCommerce est créé
   - Vérifier que le prix est correct
   - Vérifier que le produit est marqué "virtual"

✅ Modifier le film (prix)
   - Vérifier que le produit WooCommerce est mis à jour

✅ Changer le type d'accès
   - Si payperview → freemium, produit doit être dépublié
```

### 6. Paiements

```bash
✅ Tester un achat d'abonnement
   - Complèter commande avec SKU "premium_monthly"
   - Vérifier que user_meta subscription_type = "premium"

✅ Tester un achat pay-per-view
   - Complèter commande
   - Vérifier que user_meta purchased_content contient le post_id
```

### 7. Accès Utilisateur

```bash
✅ Tester un utilisateur freemium
   - GET /malagasy/v1/film/[FREEMIUM_FILM]
   - Expected: 200 ✅

✅ Tester un utilisateur freemium sur contenu premium
   - GET /malagasy/v1/film/[PREMIUM_FILM]
   - Expected: 403 ✅

✅ Tester un utilisateur premium
   - Mettre subscription_type = "premium"
   - GET /malagasy/v1/film/[PREMIUM_FILM]
   - Expected: 200 ✅
```

### 8. Fichiers Anciens

```bash
⚠️ Les anciens fichiers sont toujours présents:
   - includes/class-films-cpt.php
   - includes/class-tantara-cpt.php
   - includes/class-auth.php
   - includes/class-catalogue.php
   - includes/class-streaming.php
   - includes/class-woo-sync.php

🔍 Vérifier qu'aucun de ces fichiers n'est inclus dans:
   - malagasy-streaming.php
   - class-plugin.php
   - class-plugin-loader.php

📝 À FAIRE: Supprimer ces fichiers après validation complète
```

## 📋 Test de Régression

### Routes API qui doivent rester identiques

```php
// AVANT:
new Malagasy_Catalogue();
register_rest_route('/malagasy/v1', '/films', ...);

// APRÈS:
new Malagasy_Catalogue_Controller();
register_rest_route('malagasy/v1', '/films', ...);

✅ URL: /wp-json/malagasy/v1/films → IDENTIQUE
✅ Paramètres: page, per_page, genre → IDENTIQUE
✅ Réponse: JSON array → IDENTIQUE
```

### Métadonnées WordPress qui doivent rester identiques

```php
// AVANT:
_film_realisateur
_film_annee
_film_url_video_hls
_film_duree
_film_licence
_content_access_type
_film_prix

// APRÈS:
_film_malagasy_realisateur
_film_malagasy_annee
_film_malagasy_url_video_hls
_film_malagasy_duree
_film_malagasy_licence
_content_access_type
_film_malagasy_prix

⚠️ ATTENTION: Les clés ont changé!
   OLD: _film_realisateur
   NEW: _film_malagasy_realisateur
```

### 🚨 Action Nécessaire: Migration des Métadonnées

Si vous avez des données existantes, exécuter ce script de migration:

```php
// Temporaire: dans functions.php ou un admin-ajax endpoint
add_action('wp_ajax_migrate_film_meta', function() {
    $args = ['post_type' => 'film_malagasy', 'posts_per_page' => -1];
    $posts = get_posts($args);
    
    foreach ($posts as $post) {
        // Migrer les metas
        $realisateur = get_post_meta($post->ID, '_film_realisateur', true);
        if ($realisateur) {
            update_post_meta($post->ID, '_film_malagasy_realisateur', $realisateur);
            delete_post_meta($post->ID, '_film_realisateur');
        }
        
        // ... répéter pour chaque champ
    }
    
    wp_die('Migration complète');
});

// Appeler: /wp-admin/admin-ajax.php?action=migrate_film_meta
```

## 🔍 Debugging

### Erreur: "Class not found"

```bash
✅ Vérifier que le plugin est activé
✅ Vérifier que malagasy-streaming.php charge class-plugin.php
✅ Vérifier que class-plugin-loader.php charge tous les fichiers
✅ Vérifier les chemins de fichiers (case-sensitive sur Linux!)
```

### Routes API ne répondent pas

```bash
✅ Vérifier que les Controllers sont instanciés
✅ Vérifier que add_action('rest_api_init', ...) fonctionne
✅ Vérifier les permission_callbacks
✅ Tester avec wp-json/malagasy/v1/[endpoint]
```

### Metabox ne sauvegarde pas

```bash
✅ Vérifier wp_nonce_field et wp_verify_nonce
✅ Vérifier current_user_can('edit_post')
✅ Vérifier que save_post hook fonctionne
✅ Vérifier pas d'erreur dans wp-admin/plugins.php
```

### WooCommerce Sync ne crée pas de produits

```bash
✅ Vérifier que WooCommerce est activé
✅ Vérifier que class_exists('WooCommerce')
✅ Vérifier qu'access_type = 'payperview'
✅ Vérifier les hooks save_post_film_malagasy et save_post_tantara_malagasy
```

## 📊 Logs & Monitoring

### Activer le debugging WordPress

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Vérifier wp-content/debug.log
```

### Tester les Classes

```php
// Dans admin-ajax ou functions.php

// Tester l'autoload
if (class_exists('Malagasy_Film')) {
    error_log('✅ Malagasy_Film chargée');
} else {
    error_log('❌ Malagasy_Film non trouvée');
}

// Tester les repositories
$repo = new Malagasy_Film_Repository();
$film = $repo->find(123);
error_log('Film: ' . json_encode($film->to_array()));

// Tester les services
$has_access = Malagasy_User_Service::has_access(1, 123);
error_log('Accès: ' . ($has_access ? 'Oui' : 'Non'));
```

## ✅ Validation Finale

Une fois tous les tests passés:

1. ✅ Supprimer les anciens fichiers `includes/class-*.php`
2. ✅ Vérifier que tout fonctionne toujours
3. ✅ Committer les changements (git)
4. ✅ Documenter dans CHANGELOG.md
5. ✅ Augmenter le numéro de version (1.0 → 1.1)
6. ✅ Prêt pour les phases suivantes!

---

## 📚 Ressources

- [docs/ARCHITECTURE.md](ARCHITECTURE.md) - Architecture détaillée
- [docs/BEFORE_AFTER.md](BEFORE_AFTER.md) - Comparaison avant/après
- [MIGRATION.md](../MIGRATION.md) - Détails de migration
- [walkthrough.md](../walkthrough.md) - Guide de déploiement
