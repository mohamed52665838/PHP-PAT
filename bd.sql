
CREATE DATABASE IF NOT EXISTS patisserie_db;

-- Utiliser la base de données
USE patisserie_db;

CREATE TABLE users(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255),
    password TEXT NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role ENUM('admin', 'client','preparateur') NOT NULL DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE produits(
    id int primary key AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    prix DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE panier (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prixFinaleDeProduit DECIMAL(10, 2) NOT NULL,
    active BOOLEAN DEFAULT FALSE,
    user_id INT NOT NULL,
    status ENUM('en attente', 'en cours', 'livrée', 'annulée') NOT NULL DEFAULT 'en attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE commande_produit (
    id INT PRIMARY KEY AUTO_INCREMENT,
    panier_id INT NOT NULL,
    produit_id INT NOT NULL,
    qte INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(produit_id) REFERENCES produits(id) ON DELETE CASCADE,
    FOREIGN KEY(panier_id) REFERENCES panier(id) ON DELETE CASCADE
);

1 step

we should get the active cart by user id which is from the session

SELECT id as id_panier from panier where user_id = id AND active = true

2 step

now we can get all our orders by matching the id_panier in commande_produit table

select * from commande_produit where id_panier = id_panier