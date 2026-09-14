CREATE DATABASE IF NOT EXISTS pet_adoption_system;
USE pet_adoption_system;

CREATE TABLE users (
    user_id INT(11) NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    country VARCHAR(100) DEFAULT 'Nepal',
    province VARCHAR(100) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','super_admin','shelter','adopter') DEFAULT NULL,
    status ENUM('pending','active','rejected') DEFAULT 'pending',
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    verification_doc VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (user_id),
    UNIQUE KEY email (email),
    KEY province (province),
    KEY country (country)
);

CREATE TABLE pets (
    pet_id INT(11) NOT NULL AUTO_INCREMENT,
    country VARCHAR(100) DEFAULT 'Nepal',
    pet_name VARCHAR(50) NOT NULL,
    species ENUM('dog','cat','bird','others') DEFAULT 'others',
    breed VARCHAR(50) DEFAULT NULL,
    age VARCHAR(50) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    adoption_status ENUM('pending','available','rejected','adopted') DEFAULT 'pending',
    current_owner_id INT(11) DEFAULT NULL,
    final_adopter_id INT(11) DEFAULT NULL,
    gender ENUM('Male','Female','Unknown') DEFAULT 'Unknown',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (pet_id),
    KEY current_owner_id (current_owner_id)
);

CREATE TABLE adoption_applications (
    app_id INT(11) NOT NULL AUTO_INCREMENT,
    applicant_id INT(11) NOT NULL,
    target_pet_id INT(11) NOT NULL,
    application_message TEXT DEFAULT NULL,
    identity_doc VARCHAR(255) DEFAULT NULL,
    app_status ENUM('pending','approved','rejected') DEFAULT 'pending',
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_viewed TINYINT(1) DEFAULT 0,
    PRIMARY KEY (app_id),
    KEY applicant_id (applicant_id),
    KEY target_pet_id (target_pet_id)
);

CREATE TABLE audit_logs (
    log_id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    KEY user_id (user_id)
);