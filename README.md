README


Integration of Telehealth & E-Clinic System for Patient Management

## Overview
The **Telehealth & E-Clinic System** is a secure and user-friendly platform designed to integrate telehealth and e-clinic functionalities, enabling seamless healthcare delivery. This system provides an intuitive interface for patients, doctors, administrators, pharmacists, and other healthcare professionals to access and manage services such as video consultations, appointment scheduling, laboratory test results, secure messaging, and more.

The platform adheres to strict healthcare data standards, ensuring privacy, scalability, and high performance for a diverse user base.

---

## Features
### Key Modules:
1. **Login System**:
   - Secure authentication with hashed passwords.
   - Role-Based Access Control (RBAC) for personalized dashboards and access levels.
   - Real-time updates on user login status.

2. **Registration**:
   - Dual registration for patients and staff.
   - Automated unique ID generation for users.
   - Profile picture upload and validation.

3. **Appointment Management**:
   - Book, view, and manage appointments with doctors and specialists.
   - Dynamic time slot availability to avoid scheduling conflicts.
   - Notifications for appointment reminders.

4. **Waiting Room**:
   - Virtual queue management with real-time status updates.
   - Department-specific queues for streamlined workflows.

5. **Messaging System**:
   - Secure messaging with threading and real-time notifications.
   - Message drafting and inbox management.
   - File attachments for easy communication.

6. **Video Consultations**:
   - Integrated Jitsi video conferencing for face-to-face consultations.
   - Real-time chat during sessions.
   - Session scheduling and management.

7. **Laboratory Module**:
   - Test orders and result management.
   - Real-time updates for pending and completed tests.

8. **Pharmacy Module**:
   - Prescription tracking and inventory management.
   - Notifications for low stock and expired medications.

9. **Sidebar Navigation**:
   - Responsive, role-based design for easy navigation.
   - Customizable shortcuts and dynamic notifications.

---

## Technologies Used
- **Frontend**: HTML, CSS (Tailwind), JavaScript
- **Backend**: PHP (Core application logic)
- **Database**: MySQL (Secure storage of user data, medical records, appointments, and prescriptions)
- **Video Conferencing**: Jitsi Meet
- **Server**: XAMPP (Apache, PHP, MySQL)

---

## Installation and Setup
### Prerequisites:
- XAMPP or any PHP-compatible server with MySQL.
- A modern web browser (Google Chrome, Microsoft Edge, etc.).

### Steps:

1. Place the project folder in your XAMPP `htdocs` directory.
2. Start XAMPP and activate the Apache and MySQL modules.
3. Import the database:
   - Open `phpMyAdmin` and create a database named `administrator`.
   - Import the `administratorr.sql` file ninto the `administrator` database.
5. Update database credentials in the `config.php` file:
   ```php
   $DATABASE_HOST = 'localhost';
   $DATABASE_USER = 'root';
   $DATABASE_PASS = '';
   $DATABASE_NAME = 'administrator';
   ```
6. Open your browser and navigate to:
   ```
   http://localhost/my clinic
   ```

---


---

## Future Enhancements
- **Multi-Factor Authentication (MFA)** for improved security.
- **AI-Driven Scheduling** for appointment optimization.
- **Mobile Application** support using Jitsi SDK for video consultations.
- **Accessibility Improvements** with voice-to-text and text-to-speech features.

---



## Authors
Kingsley Ugonna Aguagwa

---

Acknowledgments
- Jitsi Meet for video conferencing integration.
- Tailwind CSS for the responsive design framework.


--- 

This README serves as a comprehensive guide to understanding, installing, and running the Telehealth & E-Clinic System.