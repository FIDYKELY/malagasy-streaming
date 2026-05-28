# Architecture du Projet Malagasy Streaming Platform

## 📋 Vue d'Ensemble

Ce projet subit une restructuration professionnelle pour suivre une architecture en couches inspirée de DDD (Domain-Driven Design) et des principes SOLID.

## 🏗️ Structure des Répertoires

```
malagasy-streaming/
│
├── malagasy-streaming.php              # Point d'entrée du plugin
├── walkthrough.md                      # Guide de déploiement
│
├── includes/
│   │
│   ├── Core/                           # Initialisation du plugin
│   │   ├── class-plugin.php            # Classe principale
│   │   └── class-plugin-loader.php     # Autoloading des classes
│   │
│   ├── Domain/                         # Logique métier pure
│   │   ├── Films/
│   │   │   ├── class-film.php          # Entité Film
│   │   │   └── class-film-repository.php # Accès données Films
│   │   │
│   │   ├── Tantara/
│   │   │   ├── class-tantara.php       # Entité Tantara
│   │   │   └── class-tantara-repository.php # Accès données Tantara
│   │   │
│   │   ├── User/
│   │   │   └── class-user-service.php  # Logique utilisateur
│   │   │
│   │   └── Payment/                    # (Réservé pour futures phases)
│   │
│   ├── Infrastructure/                 # Couche technique
│   │   ├── WordPress/
│   │   │   ├── class-cpt-manager.php   # Base CPT abstraite
│   │   │   ├── class-films-cpt-manager.php
│   │   │   ├── class-tantara-cpt-manager.php
│   │   │   ├── class-metabox-manager.php # Base Metabox abstraite
│   │   │   ├── class-films-metabox.php
│   │   │   └── class-tantara-metabox.php
│   │   │
│   │   ├── Streaming/                  # (Pour futures phases)
│   │   │   └── ...
│   │   │
│   │   └── Payment/
│   │       ├── class-woo-sync.php      # Sync CPT ↔ WooCommerce
│   │       └── class-payment-handler.php # Gestion paiements
│   │
│   ├── Application/                    # Couche API REST
│   │   ├── Controllers/
│   │   │   ├── class-auth-controller.php
│   │   │   ├── class-catalogue-controller.php
│   │   │   └── class-streaming-controller.php
│   │   │
│   │   ├── Middleware/
│   │   │   └── class-auth-middleware.php
│   │   │
│   │   └── DTO/                        # (Pour futures phases)
│   │       └── ...
│   │
│   └── Utils/                          # Utilitaires (pour futures phases)
│       ├── class-logger.php
│       ├── class-validator.php
│       └── class-error-handler.php
│
├── admin/                              # Interface WordPress admin (future)
├── assets/                             # CSS, JS, images
├── languages/                          # Traductions i18n
├── tests/                              # Tests unitaires (future)
├── docs/                               # Documentation
├── composer.json                       # Dépendances PHP (future)
└── README.md                           # Documentation générale
```

## 🎯 Principes d'Architecture

### 1. **Séparation des Responsabilités (SOLID)**

#### **Core**
- Initialisation du plugin
- Chargement des classes
- Hooks d'activation/désactivation

#### **Domain** (Logique métier pure)
- **Entités** : `Film`, `Tantara` (représentations métier)
- **Repositories** : Accès aux données (abstrait la source)
- **Services** : Logique métier (`UserService` pour accès, achats)
- **Indépendant** de WordPress et de l'infrastructure

#### **Infrastructure** (Couche technique)
- **WordPress** : CPT, Metabox, Taxonomies
- **Payment** : WooCommerce, passerelles paiement
- **Streaming** : HLS, URLs signées, DRM
- Adaptateurs techniques qui utilisent le Domain

#### **Application** (API)
- **Controllers** : Endpoints REST (`/login`, `/films`, `/stream`)
- **Middleware** : Authentification, validation, permissions
- **DTO** : Data Transfer Objects (sérialisation API)
- Orchestration du Domain + Infrastructure

#### **Utils** (Réutilisable)
- Logger, Validator, ErrorHandler
- Fonctions transversales

### 2. **Pattern Repository**

Les Repositories isolent l'accès aux données et permettent de changer la source sans modifier la logique métier.

```php
// Domain/Films/class-film-repository.php
class Malagasy_Film_Repository {
    public function find($id) { /* ... */ }
    public function find_all($criteria) { /* ... */ }
    public function count($criteria) { /* ... */ }
}

// Application/Controllers/class-streaming-controller.php
$repository = new Malagasy_Film_Repository();
$film = $repository->find($id);
```

### 3. **Entités (Entities)**

Les Entités représentent les concepts métier avec leurs propriétés.

```php
// Domain/Films/class-film.php
class Malagasy_Film {
    public $id;
    public $title;
    public $realisateur;
    public $duree;
    public $access_type;
    
    public static function from_post($post) { /* ... */ }
    public function is_free() { /* ... */ }
    public function is_premium() { /* ... */ }
}
```

### 4. **Services (Business Logic)**

Les Services contiennent la logique métier complexe.

```php
// Domain/User/class-user-service.php
class Malagasy_User_Service {
    public static function has_access($user_id, $post_id) { /* ... */ }
    public static function purchase_content($user_id, $content_id) { /* ... */ }
}
```

### 5. **Controllers (API)**

Les Controllers gèrent les requêtes REST et orchestrent Domain + Infrastructure.

```php
// Application/Controllers/class-streaming-controller.php
class Malagasy_Streaming_Controller {
    private $film_repository;
    
    public function get_film($request) {
        $film = $this->film_repository->find($id);
        
        $access = Malagasy_Auth_Middleware::verify_content_access($user_id, $id);
        if (is_wp_error($access)) return $access;
        
        return rest_ensure_response($film->to_array());
    }
}
```

## 📡 Flux d'une Requête API

```
Request (Flutter)
    ↓
API Router (/malagasy/v1/film/123)
    ↓
Controller::action()
    ├─ Middleware::validate() (auth, perms)
    ├─ Repository::find() (fetch data)
    ├─ Service::check() (business logic)
    ├─ Infrastructure::process() (technical)
    └─ Response
    ↓
Response JSON (Flutter)
```

### Exemple: GET /malagasy/v1/film/123

```
1. Malagasy_Streaming_Controller::get_film()
2. Malagasy_Auth_Middleware::is_authenticated()
3. Malagasy_Auth_Middleware::verify_content_access()
   └─ Malagasy_User_Service::has_access()
4. Malagasy_Film_Repository::find()
5. Malagasy_Film::from_post() (convert to entity)
6. Response avec $film->to_array()
```

## 🔄 Flux de Paiement

```
WooCommerce Order Completed
    ↓
Hook: woocommerce_order_status_completed
    ↓
Malagasy_Payment_Handler::handle_order_completed()
    ├─ Get product SKU
    │   ├─ If "premium_monthly"
    │   │   └─ Malagasy_User_Service::upgrade_subscription()
    │   └─ Else (pay-per-view)
    │       └─ Malagasy_User_Service::purchase_content()
    │           └─ Update user_meta purchased_content[]
    │
    └─ Contenu déverrouillé
```

## 🚀 Flux d'Initialisation

```
1. WordPress charge les plugins
2. malagasy-streaming.php inclus
3. class-plugin.php initialisé
4. Malagasy_Streaming_Plugin::__construct()
   ├─ Define constants
   ├─ Require class-plugin-loader.php
   └─ Malagasy_Plugin_Loader::__construct()
      ├─ load_classes() (tous les files)
      ├─ initialize_infrastructure()
      │  ├─ new Malagasy_Films_CPT_Manager()
      │  ├─ new Malagasy_Tantara_CPT_Manager()
      │  ├─ new Malagasy_Films_Metabox()
      │  ├─ new Malagasy_Tantara_Metabox()
      │  └─ new Malagasy_Woo_Sync()
      ├─ initialize_application()
      │  ├─ new Malagasy_Auth_Controller()
      │  ├─ new Malagasy_Catalogue_Controller()
      │  └─ new Malagasy_Streaming_Controller()
      └─ setup_hooks()
         └─ add_action('woocommerce_order_status_completed', ...)
```

## 🔐 Sécurité

### Authentification
- Middleware: `Malagasy_Auth_Middleware::is_authenticated()`
- Vérifie `is_user_logged_in()`
- JWT via plugin JWT Auth externe

### Autorisation
- Middleware: `Malagasy_Auth_Middleware::verify_content_access()`
- Service: `Malagasy_User_Service::has_access()`
- Vérifie le `subscription_type` ou `purchased_content`

### Sanitization
- Input: `sanitize_user()`, `sanitize_email()`, etc.
- Output: `esc_attr()`, `esc_url()`, etc.
- Metabox: Nonces WordPress (`wp_verify_nonce()`)

## 🧪 Testabilité

Cette architecture rend les tests faciles grâce à:

1. **Dependency Injection**
   ```php
   $controller = new Malagasy_Streaming_Controller(
       $film_repository,
       $auth_middleware
   );
   ```

2. **Services séparés**
   ```php
   $result = Malagasy_User_Service::has_access($user_id, $post_id);
   // Testable indépendamment
   ```

3. **Repositories abstraits**
   ```php
   interface FilmRepositoryInterface {
       public function find($id);
   }
   ```

## 📈 Scalabilité

### Facile à ajouter de nouvelles fonctionnalités:

```
Nouvelle fonctionnalité "Favoris"?
1. Domain/Favorites/class-favorite.php (entité)
2. Domain/Favorites/class-favorite-repository.php (data)
3. Domain/Favorites/class-favorite-service.php (logique)
4. Infrastructure/WordPress/class-favorite-metabox.php (UI)
5. Application/Controllers/class-favorite-controller.php (API)
```

### Cache & Performance:

```php
// Facile à ajouter dans le Repository
public function find_all($criteria) {
    $cache_key = 'films_' . md5(json_encode($criteria));
    
    if ($cached = wp_cache_get($cache_key)) {
        return $cached;
    }
    
    // Fetch from DB...
    wp_cache_set($cache_key, $result, '', 1 HOUR_IN_SECONDS);
    
    return $result;
}
```

## 🔄 Migrations depuis l'ancienne structure

### Avant (Monolithe)
```
class-films-cpt.php → CPT + Metabox mélangées
class-auth.php → Routes API + logique métier mélangées
class-catalogue.php → Routes API + requêtes DB
```

### Après (Architecture en couches)
```
CPT registration           → Infrastructure/WordPress/
Metabox rendering         → Infrastructure/WordPress/
Film logic                → Domain/Films/
User access rules         → Domain/User/
API routes                → Application/Controllers/
Authentication check      → Application/Middleware/
```

## 📚 Conventions de Code

### Nommage des Classes

```
Infrastructure:  Malagasy_Films_CPT_Manager
Domain:          Malagasy_Film
Repository:      Malagasy_Film_Repository
Service:         Malagasy_User_Service
Controller:      Malagasy_Streaming_Controller
Middleware:      Malagasy_Auth_Middleware
```

### Nommage des Fonctions

```php
// Services
Malagasy_User_Service::has_access()
Malagasy_User_Service::purchase_content()

// Repository methods
$repo->find($id)
$repo->find_all($criteria)
$repo->count($criteria)

// Entity converters
Malagasy_Film::from_post($post)
$film->to_array()
```

### Nommage des Meta Keys

```
// Pattern: _{post_type}_{field}
_film_malagasy_realisateur
_film_malagasy_annee
_tantara_malagasy_conteur
_tantara_malagasy_url_audio

// Access type (partagé)
_content_access_type (valeurs: freemium, premium, payperview)

// User meta
subscription_type (valeurs: freemium, premium)
purchased_content (array de post_id)
```

## 🚀 Prochaines Phases

### Phase 2 (Actuellement en cours)
- ✅ Structure Infrastructure/WordPress (CPT, Metabox)
- ✅ Structure Domain (Film, Tantara, User, Payment)
- ✅ Structure Application (Controllers, Middleware)
- ⏳ Tests unitaires pour chaque couche

### Phase 3 (Réservé)
- Infrastructure/Streaming/ (HLS, URL signing)
- Infrastructure/Payment/ (passerelles paiement additionnelles)
- Utils/ (Logger, Validator, ErrorHandler)

### Phase 4 (Réservé)
- Admin Dashboard components
- Email notifications
- Advanced logging

## 📖 Documentation

Voir aussi:
- [walkthrough.md](../../walkthrough.md) - Guide de déploiement
- [README.md](../../README.md) - Documentation générale

## ✅ Checklist Architecture

- [x] Structure en couches (Core, Domain, Infrastructure, Application)
- [x] CPT abstraits et réutilisables
- [x] Metabox abstraites et réutilisables
- [x] Entités Domain (Film, Tantara)
- [x] Repositories pour accès données
- [x] UserService pour logique utilisateur
- [x] Controllers pour API
- [x] Middleware pour auth/permissions
- [x] WooCommerce Sync
- [x] Payment Handler
- [ ] Tests unitaires
- [ ] Logger centralisé
- [ ] Validator centralisé
- [ ] DTO pour API
- [ ] Documentation API (Swagger)
- [ ] CI/CD pipeline
