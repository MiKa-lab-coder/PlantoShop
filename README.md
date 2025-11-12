# PlantoShop

Bienvenue sur PlantoShop, une application web complète de commerce électronique spécialisée dans la vente de plantes.

## Introduction

Ce projet a été développé dans le cadre de la rédaction d'un dossier professionnel pour la formation de développeur web full stack,
servant d'exemple concret des compétences acquises en matière de conception, développement et déploiement d'applications web modernes.

## Table des matières

- [PlantoShop](#plantoshop)
  - [Table des matières](#table-des-matières)
  - [Description du Projet](#description-du-projet)
  - [Fonctionnalités](#fonctionnalités)
  - [Technologies Utilisées](#technologies-utilisées)
  - [Prérequis](#prérequis)
  - [Installation](#installation)
  - [Utilisation](#utilisation)
  - [Identifiants de Test](#identifiants-de-test)
  - [Gestion des Images](#gestion-des-images)
  - [Licence](#licence)

## Description du Projet

PlantoShop est une plateforme e-commerce moderne pour la vente de plantes.
Elle offre une expérience utilisateur fluide pour l'achat et la gestion de produits,
ainsi qu'une interface d'administration complète.
Le projet est construit avec une architecture robuste utilisant Symfony pour le backend API et React pour le frontend,
le tout orchestré avec Docker.

## Fonctionnalités

### Pour les Clients
-   Parcourir un catalogue de plantes et les rechercher.
-   Ajouter des plantes à un panier et gérer les quantités.
-   Passer des commandes sécurisées.
-   Consulter l'historique de leurs commandes et laisser des avis sur les plantes achetées.
-   Gérer leur profil utilisateur.

### Pour les Administrateurs
-   Un panneau d'administration dédié pour gérer l'ensemble du catalogue.
-   Effectuer des opérations CRUD (Créer, Lire, Mettre à jour, Supprimer) sur les plantes, les utilisateurs et les commandes.
-   Uploader des images pour les plantes directement via l'interface.

## Technologies Utilisées

### Backend
-   **Symfony 6.x** (PHP)
-   **PostgreSQL** (Base de données relationnelle)
-   **MongoDB** (Base de données NoSQL pour les avis)
-   **JWT Authentication**

### Frontend
-   **React 18+**
-   **Vite**
-   **Tailwind CSS**
-   **React Router DOM**

### Infrastructure
-   **Docker & Docker Compose**

## Prérequis

Assurez-vous d'avoir les éléments suivants installés sur votre machine :
-   [Docker Desktop](https://www.docker.com/products/docker-desktop) (inclut Docker Engine et Docker Compose)
-   [Node.js](https://nodejs.org/) (avec npm)

## Installation

Suivez ces étapes pour configurer et lancer le projet en local :

1.  **Clonez le dépôt :**
    ```bash
    git clone https://github.com/votre-utilisateur/PlantoShop.git
    cd PlantoShop
    ```

2.  **Configurez l'environnement :**
    Créez un fichier `.env.local` à la racine du projet.
    Ce fichier contiendra les variables d'environnement spécifiques à votre installation locale 
    (par exemple, les identifiants de base de données).

3.  **Construisez et lancez les conteneurs Docker :**
    ```bash
    docker compose up -d --build
    ```

4.  **Installez les dépendances Composer (Backend) :**
    ```bash
    docker compose exec php composer install
    ```

5.  **Installez les dépendances npm (Frontend) :**
    ```bash
    docker compose exec react npm install
    ```

6.  **Générez les clés JWT :**
    ```bash
    docker compose exec php bin/console lexik:jwt:generate-key
    ```

7.  **Exécutez les migrations de base de données (PostgreSQL) :**
    ```bash
    docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
    ```

8.  **Chargez les données initiales (Seeding) :**
    ```bash
    docker compose exec php bin/console app:seed-database
    ```

9.  **Lancez le serveur de développement React :**
    Dans un nouveau terminal, naviguez dans le dossier `react/` et lancez le serveur de développement :
    ```bash
    cd react
    npm run dev
    ```

## Utilisation

Une fois toutes les étapes d'installation terminées :
-   Le **Frontend React** sera accessible via votre navigateur à l'adresse : `http://localhost:5173`
-   Le **Backend Symfony** (API) est accessible via : `http://localhost`
-   **MailHog** (pour visualiser les emails envoyés par l'application) est accessible à : `http://localhost:8025`

## Identifiants de Test

Pour tester l'application, vous pouvez utiliser les identifiants suivants (créés par le seeding) :
-   **Admin:** `admin@plantoshop.com` / `adminpass`
-   **Utilisateur:** `user@plantoshop.com` / `userpass`

## Gestion des Images

### Images de Seed
Les images utilisées pour le seeding initial sont stockées dans `public/images/seed/` et sont suivies par Git.

### Images Uploadées
Les images uploadées par les administrateurs sont stockées dans `public/uploads/plant_images/`.
Ce dossier est **ignoré par Git** (`.gitignore`) car il contient des données générées par l'application.


## Licence

Ce projet est sous licence MIT.
