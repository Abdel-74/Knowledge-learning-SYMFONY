# Knowledge Learning - Plateforme e-learning

## 📖 Description  
Knowledge Learning est une plateforme e-learning permettant aux utilisateurs d'acheter et suivre des formations en ligne.  
Le projet a été développé en **Symfony** avec une architecture **MVC** et une base de données relationnelle.

---

## ⚙️ Prérequis  
Avant d'installer et d'exécuter ce projet, assurez-vous d'avoir installé les éléments suivants :

- [PHP 8.1+](https://www.php.net/downloads)
- [Composer](https://getcomposer.org/download/)
- [Symfony CLI](https://symfony.com/download)
- [MySQL 5.7+ ou MariaDB](https://www.mysql.com/)
- [Git](https://git-scm.com/)

---

## 🚀 Installation

### 1️⃣ **Cloner le projet**
```bash
git clone https://github.com/ton-utilisateur/ton-repo.git
cd ton-repo
```

### 2️⃣ **Installer les dépendances PHP**
```bash
composer install
```

### 3️⃣ **Configurer les variables d'environnement**  
Modifier les paramètres de connexion à la base de données :

Dans `.env`, ajuster la ligne suivante avec vos identifiants MySQL :
```
DATABASE_URL="mysql://root:motdepasse@127.0.0.1:3306/knowledge_learning"
```

### 4️⃣ **Créer la base de données et exécuter les migrations**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5️⃣ **Charger les jeux de données (Fixtures)**
```bash
php bin/console doctrine:fixtures:load
```

---

## 🎬 Lancer le projet

### Démarrer le serveur Symfony :
```bash
symfony server:start
```
Ou avec PHP directement :
```bash
php -S 127.0.0.1:8000 -t public
```

Le projet sera accessible sur **http://127.0.0.1:8000**

---

## 🛠️ Tests  
Exécuter les tests unitaires avec :
```bash
php bin/phpunit
```

---

## 🏗️ Technologies utilisées  
- Symfony 6+  
- Twig (templating)  
- Doctrine ORM  
- MySQL  
- Bootstrap  

---

## 📜 Licence  
Ce projet est sous licence MIT.  
