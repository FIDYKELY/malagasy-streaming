# Malagasy Streaming — Backend WordPress (Walkthrough)

## Fichiers créés

### 1. [class-favoris.php](file:///d:/Fidy/asa/malagasy-streaming/includes/class-favoris.php)
**Favoris + Progression de lecture**

| Endpoint | Méthode | Description |
|---|---|---|
| `/malagasy/v1/favoris` | `GET` | Liste des favoris (paginé, filtrable par type) |
| `/malagasy/v1/favoris` | `POST` | Ajouter un favori `{ "content_id": 123 }` |
| `/malagasy/v1/favoris/{id}` | `DELETE` | Retirer des favoris |
| `/malagasy/v1/favoris/check/{id}` | `GET` | Vérifier si un contenu est en favori |
| `/malagasy/v1/progress` | `GET` | "Continuer à regarder/écouter" (non terminés) |
| `/malagasy/v1/progress` | `POST` | Sauvegarder position `{ "content_id": 123, "position": 1200, "duration": 5400 }` |
| `/malagasy/v1/progress/{id}` | `GET` | Progression d'un contenu spécifique |
| `/malagasy/v1/progress/{id}` | `DELETE` | Supprimer la progression |

- **Tables MySQL** : `malagasy_favorites`, `malagasy_progress`
- Progression en secondes, pourcentage auto-calculé, marqué "completed" à 95%

---

### 2. [class-stats.php](file:///d:/Fidy/asa/malagasy-streaming/includes/class-stats.php)
**Tracking vues / écoutes + Dashboard admin**

| Endpoint | Méthode | Accès | Description |
|---|---|---|---|
| `/malagasy/v1/stats/track` | `POST` | Auth | Enregistrer un événement (view, play, download, complete) |
| `/malagasy/v1/stats/history` | `GET` | Auth | Historique personnel (dédupliqué par contenu) |
| `/malagasy/v1/stats/dashboard` | `GET` | Admin | Résumé global (vues, écoutes, abonnés, revenus, graphe journalier) |
| `/malagasy/v1/stats/content/{id}` | `GET` | Admin | Stats détaillées d'un contenu |
| `/malagasy/v1/stats/top` | `GET` | Admin | Top contenus (par vues/écoutes/downloads) |

- **Table MySQL** : `malagasy_stats_log` (événements horodatés avec IP, user_agent)
- Anti-spam : throttle 30s entre événements identiques
- Dashboard : revenus WooCommerce, stats quotidiennes pour graphiques

---

### 3. [class-notifications.php](file:///d:/Fidy/asa/malagasy-streaming/includes/class-notifications.php)
**Notifications Push (FCM)**

| Endpoint | Méthode | Accès | Description |
|---|---|---|---|
| `/malagasy/v1/notifications/register` | `POST` | Auth | Enregistrer token FCM `{ "token": "...", "platform": "android" }` |
| `/malagasy/v1/notifications/unregister` | `DELETE` | Auth | Supprimer un token |
| `/malagasy/v1/notifications` | `GET` | Auth | Historique des notifications |
| `/malagasy/v1/notifications/settings` | `GET/PUT` | Auth | Préférences (new_films, new_tantara, promotions) |
| `/malagasy/v1/notifications/send` | `POST` | Admin | Envoi manuel |

- **Tables MySQL** : `malagasy_push_tokens`, `malagasy_notifications`
- **Envoi automatique** quand un film ou tantara passe en statut "publish"
- FCM par lots de 1000 tokens

> [!TIP]
> Ajouter `define('MALAGASY_FCM_SERVER_KEY', 'votre_cle');` dans `wp-config.php` pour activer l'envoi réel.

---

### 4. [class-promotions.php](file:///d:/Fidy/asa/malagasy-streaming/includes/class-promotions.php)
**Publicités & Promotions**

| Endpoint | Méthode | Description |
|---|---|---|
| `/malagasy/v1/promos` | `GET` | Promotions actives (filtrable par placement/type) |
| `/malagasy/v1/promos/{id}` | `GET` | Détail d'une promo |
| `/malagasy/v1/promos/validate` | `POST` | Valider un code promo `{ "code": "GASY2026" }` |
| `/malagasy/v1/ads` | `GET` | Bannières pub pour utilisateurs freemium |
| `/malagasy/v1/ads/click` | `POST` | Tracking clic pub |

- **CPT `malagasy_promo`** avec metabox admin complète
- Types : bannière, code promo, contenu mis en avant, sponsor
- Placements : accueil, catalogue, lecteur (pré-roll), interstitiel
- Compteurs impressions + clics intégrés

---

## Fichier modifié

### [malagasy-streaming.php](file:///d:/Fidy/asa/malagasy-streaming/malagasy-streaming.php)
- `require_once` pour les 4 nouveaux fichiers
- `register_activation_hook` mis à jour (création des tables)
- Instanciation des 4 nouvelles classes

## Architecture complète du plugin

```
malagasy-streaming/
├── malagasy-streaming.php          # Point d'entrée
└── includes/
    ├── class-films-cpt.php         # CPT Films + Metabox
    ├── class-tantara-cpt.php       # CPT Tantara + Metabox
    ├── class-auth.php              # Auth (login, profil, JWT)
    ├── class-catalogue.php         # Catalogue (liste, recherche, filtres)
    ├── class-streaming.php         # URLs signées (stream + download)
    ├── class-woo-sync.php          # Sync WooCommerce (pay-per-view)
    ├── class-favoris.php           # ✅ Favoris + progression
    ├── class-stats.php             # ✅ Tracking vues/écoutes + dashboard
    ├── class-notifications.php     # ✅ Push notifications FCM
    └── class-promotions.php        # ✅ Publicités & promotions
```

> [!IMPORTANT]
> Après upload sur le serveur, **désactiver puis réactiver le plugin** pour créer les tables MySQL.
