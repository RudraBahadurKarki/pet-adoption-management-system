# 🐾 Pet Adoption Management System

A web-based **Pet Adoption Management System** developed using **PHP, MySQL/MariaDB, HTML, CSS, JavaScript, and Bootstrap** to manage pets, users, shelters, adoption applications, verification, and administrative activities.

The system provides role-based access for **Adopters, Shelters, Provincial Administrators, and Super Administrators**.

---

## 📌 Overview

The Pet Adoption Management System provides a centralized platform for managing pet adoption activities.

### The system allows users to:

* Register and log in according to their role
* Explore available pets
* View detailed pet information
* Submit adoption applications
* Upload supporting documents
* Manage pet records
* Review and process adoption applications
* Manage users and administrative records
* Track important system activities through audit logs

This project was developed as a **BCA academic project**.

---

## 👥 User Roles

### 🧑 Adopter

* Register and log in
* Explore available pets
* View pet details
* Submit adoption applications
* Upload supporting documents
* View submitted applications
* Check application status

### 🏠 Shelter

* Log in to the system
* Add new pets
* Edit pet information
* Manage listed pets
* View adoption-related information
* Review applications for their pets
* Approve or reject applications

### 🏛️ Provincial Administrator

* Manage province-related records
* Handle administrative verification activities
* Manage relevant users and shelter records

### 👨‍💼 Super Administrator

* Manage users
* Manage pet records
* Manage administrative records
* Monitor system activities
* View audit logs
* Manage provincial administration records

---

## ✨ Main Features

### 🔐 User Authentication

The system provides registration and login functionality with role-based access.

User accounts contain information such as:

* Full name
* Country
* Province
* Address
* Email
* Phone
* Password
* Role
* Account status
* Verification document

Passwords are securely stored using PHP password hashing.

### 🐕 Pet Management

Shelters can manage:

* Pet name
* Species
* Breed
* Age
* Gender
* Description
* Pet image
* Adoption status
* Current owner
* Final adopter
* Country

### 🔎 Pet Exploration

Adopters can explore available pets, filter/browse records, and view individual pet details before applying for adoption.

### 📝 Adoption Applications

Adopters can submit applications containing:

* Applicant information
* Target pet
* Application message
* Supporting identity document
* Application status
* Application date
* Viewed/unviewed state

Application statuses:

`Pending` · `Approved` · `Rejected`

### 📋 Application Review

Authorized users can review adoption applications and take appropriate action.

When an application is approved, the related pet's adoption information can also be updated.

### 📂 Document Verification

The system supports document uploads for relevant registration and adoption processes.

Private uploaded documents are excluded from the public GitHub repository.

### 📊 Audit Logging

Important system activities can be recorded through audit logs.

Each audit record contains:

* User
* Action type
* Description
* Date and time

### 🛡️ Role-Based Management

Different users receive different functionality according to their assigned role, separating adopter, shelter, and administrative operations.

---

## 🔄 System Workflow

```text
User Registration
        ↓
     Login
        ↓
  Role Verification
        ↓
 Role-Based Dashboard
        ↓
 ┌────────────┬────────────┬─────────────────┬──────────────┐
 ↓            ↓            ↓                 ↓
Adopter     Shelter    Provincial Admin   Super Admin
 ↓            ↓            ↓                 ↓
Explore      Manage     Verify/Manage      System-wide
Pets         Pets       Records            Management
 ↓            ↓            ↓                 ↓
Apply     Review Apps   Administration    Audit Logs
        \       |          |              /
         \      |          |             /
          └─────┴──────────┴────────────┘
                       ↓
                 System Records
```

---

## 🏠 Adoption Process

```text
Adopter
   ↓
Explore Pets
   ↓
View Pet Details
   ↓
Submit Adoption Application
   ↓
Application Stored
   ↓
Application Review
   ↓
 ┌───────────────┐
 ↓               ↓
Approve        Reject
 ↓               ↓
Pet Adopted    Application Rejected
```

---

## 🏗️ System Architecture

The project follows a simple three-layer web application structure.

### Presentation Layer

Responsible for the user interface and interaction.

**Technologies:**

* HTML
* CSS
* Bootstrap
* JavaScript
* PHP-generated views

### Application Layer

PHP handles the main application logic, including:

* Authentication
* Registration
* Pet management
* Adoption applications
* Application approval/rejection
* User management
* Administrative actions
* Audit logging

### Data Layer

MySQL/MariaDB stores the system's persistent data.

The project was developed and tested using **XAMPP**.

---

## 💻 Technology Stack

| Technology          | Purpose                             |
| ------------------- | ----------------------------------- |
| **PHP**             | Server-side application development |
| **MySQL / MariaDB** | Database management                 |
| **HTML**            | Page structure                      |
| **CSS**             | Styling                             |
| **JavaScript**      | Client-side functionality           |
| **Bootstrap**       | UI components and responsive design |
| **Apache**          | Local web server                    |
| **XAMPP**           | Local development environment       |

---

## 🗄️ Database

Database name:

```text
pet_adoption_system
```

### Main Tables

| Table                   | Purpose                                                    |
| ----------------------- | ---------------------------------------------------------- |
| `users`                 | Stores users, roles, account status, and user information  |
| `pets`                  | Stores pet details, images, ownership, and adoption status |
| `adoption_applications` | Stores adoption applications and their status              |
| `audit_logs`            | Stores records of important system activities              |

### User Roles

```text
admin
super_admin
shelter
adopter
```

### User Status

```text
pending
active
rejected
```

### Pet Adoption Status

```text
pending
available
rejected
adopted
```

### Application Status

```text
pending
approved
rejected
```

The current database uses indexed references between related records rather than explicit foreign-key constraints.

---

## 📁 Project Structure

```text
pet_adoption_system/
│
├── actions/
│   ├── add_pet_actions.php
│   ├── apply_action.php
│   ├── apply_adoption.php
│   ├── approve_adoption.php
│   ├── auth_action.php
│   ├── delete_pet.php
│   ├── logout.php
│   ├── register_action.php
│   ├── submit_request_action.php
│   ├── update_admin_status.php
│   ├── update_pet_action.php
│   ├── update_status.php
│   └── user_status.php
│
├── assets/
│   ├── adopter_doc/
│   ├── images/
│   │   └── pets/
│   └── shelterdocs/
│       └── verification/
│
├── config/
│   ├── db.php
│   └── pet_adoption_system.sql
│
├── includes/
│   ├── footer.php
│   └── header.php
│
├── views/
│   ├── admin/
│   ├── adopter/
│   └── shelter/
│
├── index.php
├── login.php
├── register.php
├── .gitignore
└── README.md
```

---

## 📂 Important Directories

### `actions/`

Contains PHP scripts responsible for processing application actions such as authentication, registration, pet operations, adoption applications, status updates, and user management.

### `views/`

Contains role-based pages for administrators, adopters, and shelters.

### `config/`

Contains the database configuration and SQL database structure.

### `assets/images/pets/`

Contains the pet images used by the application.

### Private Upload Directories

```text
assets/adopter_doc/
assets/shelterdocs/verification/
```

These directories are used for private uploaded documents and are excluded from the public GitHub repository using `.gitignore`.

---

## ⚙️ Installation & Setup

### Requirements

* XAMPP
* Apache
* MySQL/MariaDB
* PHP
* Modern web browser

### 1. Clone the Repository

Clone the project into the XAMPP `htdocs` directory.

```text
C:\xampp\htdocs\pet_adoption_system
```

### 2. Start XAMPP

Open XAMPP Control Panel and start:

```text
Apache
MySQL
```

### 3. Import the Database

Open **phpMyAdmin** and import:

```text
config/pet_adoption_system.sql
```

The SQL file creates:

```text
pet_adoption_system
```

### 4. Configure the Database

Database configuration is located at:

```text
config/db.php
```

Default local XAMPP configuration:

```php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "pet_adoption_system";
```

Update these values if your local database configuration is different.

### 5. Create Upload Directories

Make sure these directories exist locally:

```text
assets/adopter_doc/
assets/shelterdocs/verification/
```

Their uploaded contents should remain private and should not be committed to GitHub.

### 6. Run the Project

Open:

```text
http://localhost/pet_adoption_system/
```

---

## 🔒 Security & Privacy

The project handles user accounts and uploaded documents.

The following should **never** be committed to a public repository:

* Identity documents
* Shelter verification documents
* Personal uploaded files
* API keys
* Passwords or other secrets

The project uses PHP password hashing for user passwords.

> This project is intended for academic and learning purposes and should receive additional security hardening before production use.

---

## 🚀 Future Improvements

Possible future improvements include:

* Email notifications
* Advanced pet search and filtering
* Improved document verification
* Foreign-key relationships
* Stronger input validation and security
* Password reset functionality
* Detailed administrative reports
* Improved notification system
* Better image management
* Production deployment
* Adoption analytics and statistics

---

## 🎓 Academic Project

**Project:** Pet Adoption Management System

**Program:** Bachelor of Computer Applications (BCA)

**Purpose:** An academic project demonstrating web application development, database management, authentication, role-based access, pet management, and the pet adoption workflow.

---

## 👨‍💻 Author

**Rudra Bahadur Karki**

BCA Student · Aspiring Full-Stack Developer

### Technologies

`PHP` · `Laravel` · `JavaScript` · `Python` · `MySQL` · `Git` · `GitHub`

---

> This project was developed as a BCA academic and learning project. It demonstrates the core workflow of a pet adoption platform and can be further improved for security, scalability, validation, notifications, and production deployment.
