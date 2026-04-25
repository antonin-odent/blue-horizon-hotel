# 🏨 Blue Horizon Hotel — Site web de réservation

Projet réalisé dans le cadre du BTS SIO SLAM — 1ère année.

Site web de réservation de chambres en ligne pour l'hôtel Blue Horizon Hotel, permettant aux clients de consulter les chambres disponibles et d'effectuer une réservation enregistrée en base de données.

---
 
## Membres du groupe

Antonin ODENT-ALLET
---

##  Fonctionnalités

- Page d'accueil de présentation de l'hôtel
- Liste de toutes les chambres disponibles (données dynamiques depuis MySQL)
- Fiche détaillée de chaque chambre
- Formulaire de réservation avec enregistrement en base de données
- Page de confirmation après réservation
- Site responsive (Mobile First : 375px, 768px, 1200px)

---

##  Technologies utilisées

- HTML / CSS — Structure et style responsive
- PHP — Traitement des formulaires et connexion MySQL
- MySQL — Base de données relationnelle
- GitHub — Versioning et travail collaboratif

---

##  Prérequis

- [XAMPP](https://www.apachefriends.org/) (Apache + PHP + MySQL)
- Un navigateur web moderne (Chrome, Firefox, Edge…)
- Git

---

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/antonin-odent/blue-horizon-hotel
```

### 2. Placer le projet dans XAMPP

Copier le dossier `blue-horizon-hotel` dans :

```
C:/xampp/htdocs/   (Windows)
/Applications/XAMPP/htdocs/   (macOS)
```

### 3. Créer la base de données

1. Lancer XAMPP → démarrer Apache et MySQL
2. Ouvrir phpMyAdmin : [localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Créer une base de données nommée `blue_horizon_hotel` (encodage : `utf8mb4_general_ci`)
4. Sélectionner la base → onglet Importer → choisir le fichier `blue_horizon_hotel.sql` → Exécuter

### 4. Lancer le site

Ouvrir dans le navigateur : http://localhost/blue-horizon-hotel/

---

##  Structure du projet

```
blue-horizon-hotel/
├── index.php               ← Page d'accueil
├── chambres.php            ← Liste des chambres
├── chambre.php             ← Détail d'une chambre
├── reservation.php         ← Formulaire de réservation
├── confirmation.php        ← Page de confirmation
├── config/
│   └── database.php        ← Connexion PDO à la base de données
├── css/
│   └── style.css           ← Feuille de styles responsive
├── images/                 ← Photos des chambres
├── blue_horizon_hotel.sql  ← Script SQL de la base de données
└── README.md
```

---

##  Sécurité

- Utilisation de `htmlspecialchars()` pour tout affichage de données