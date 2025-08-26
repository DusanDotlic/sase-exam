# SASE Exam Project – Notes App

A secure notes web app built for the SASE exam.  
Implements **users ↔ notes** relation, JWT auth with access & refresh tokens, and HTTPS with a self-signed certificate.

---

## Features
- Relational MySQL database with `users` and `notes` tables
- User authentication with hashed passwords
- JWT authentication: short-lived **access tokens** + long-lived **refresh tokens**
- Frontend auto-refresh on token expiration
- CRUD API for personal notes (user can only access their own notes)
- Served over **HTTPS** with self-signed SSL certificate

---

## Requirements
- PHP 8.1+ with Composer
- MySQL / MariaDB
- XAMPP or Apache with SSL enabled
- Git

---

## Setup

1. **Clone repo**
   ```bash
   git clone https://github.com/YOURNAME/sase-exam.git
   cd sase-exam
