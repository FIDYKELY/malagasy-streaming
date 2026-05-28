# 📚 Documentation - Malagasy Streaming Platform

Bienvenue dans la documentation de la nouvelle architecture du projet Malagasy Streaming!

## 🚀 Par Où Commencer?

### 1. **Je veux comprendre la nouvelle architecture**
→ Lire [ARCHITECTURE.md](ARCHITECTURE.md)
- Vue d'ensemble de la structure
- Les 4 couches (Core, Domain, Infrastructure, Application)
- Patterns utilisés (Repository, Entity, Service, etc.)
- Flux d'une requête API

### 2. **Je viens de l'ancienne version et je veux savoir quoi a changé**
→ Lire [BEFORE_AFTER.md](BEFORE_AFTER.md)
- Comparaison visuelle avant/après
- Avantages de la nouvelle architecture
- Statistiques de qualité de code
- Bénéfices pour la scalabilité

### 3. **Je dois valider que tout fonctionne après la migration**
→ Lire [VALIDATION.md](VALIDATION.md)
- Checklist de validation
- Tests des routes API
- Tests des CPT et Metabox
- Debugging en cas de problème

### 4. **Je dois ajouter une nouvelle fonctionnalité**
→ Lire [ARCHITECTURE.md](ARCHITECTURE.md) section "Scalabilité"
- Comment ajouter une nouvelle feature en 5 étapes
- Quelle couche pour quoi
- Patterns de réutilisation

## 📂 Structure de la Documentation

```
docs/
├── README.md              ← Tu es ici
├── ARCHITECTURE.md        # Documentation complète (START HERE!)
├── BEFORE_AFTER.md       # Comparaison visuelle
├── VALIDATION.md         # Guide de validation post-migration
├── API.md               # (À créer) Documentation des endpoints API
├── DEPLOYMENT.md        # (À créer) Guide de déploiement
└── CONTRIBUTING.md      # (À créer) Guide de contribution
```

## 🎯 Les 4 Couches de l'Architecture

### **1. Core** 
Initialisation et chargement du plugin
- Classe principale
- Autoloader

### **2. Domain**
Logique métier pure indépendante de WordPress
- Entités (Film, Tantara)
- Repositories (accès données)
- Services (logique métier)

### **3. Infrastructure**
Couche technique qui implémente WordPress
- CPT et Metabox
- WooCommerce Sync
- (Futur) Streaming HLS, DRM

### **4. Application**
API REST et points d'entrée
- Controllers (endpoints)
- Middleware (auth, validation)
- (Futur) DTO pour sérialisation

## 🔍 Localiser du Code

### Je cherche...

**Les CPT et Metabox**
→ `includes/Infrastructure/WordPress/`

**La logique métier (accès, achats, permissions)**
→ `includes/Domain/`

**Les endpoints API REST**
→ `includes/Application/Controllers/`

**L'authentification et l'autorisation**
→ `includes/Application/Middleware/`

**La synchronisation WooCommerce**
→ `includes/Infrastructure/Payment/`

**L'initialisation du plugin**
→ `includes/Core/`

## 🛠️ Outils pour Développer

### Installation Locale

```bash
# 1. WordPress en local
# 2. Activer le plugin
# 3. Tester les routes API

POST /wp-json/malagasy/v1/register
{ "username": "test", "email": "test@test.com", "password": "pass" }
```

### Fichiers Importants

| Fichier | Rôle |
|---------|------|
| `includes/Core/class-plugin.php` | Initialisation |
| `includes/Core/class-plugin-loader.php` | Autoloader |
| `includes/Domain/User/class-user-service.php` | Logique utilisateur |
| `includes/Infrastructure/Payment/class-payment-handler.php` | Paiements |
| `includes/Application/Controllers/class-streaming-controller.php` | API Streaming |

## 📚 Patterns Expliqués

### Repository Pattern
```php
$repo = new Malagasy_Film_Repository();
$films = $repo->find_all(['page' => 1, 'per_page' => 10]);
```
Isole l'accès aux données du reste du code.

### Entity Pattern
```php
$film = Malagasy_Film::from_post($post);
echo $film->title;
```
Représente un concept métier avec ses données et logique.

### Service Layer
```php
if (Malagasy_User_Service::has_access($user_id, $post_id)) {
    // Montrer le contenu
}
```
Contient la logique métier réutilisable.

### Middleware Pattern
```php
if (!Malagasy_Auth_Middleware::is_authenticated()) {
    return new WP_Error(...);
}
```
Valide et prépare les requêtes avant le contrôleur.

### Controller Pattern
```php
class Malagasy_Streaming_Controller {
    public function get_film($request) {
        // Orchestrer Domain + Infrastructure
        // Retourner réponse API
    }
}
```
Orchestre les couches et expose les API.

## 🔐 Sécurité

### Authentification
- JWT via plugin externe "JWT Authentication for REST API"
- Middleware `Malagasy_Auth_Middleware::is_authenticated()`

### Autorisation
- Vérification des droits d'accès dans `Malagasy_Auth_Middleware::verify_content_access()`
- Logique métier dans `Malagasy_User_Service::has_access()`

### Validation & Sanitization
- Input: `sanitize_*()` WordPress
- Output: `esc_*()` WordPress
- Nonces pour formulaires

## 📊 Méthodologie de Développement

### Avant d'ajouter une feature

1. ✅ Créer le ticket/task dans Notion
2. ✅ Définir les critères d'acceptation
3. ✅ Estimer le temps
4. ✅ Assigner la priorité

### Pendant le développement

1. ✅ Créer une branche `feature/nom-feature`
2. ✅ Développer dans les bonnes couches
3. ✅ Écrire des tests (futur)
4. ✅ Faire des commits significatifs
5. ✅ Pull Request avec description
6. ✅ Code review
7. ✅ Merge dans `develop`

### Après le déploiement

1. ✅ Tests en staging
2. ✅ Merge `develop` → `main`
3. ✅ Tag version
4. ✅ Déployer en production
5. ✅ Monitoring 24h
6. ✅ Documenter les changements

## 🚫 Fichiers Obsolètes

⚠️ À supprimer après validation:
- `includes/class-films-cpt.php`
- `includes/class-tantara-cpt.php`
- `includes/class-auth.php`
- `includes/class-catalogue.php`
- `includes/class-streaming.php`
- `includes/class-woo-sync.php`

## 📝 Checklists

### ✅ Avant de merger une feature

- [ ] Code écrit dans la bonne couche
- [ ] Tests passent (quand disponibles)
- [ ] Code review approuvé
- [ ] Documentation mise à jour
- [ ] Pas de régression

### ✅ Avant de déployer en production

- [ ] Tous les tests passent
- [ ] Tests smoke en staging
- [ ] Backup base de données
- [ ] Monitoring configuré
- [ ] Rollback plan préparé

## 🆘 Support & Ressources

### Questions Courantes

**Q: Où ajouter une nouvelle fonctionnalité?**
A: Suivre la structure des dossiers. Chaque feature = Domain + Infrastructure + Application

**Q: Comment tester localement?**
A: Lire VALIDATION.md pour les checklist de test

**Q: Qu'est-ce qui a changé avec les métadonnées?**
A: Les clés sont maintenant prefixées par le post_type (ex: `_film_malagasy_realisateur`)

### Ressources Externes

- [WordPress Handbook](https://developer.wordpress.org/)
- [REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Domain-Driven Design](https://en.wikipedia.org/wiki/Domain-driven_design)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)

## 📞 Contact & Questions

Pour les questions sur l'architecture:
1. Consulter d'abord ARCHITECTURE.md
2. Consulter BEFORE_AFTER.md
3. Consulter VALIDATION.md
4. Voir le code commenté dans les fichiers

---

**Version:** 1.1  
**Dernière mise à jour:** Mai 2026  
**Auteur:** Équipe CTO  

🚀 Bonne chance avec le projet!
