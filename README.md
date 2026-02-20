# Centralized Document Archiving System (CDAS)

The **Centralized Document Archiving System (CDAS)** is a web-based archiving platform designed for the **MARINA Office**. It facilitates the organization, digitalization, and archiving of physical documents to support agency-wide paperless initiatives and digital transformation goals.

## 🚀 System Overview
CDAS runs in a local environment (localhost) and provides a secure, structured way to manage digital records. It ensures that documents are easily searchable, traceable, and protected by unit-level access controls.

### Development Stack
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP
- **Database:** MySQL
- **IDE:** Visual Studio Code
- **Server Environment:** XAMPP (Apache & MySQL)

---

## 👥 System Users & Roles
The system supports three distinct account types:

1. **Admin (Record Custodian):** Full oversight of the digital archive, user management, and auditing authority.
2. **Head of Unit:** Supervisor for specific departments; approves or denies document-related requests (e.g., printing).
3. **User (Employee):** Staff members restricted to their designated Office or Unit, responsible for managing their unit's digital library.

---

## ✨ Features

### 🛠 Admin (Record Custodian)
- **Dashboard:** Overview of total archives, registered users, file distribution per unit, and system storage status.
- **User Management:** Full CRUD operations for employee accounts and unit assignments.
- **Global Archive Oversight:** Ability to view all documents for auditing and monitor deletion logs.
- **Reporting:** Generate reports on total digitized records.

### 🏢 Head of Unit
- **Approval System:** Receives real-time "pings" for document printing requests.
- **Authorization:** Grants or denies permission to maintain security and paperless targets.

### 👤 User (Employee)
- **Unit Dashboard:** Restricted view showing only files relevant to the user's specific office.
- **Document CRUD:** Upload (PDF, DOCX, IMG), edit metadata, and archive/delete outdated files.
- **Advanced Search:** Filter files by name, date, or type.
- **Workflow Integration:** 
  - **Printable Option:** Triggers a request to the Head of Unit.
  - **Edit Trigger:** Allows downloading `.doc` files for local modification.

---

## 📊 Database Structure
The system uses a relational architecture with the following core tables:

- `users`: Stores user credentials, roles, and unit associations.
- `units`: Defines different departments (e.g., Manpower, Legal, STCW).
- `documents`: Stores file metadata, paths, and ownership information.
- `print_requests`: Manages the "ping" system for printing approvals.

---

## 🛠 Setup & Installation

Follow these steps to set up the project locally:

### 1. Prerequisites
- Install **XAMPP** (or any server with Apache and MySQL).
- Install **Visual Studio Code** (optional, recommended for development).

### 2. Database Configuration
1. Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. Go to `http://localhost/phpmyadmin/`.
3. Create a new database named `cdas_db`.
4. Import the `cdas_database.sql` file provided in the project root.

### 3. Project Deployment
1. Clone this repository or copy the project files to your XAMPP installation directory:
   - `C:\xampp\htdocs\cdas`
2. Configure your database connection in `includes/db.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'cdas_db');
   ```

### 4. Running the System
- Open your browser and navigate to `http://localhost/cdas`.

---

## 🔒 Security & Digitalization
- **Data Privacy:** Strict unit-level isolation (Users cannot see files from other units).
- **Traceability:** Detailed logs of who uploaded/modified files and when.
- **Controlled Printing:** Print-on-demand workflow reduces physical waste and ensures authorized access to sensitive documents.
