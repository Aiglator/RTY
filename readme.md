# 📌 Ecom Kimono - Documentation du Projet

## 📌 Introduction
Bienvenue dans **Ecom Kimono**, une plateforme e-commerce développée en PHP permettant aux utilisateurs d'explorer des produits, de les ajouter à leur panier et de finaliser leurs achats.

## 🎯 Objectifs
- Développer une **application web dynamique** en PHP avec une gestion de session sécurisée.
- Mettre en place une **authentification robuste**.
- Concevoir une **gestion de panier efficace**.
- Sécuriser la plateforme contre les attaques courantes (SQL Injection, XSS, CSRF, etc.).
- Implémenter une **base de données performante**.

## 🚀 Fonctionnalités Principales
### ✅ Authentification & Gestion des Sessions
- Inscription et connexion sécurisées.
- Hashage des mots de passe avec Bcrypt.
- Gestion des rôles (utilisateur/admin).
- Vérification CSRF pour protéger les actions utilisateur.

### ✅ Gestion des Produits
- Affichage des produits avec tri et filtres.
- Recherche avancée avec autocomplétion AJAX.
- Ajout, modification et suppression de produits (admin).

### ✅ Gestion du Panier
- Ajout et suppression de produits.
- Stockage des articles en session.
- Interface utilisateur optimisée pour la gestion du panier.

### ✅ Sécurité
- Utilisation de requêtes préparées pour prévenir les injections SQL.
- Protection XSS via `htmlspecialchars()`.
- Protection CSRF avec jetons de session.

---

## 🏗 Structure du Projet
```
📂 ecom-kimono/
├── 📂 admin/
│   ├── admin.php          # Tableau de bord admin
│   ├── modif.php          # Modification des produits
│   ├── stock.php          # Gestion des stocks
│
├── 📂 lib/
│   ├── db.php             # Connexion à la base de données
│   ├── register_login.php # Gestion de l'authentification et des sessions
│   ├── url.php            # Centralisation des URLs
│
├── 📂 public/
│   ├── add_to_cart.php    # Ajout de produits au panier
│   ├── cart.php           # Gestion du panier
│   ├── checkout.php       # Finalisation de l'achat
│   ├── index.php          # Page d'accueil
│   ├── login.php          # Connexion utilisateur
│   ├── logout.php         # Déconnexion utilisateur
│   ├── produit.php        # Détails du produit
│   ├── profil.php         # Gestion du profil utilisateur
│   ├── register.php       # Inscription
│   ├── search.php         # Recherche de produits
│   ├── order_success.php  # Confirmation de commande
│
├── 📂 sql/
│   ├── rty.sql            # Script de base de données
│
├── 📂 uploads/            # Stockage des images produits
│
├── README.md              # Documentation du projet
```

## 🗄 Base de Données
### 📌 Tables Principales
- **users** : Utilisateurs (id, username, email, password, role)
- **products** : Produits (id, title, description, price, stock, image, size, color, tags)
- **cart** : Panier (user_id, product_id, size, color, quantity)
- **orders** : Commandes passées (user_id, order_date, status)

## ⚙ Installation & Configuration
### 1️⃣ Prérequis
- PHP 7.4+
- MySQL 5.7+
- Apache avec **mod_rewrite** activé

### 2️⃣ Installation
1. **Cloner le projet**
```bash
git clone https://github.com/Aiglator/RTY.git
cd ecom-kimono
```
2. **Importer la base de données**
```sql
mysql -u root -p < sql/rty.sql
```

## 🛠 Technologies Utilisées
- **Backend** : PHP 7+ (PDO, Sessions, Authentification)
- **Base de Données** : MySQL
- **Frontend** : HTML5, CSS3 (Tailwind), JavaScript (jQuery, AJAX)
- **Sécurité** : Bcrypt, CSRF Tokens, Validation des entrées

## 📌 Répartition des Contributions
- **Rayan Chattaoui** : `register_login.php`, `login.php`, `register.php`, `db.php`, `url.php`, `SQL`, `admin.php`, `modif.php`, `stock.php`
- **Thomas** : `index.php`, `search.php`, `add_to_cart.php`, `order_success.php`, `clear_cart.php`
- **Yanis** : `cart.php`, `remove_from_cart.php`, `logout.php`, `graphic_logout.php`, `produit.php`

## 🎉 Contributeurs
| [<img src="https://github.com/Vtom7.png" width="100px"><br><sub>@Vtom7</sub>](https://github.com/Vtom7) | [<img src="https://github.com/yascodev.png" width="100px"><br><sub>@yascodev</sub>](https://github.com/yascodev) |
|:-:|:-:|

## 📌 Contact & Contributions
Si vous souhaitez contribuer ou signaler un bug, n'hésitez pas à nous contacter ! 🚀
