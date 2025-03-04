# Projet PHP - Ecom Kimono

## 📌 Introduction
Bienvenue dans **Ecom Kimono**, une plateforme e-commerce permettant aux utilisateurs de consulter des produits, les ajouter à leur panier et passer commande. Ce projet a été réalisé dans le cadre de ma formation en développement web.

## 🎯 Objectifs
- Développer une **application web dynamique** en PHP.
- Implémenter une **authentification sécurisée**.
- Concevoir une **gestion de panier intuitive**.
- Assurer une **expérience utilisateur fluide** avec des filtres et de l'autocomplétion.
- Mettre en place une **base de données robuste** et sécurisée.

## 🚀 Fonctionnalités Principales
### ✅ Gestion des Utilisateurs
- Inscription et connexion sécurisées (hashage des mots de passe avec bcrypt).
- Gestion des rôles (utilisateur/admin).
- Authentification persistante avec sessions.

### ✅ Gestion des Produits
- Affichage des produits avec tri et filtres.
- Recherche avancée avec autocomplétion AJAX.
- Page détaillée du produit avec sélection de la taille et de la couleur.
- Ajout et suppression de produits par les administrateurs.

### ✅ Gestion du Panier
- Ajout de produits avec taille, couleur et quantité.
- Stockage temporaire en session.
- Page dédiée au panier avec résumé des achats.
- Passage de commande (simulation).

### ✅ Sécurité
- Protection contre les injections SQL (requêtes préparées PDO).
- Protection contre les attaques XSS (htmlspecialchars sur toutes les entrées utilisateur).
- Gestion des sessions sécurisées avec validation CSRF.

## 🏗 Architecture du Projet
```
📂 ecom-kimono/
├── 📂 admin/
│   ├── admin.php          # Tableau de bord administrateur
│   ├── modif.php          # Modification d'un produit
│   ├── delete.php         # Suppression d'un produit (à ajouter si nécessaire)
│
├── 📂 lib/
│   ├── db.php             # Connexion à la base de données
│   ├── graphic_logout.php # Gestion graphique de la déconnexion
│   ├── register_login.php # Gestion de l'inscription et connexion
│   ├── url.php            # Centralisation des URLs
│
├── 📂 public/
│   ├── add_to_cart.php    # Ajout d'un produit au panier
│   ├── cart.php           # Affichage et gestion du panier
│   ├── index.php          # Page d'accueil de la boutique
│   ├── login.php          # Page de connexion utilisateur
│   ├── logout.php         # Déconnexion de l'utilisateur
│   ├── produit.php        # Détails d'un produit
│   ├── register.php       # Page d'inscription
│   ├── remove_from_cart.php # Suppression d'un produit du panier
│   ├── search.php         # Recherche de produits
│
├── 📂 sql/
│   ├── rty.sql            # Base de données (nom à renommer pour plus de clarté, ex: `database.sql`)
│
├── 📂 uploads/
│   ├── 67c76b1641453_kimono_bleu.jpg  # Image produit - Kimono bleu
│   ├── 67c76826afb2e_kimono_blanc.webp # Image produit - Kimono blanc
│
├── README.md              # Documentation du projet


## 🗄 Base de Données
### 📌 Tables Principales
- **users** : Gestion des utilisateurs (id, username, email, password, role)
- **products** : Produits en vente (id, title, description, price, stock, image, size, color, tags)
- **cart** : Panier temporaire (user_id, product_id, size, color, quantity)
- **orders** : Commandes passées (user_id, order_date, status)

## ⚙ Installation et Configuration
### 1️⃣ Prérequis
- PHP 7.4+
- MySQL 5.7+
- Apache avec module **mod_rewrite** activé
- Composer (gestionnaire de dépendances PHP)

### 2️⃣ Installation
1. **Cloner le projet**
```bash
git clone https://github.com/Aiglator/RTY.git
cd ecom-kimono
```

## 🔍 Utilisation
### 🎯 Fonctionnalités pour un utilisateur
1. S'inscrire ou se connecter.
2. Rechercher un produit avec autocomplétion.
3. Ajouter un produit au panier avec taille/couleur.
4. Modifier le panier et finaliser la commande.

### 🔑 Fonctionnalités pour un administrateur
1. Ajouter/modifier/supprimer des produits.
2. Gérer les commandes.

## 🛠 Technologies Utilisées
- **Backend** : PHP 7+ (PDO, Sessions, Authentification)
- **Base de Données** : MySQL (ORM PDO)
- **Frontend** : HTML5, CSS3 (Tailwind), JavaScript (jQuery, AJAX)
- **Sécurité** : Hashage Bcrypt, Protection CSRF/XSS
- **Outils** : Git, Composer

## 📌 Auteurs & Contributions
- **Rayan Chattaoui** - Développeur/chef de projet (register_login.php,login/register.php/admin/modif.php/sql database)
- **Thomas** : index.php/search.php/add_to_cart.php
- **Yanis** : cart.php/remove_from_cart.php/logout.php/graphic_logout.php


---
📌 Ce projet est un défi technique et une opportunité d’apprentissage ! 🚀 Si vous avez des questions, n’hésitez pas à contribuer ou à me contacter.

