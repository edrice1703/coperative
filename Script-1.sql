CREATE TABLE utilisateurs
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(100) NOT NULL,

    prenom VARCHAR(100) NOT NULL,

    telephone VARCHAR(20) NOT NULL UNIQUE,

    email VARCHAR(150) NOT NULL UNIQUE,

    mot_de_passe VARCHAR(255) NOT NULL,

    role ENUM('ADMIN','CLIENT') NOT NULL DEFAULT 'CLIENT',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE vehicules
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    immatriculation VARCHAR(30) NOT NULL UNIQUE,

    marque VARCHAR(100) NOT NULL,

    capacite INT NOT NULL,

    etat ENUM('DISPONIBLE', 'EN_SERVICE', 'EN_PANNE')
        NOT NULL DEFAULT 'DISPONIBLE',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE chauffeurs
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(100) NOT NULL,

    prenom VARCHAR(100) NOT NULL,

    telephone VARCHAR(20) NOT NULL UNIQUE,

    numero_permis VARCHAR(50) NOT NULL UNIQUE,

    etat ENUM('DISPONIBLE', 'EN_MISSION', 'ABSENT')
        NOT NULL DEFAULT 'DISPONIBLE',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE trajets
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    depart VARCHAR(100) NOT NULL,

    arrivee VARCHAR(100) NOT NULL,

    date_depart DATE NOT NULL,

    heure_depart TIME NOT NULL,

    prix DECIMAL(10,2) NOT NULL,

    places_restantes INT NOT NULL,

    vehicule_id INT NOT NULL,

    chauffeur_id INT NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_trajet_vehicule
        FOREIGN KEY (vehicule_id)
        REFERENCES vehicules(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_trajet_chauffeur
        FOREIGN KEY (chauffeur_id)
        REFERENCES chauffeurs(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE reservations
(
    id INT AUTO_INCREMENT PRIMARY KEY,

    utilisateur_id INT NOT NULL,

    trajet_id INT NOT NULL,

    nombre_places INT NOT NULL CHECK (nombre_places > 0),

    statut ENUM('CONFIRMEE', 'ANNULEE')
        NOT NULL DEFAULT 'CONFIRMEE',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_reservation_utilisateur
        FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_reservation_trajet
        FOREIGN KEY (trajet_id)
        REFERENCES trajets(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


