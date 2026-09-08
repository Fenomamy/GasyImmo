# GasyImmo — Guide d'installation XAMPP

## 📋 Prérequis
- XAMPP installé (Apache + MySQL + PHP 8.0+)
- Navigateur web moderne

---

## 🚀 Installation en 4 étapes

### Étape 1 — Copier le projet
Copiez le dossier `gasyimmo/` dans :
```
C:/xampp/htdocs/gasyimmo/
```

### Étape 2 — Démarrer XAMPP
Ouvrez le **Panneau de contrôle XAMPP** et démarrez :
- ✅ **Apache**
- ✅ **MySQL**

### Étape 3 — Créer la base de données
1. Ouvrez votre navigateur et allez sur : `http://localhost/phpmyadmin`
2. Cliquez sur **"Nouvelle base de données"** → nommez-la `gasyimmo`
3. Cliquez sur l'onglet **"Importer"**
4. Sélectionnez le fichier `gasyimmo.sql` (à la racine du projet)
5. Cliquez sur **"Importer"**

### Étape 4 — Lancer l'application
Ouvrez : `http://localhost/gasyimmo/`

---

## 🔐 Accès Administrateur
| Champ       | Valeur     |
|-------------|------------|
| Identifiant | `admin`    |
| Mot de passe| `admin123` |

URL directe : `http://localhost/gasyimmo/admin/connexion.php`

---

## 📂 Structure du projet
```
gasyimmo/
├── index.php               → Redirection accueil
├── gasyimmo.sql            → Script base de données
├── README.md               → Ce fichier
│
├── config/
│   ├── connexion.php       → Connexion PDO
│   └── fonctions.php       → Fonctions utilitaires
│
├── includes/
│   ├── entete_admin.php    → Header admin + sidebar
│   ├── pied_admin.php      → Footer admin
│   ├── entete_public.php   → Header public
│   └── pied_public.php     → Footer public
│
├── admin/
│   ├── connexion.php       → Page de connexion
│   ├── deconnexion.php     → Déconnexion
│   ├── tableau_bord.php    → Dashboard + graphiques
│   ├── biens.php           → Liste biens (CRUD)
│   ├── bien_ajouter.php    → Ajouter un bien
│   ├── bien_modifier.php   → Modifier un bien
│   ├── bien_supprimer.php  → Supprimer un bien
│   ├── clients.php         → Gestion clients
│   ├── rendez_vous.php     → Gestion RDV
│   ├── transactions.php    → Liste transactions
│   └── transaction_ajouter.php → Nouvelle transaction
│
├── pages/
│   ├── accueil.php         → Page d'accueil
│   ├── biens.php           → Liste biens + filtres
│   ├── bien_detail.php     → Détail d'un bien
│   ├── rendez_vous.php     → Formulaire RDV + suivi
│   └── a_propos.php        → À propos + contact
│
├── assets/
│   ├── css/
│   │   ├── admin.css       → Styles administration
│   │   └── public.css      → Styles site public
│   └── js/
│       ├── admin.js        → JS admin (graphiques, modals)
│       └── public.js       → JS public (validation, animations)
│
└── uploads/                → Images uploadées (auto-créé)
```

---

## ✅ Fonctionnalités implémentées

### Site public
- [x] Page d'accueil avec biens en vedette
- [x] Liste des biens avec filtres (ville, statut, type) et tri (prix, superficie)
- [x] Détail d'un bien
- [x] Formulaire de rendez-vous (CIN 12 chiffres, téléphone 10 chiffres)
- [x] Suivi de rendez-vous par CIN
- [x] Page À propos + formulaire contact
- [x] Responsive (mobile, tablette, desktop)

### Administration
- [x] Connexion sécurisée (session PHP)
- [x] Tableau de bord avec statistiques + graphiques
- [x] CRUD complet des biens (avec upload photo)
- [x] Gestion clients (CRUD + validation CIN/téléphone)
- [x] Gestion rendez-vous (accepter/refuser/terminer)
- [x] Logique automatique : RDV accepté → bien réservé
- [x] Transactions (ventes & locations + mise à jour statut bien)
- [x] Protection suppression bien vendu/loué

### Sécurité
- [x] Requêtes préparées PDO
- [x] Validation côté serveur ET côté client
- [x] Protection sessions admin
- [x] Échappement des sorties HTML (XSS)

---

## 🗄️ Base de données (données de test)
- **5 clients** avec CIN et téléphone valides
- **10 biens** (maisons, appartements, terrains)
- **5 rendez-vous** (statuts variés)
- **2 transactions** (1 vente, 1 location)

---

## ⚙️ Configuration
Si votre MySQL utilise un mot de passe différent, modifiez :
```php
// config/connexion.php
define('DB_UTIL',  'root');  // votre utilisateur MySQL
define('DB_PASSE', '');      // votre mot de passe MySQL
```

---

*Projet universitaire L2 Informatique — Madagascar*
