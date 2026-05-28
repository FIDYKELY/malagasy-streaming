# 🚀 Quick Reference Guide

**Taille:** Cette page = 2 min de lecture  
**Utilité:** Navigation rapide du projet  

## 🎯 En 30 Secondes

Le projet Malagasy Streaming a été restructurisé en **4 couches**:

```
Core              → Initialisation du plugin
    ↓
Domain            → Logique métier (Films, Utilisateurs)
    ↓
Infrastructure    → Technologie WordPress
    ↓
Application       → API REST
```

**Fichier principal:** `malagasy-streaming.php` (10 lignes)  
**Tous les fichiers:** `includes/` (19 fichiers, 8 dossiers)  

## 📂 Où Trouver Quoi?

### Je veux modifier...

| Besoin | Aller à |
|--------|---------|
| **CPT ou Metabox Films** | `includes/Infrastructure/WordPress/` |
| **CPT ou Metabox Tantara** | `includes/Infrastructure/WordPress/` |
| **Routes API /login, /films, etc** | `includes/Application/Controllers/` |
| **Vérifier l'accès utilisateur** | `includes/Domain/User/` |
| **WooCommerce Sync** | `includes/Infrastructure/Payment/` |
| **Authentification/Permissions** | `includes/Application/Middleware/` |
| **Entités Film/Tantara** | `includes/Domain/Films/` ou `Tantara/` |
| **Accès aux données Films** | `includes/Domain/Films/class-film-repository.php` |

## 🔥 5 Fichiers les Plus Importants

```
1. includes/Core/class-plugin-loader.php
   → Charge tous les fichiers automatiquement

2. includes/Domain/User/class-user-service.php
   → Logique: "Cet utilisateur a-t-il accès?"

3. includes/Application/Controllers/class-streaming-controller.php
   → API /film/123, /tantara/123, /stream

4. includes/Infrastructure/WordPress/class-films-cpt-manager.php
   → CPT, Taxonomies, Métabox Films

5. includes/Infrastructure/Payment/class-woo-sync.php
   → Synchronisation WooCommerce
```

## 🔗 Flux API: GET /malagasy/v1/film/123

```
1. Streaming_Controller::get_film()
   ↓
2. Auth_Middleware::is_authenticated()
   ↓
3. Film_Repository::find(123)
   ↓ 
4. Film::from_post($post)
   ↓
5. Auth_Middleware::verify_content_access()
   ↓
6. User_Service::has_access()
   ↓
7. Return $film->to_array()
```

**Résultat:** Réponse JSON sécurisée  

## 💾 Métadonnées WordPress

### Films
```
_film_malagasy_realisateur     (string)
_film_malagasy_annee           (int)
_film_malagasy_url_video_hls   (string)
_film_malagasy_duree           (int)
_film_malagasy_licence         (string)
_film_malagasy_prix            (int)
_content_access_type           (freemium|premium|payperview)
```

### Tantara
```
_tantara_malagasy_conteur      (string)
_tantara_malagasy_url_audio    (string)
_tantara_malagasy_duree        (int)
_tantara_malagasy_langue       (string)
_tantara_malagasy_prix         (int)
_content_access_type           (freemium|premium|payperview)
```

## 🧪 Tester une Route API

```bash
# Inscription
curl -X POST http://localhost/wp-json/malagasy/v1/register \
  -H "Content-Type: application/json" \
  -d '{"username":"test","email":"test@test.com","password":"test"}'

# Connexion
curl -X POST http://localhost/wp-json/malagasy/v1/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test"}'

# Récupérer profil (authentifié)
curl -X GET http://localhost/wp-json/malagasy/v1/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Lister films
curl -X GET "http://localhost/wp-json/malagasy/v1/films?page=1&per_page=10"

# Détails film (authentifié)
curl -X GET http://localhost/wp-json/malagasy/v1/film/123 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 🎨 Ajouter une Nouvelle Feature

### Exemple: Ajouter "Favoris"

#### Étape 1: Entité Domain
```php
// includes/Domain/Favorites/class-favorite.php
class Malagasy_Favorite {
    public $user_id;
    public $content_id;
}
```

#### Étape 2: Repository
```php
// includes/Domain/Favorites/class-favorite-repository.php
class Malagasy_Favorite_Repository {
    public function add($user_id, $content_id) { }
    public function remove($user_id, $content_id) { }
    public function get_all($user_id) { }
}
```

#### Étape 3: Service
```php
// includes/Domain/Favorites/class-favorite-service.php
class Malagasy_Favorite_Service {
    public static function add_to_favorites($user_id, $content_id) { }
}
```

#### Étape 4: Infrastructure (Metabox)
```php
// includes/Infrastructure/WordPress/class-favorites-metabox.php
class Malagasy_Favorites_Metabox extends Malagasy_Metabox_Manager {
    // UI pour les favoris
}
```

#### Étape 5: Controller (API)
```php
// includes/Application/Controllers/class-favorites-controller.php
class Malagasy_Favorites_Controller {
    public function add($request) {
        $service = new Malagasy_Favorite_Service();
        return $service->add_to_favorites($user_id, $content_id);
    }
}
```

#### Étape 6: Mettre à jour le Loader
```php
// includes/Core/class-plugin-loader.php
$this->load_file($includes_dir . '/Domain/Favorites/class-favorite.php');
// ... etc
```

**Voilà!** Nouvelle feature prête 🎉

## 🚨 Si Quelque Chose Ne Fonctionne Pas

### Route API 404
```
✅ Vérifier que le Controller est dans class-plugin-loader.php
✅ Vérifier la route: 'malagasy/v1' pas '/malagasy/v1'
✅ Vérifier le permission_callback
```

### Metabox ne s'affiche pas
```
✅ Vérifier que la Metabox est dans class-plugin-loader.php
✅ Vérifier le post_type
✅ Vérifier le nonce
```

### WooCommerce Sync ne crée pas de produits
```
✅ Vérifier que WooCommerce est activé
✅ Vérifier access_type = 'payperview'
✅ Vérifier les hooks save_post
```

### "Class not found" error
```
✅ Vérifier le chemin du fichier
✅ Vérifier la casse (case-sensitive sur Linux!)
✅ Vérifier que le fichier est inclus dans le Loader
```

## 📊 Structure Mémoire

```
0.1 KB:  class-plugin.php
0.3 KB:  class-plugin-loader.php

2.5 KB:  class-cpt-manager.php (abstraite)
0.6 KB:  class-films-cpt-manager.php
0.6 KB:  class-tantara-cpt-manager.php

2.0 KB:  class-metabox-manager.php (abstraite)
0.7 KB:  class-films-metabox.php
0.7 KB:  class-tantara-metabox.php

0.8 KB:  class-film.php
0.9 KB:  class-film-repository.php
0.8 KB:  class-tantara.php
0.9 KB:  class-tantara-repository.php
0.7 KB:  class-user-service.php

1.1 KB:  class-auth-middleware.php
1.2 KB:  class-woo-sync.php
0.8 KB:  class-payment-handler.php

2.5 KB:  class-auth-controller.php
1.8 KB:  class-catalogue-controller.php
2.2 KB:  class-streaming-controller.php

─────────────────────────
Total: ~35 KB bien organisé
```

## 🔑 Conventions Rapides

### Nommage Classes
```php
Malagasy_Films_CPT_Manager          // Infrastructure
Malagasy_Film                       // Domain Entity
Malagasy_Film_Repository            // Domain Repository
Malagasy_User_Service               // Domain Service
Malagasy_Streaming_Controller       // Application
Malagasy_Auth_Middleware            // Application
```

### Nommage Métadonnées
```
_{post_type}_{field_name}
_film_malagasy_realisateur
_tantara_malagasy_conteur
_content_access_type (partagé)
```

### Nommage Routes API
```
/malagasy/v1/login
/malagasy/v1/register
/malagasy/v1/films
/malagasy/v1/film/{id}
/malagasy/v1/film/{id}/stream
```

## 🎯 Objectifs Futurs

- [ ] Phase 4: Streaming HLS → `Infrastructure/Streaming/`
- [ ] Phase 5: Paiements → `Infrastructure/Payment/` (avancé)
- [ ] Phase 6: Notifications → `Application/` (nouvel endpoint)
- [ ] Tests unitaires → `tests/` (futur)
- [ ] Analytics → `Domain/Analytics/` + `Application/Analytics/`

---

**Besoin de plus de détails?**  
→ Lire `docs/ARCHITECTURE.md` (20 pages)  

**Besoin de valider?**  
→ Lire `docs/VALIDATION.md` (10 pages)  

**Besoin de comparer?**  
→ Lire `docs/BEFORE_AFTER.md` (15 pages)
