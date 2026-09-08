-- ============================================================
-- GasyImmo — Script SQL complet
-- Base de données : gasyimmo
-- Projet universitaire L2 — Madagascar
-- ============================================================

CREATE DATABASE IF NOT EXISTS gasyimmo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gasyimmo;

-- ============================================================
-- TABLE : administrateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS administrateurs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    identifiant VARCHAR(60)  NOT NULL UNIQUE,
    mot_passe   VARCHAR(100) NOT NULL,
    nom         VARCHAR(100) NOT NULL,
    cree_le     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Identifiant : admin | Mot de passe : admin123 (pédagogique)
INSERT INTO administrateurs (identifiant, mot_passe, nom) VALUES
('admin', 'admin123', 'Administrateur GasyImmo');

-- ============================================================
-- TABLE : clients
-- ============================================================
CREATE TABLE IF NOT EXISTS clients (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    prenom     VARCHAR(100) NOT NULL,
    cin        CHAR(12)     NOT NULL UNIQUE COMMENT 'CIN : exactement 12 chiffres',
    telephone  CHAR(10)     NOT NULL        COMMENT 'Téléphone : exactement 10 chiffres',
    email      VARCHAR(150),
    adresse    TEXT,
    cree_le    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE : biens
-- ============================================================
CREATE TABLE IF NOT EXISTS biens (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(200) NOT NULL,
    type        ENUM('maison','terrain','appartement') NOT NULL,
    ville       VARCHAR(100) NOT NULL,
    adresse     TEXT,
    superficie  DECIMAL(10,2),
    nb_pieces   INT DEFAULT 0,
    etage       INT DEFAULT NULL,
    prix        DECIMAL(15,2) NOT NULL,
    statut      ENUM('disponible','reserve','vendu','loue') DEFAULT 'disponible',
    description TEXT,
    photo       VARCHAR(255)  DEFAULT NULL,
    date_ajout  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE : rendez_vous
-- ============================================================
CREATE TABLE IF NOT EXISTS rendez_vous (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    client_id    INT  NOT NULL,
    bien_id      INT  NOT NULL,
    date_visite  DATE NOT NULL,
    heure_visite TIME NOT NULL,
    message      TEXT,
    statut       ENUM('en_attente','accepte','refuse','termine') DEFAULT 'en_attente',
    cree_le      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id)  ON DELETE CASCADE,
    FOREIGN KEY (bien_id)   REFERENCES biens(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE : transactions
-- ============================================================
CREATE TABLE IF NOT EXISTS transactions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    client_id        INT  NOT NULL,
    bien_id          INT  NOT NULL,
    type_transaction ENUM('vente','location') NOT NULL,
    montant          DECIMAL(15,2) NOT NULL,
    date_transaction DATE NOT NULL,
    notes            TEXT,
    cree_le          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (bien_id)   REFERENCES biens(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE : messages_contact
-- ============================================================
CREATE TABLE IF NOT EXISTS messages_contact (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nom       VARCHAR(150) NOT NULL,
    email     VARCHAR(150) NOT NULL,
    message   TEXT NOT NULL,
    lu        TINYINT(1) DEFAULT 0,
    cree_le   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

-- Clients (CIN 12 chiffres, téléphone 10 chiffres)
INSERT INTO clients (nom, prenom, cin, telephone, email, adresse) VALUES
('Rakoto',         'Jean',   '101234567890', '0341234567', 'jean.rakoto@email.mg',      'Lot II A 47, Antananarivo'),
('Rabe',           'Marie',  '201234567891', '0339876543', 'marie.rabe@email.mg',       'Rue des Fleurs, Toamasina'),
('Rasolofo',       'Paul',   '301234567892', '0325544433', 'paul.rasolofo@email.mg',    'Analakely, Antananarivo'),
('Randrianasolo',  'Hanta',  '401234567893', '0347788899', 'hanta.rand@email.mg',       'Ivato, Antananarivo'),
('Andriantsoa',    'Luc',    '501234567894', '0331122334', 'luc.andriantsoa@email.mg',  'Mahajanga Centre');

-- Biens immobiliers
INSERT INTO biens (titre, type, ville, adresse, superficie, nb_pieces, etage, prix, statut, description) VALUES
('Belle villa avec jardin',          'maison',      'Antananarivo', 'Lot VK 12 Ambohijanaka',         250.00, 6, NULL, 180000000, 'disponible', 'Magnifique villa avec grand jardin, garage double, piscine. Quartier calme et sécurisé.'),
('Appartement moderne centre-ville', 'appartement', 'Antananarivo', 'Rue Rainandriamampandry',         85.00, 3,    4,  75000000, 'disponible', 'Appartement rénové avec vue panoramique. Cuisine équipée, 2 salles de bain.'),
('Terrain constructible Ivato',      'terrain',     'Antananarivo', 'Ivato Nord Zone C',              500.00, 0, NULL,  45000000, 'disponible', 'Terrain plat et viabilisé, proche aéroport. Idéal pour construction villa ou immeuble.'),
('Maison familiale Toamasina',       'maison',      'Toamasina',    'Quartier Morafeno',              180.00, 5, NULL,  95000000, 'reserve',    'Maison spacieuse avec véranda, 4 chambres, salon double. Proche mer.'),
('Studio étudiant Ankatso',          'appartement', 'Antananarivo', 'Ankatso Université',              35.00, 1,    2,  15000000, 'loue',       'Studio meublé idéal pour étudiant. Toutes charges comprises.'),
('Terrain bord de mer Mahambo',      'terrain',     'Toamasina',    'Plage de Mahambo',              1200.00, 0, NULL, 220000000, 'disponible', 'Exceptionnel terrain en bord de mer, vue dégagée sur l\'océan Indien.'),
('Villa de standing Mahajanga',      'maison',      'Mahajanga',    'Corniche de Mahajanga',          320.00, 7, NULL, 350000000, 'vendu',      'Villa luxueuse avec vue mer. Piscine, jardin tropical, gardien.'),
('Appartement F3 Fianarantsoa',      'appartement', 'Fianarantsoa', 'Centre Haute-Ville',              70.00, 3,    1,  40000000, 'disponible', 'Appartement calme dans quartier historique. Parquet bois, hauts plafonds.'),
('Terrain industriel Antsirabe',     'terrain',     'Antsirabe',    'Zone industrielle Nord',         2000.00, 0, NULL, 120000000, 'disponible', 'Grand terrain plat en zone industrielle, accès poids lourds.'),
('Maison thermale Antsirabe',        'maison',      'Antsirabe',    'Quartier Thermale',              150.00, 4, NULL,  85000000, 'disponible', 'Maison avec grand salon, cuisine moderne, jardin fleuri.');

-- Rendez-vous
INSERT INTO rendez_vous (client_id, bien_id, date_visite, heure_visite, message, statut) VALUES
(1, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 'Je suis très intéressé par cette villa, merci de confirmer.', 'en_attente'),
(2, 2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:30:00', 'Souhait de visite en famille.',                               'accepte'),
(3, 6, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', 'Investissement potentiel.',                                   'en_attente'),
(4, 8, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '11:00:00', 'Première visite, besoin d\'informations.',                    'en_attente'),
(5, 9, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '15:00:00', 'Pour projet industriel.',                                     'termine');

-- Transactions
INSERT INTO transactions (client_id, bien_id, type_transaction, montant, date_transaction, notes) VALUES
(3, 7, 'vente',    350000000, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Vente conclue après négociation. Acte notarié signé.'),
(2, 5, 'location',    450000, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Location mensuelle. Bail 12 mois.');
