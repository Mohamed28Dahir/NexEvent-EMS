# 🎊 Event Management System (EMS)

[![PHP Version](https://img.shields.io/badge/PHP-8.x-777bb4.svg?style=flat-square&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1.svg?style=flat-square&logo=mysql)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC.svg?style=flat-square&logo=tailwind-css)](https://tailwindcss.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)

A comprehensive, state-of-the-art web application designed for seamless event coordination, user registration management, and administrative oversight. This platform offers a robust solution for organizational event handling with a focus on intuitive user experience and high-performance architecture.

## Project Overview

The **Event Management System (EMS)** is a multi-role platform that bridges the gap between event organizers and attendees. It provides a centralized hub where administrators can curate events and manage participants, while users enjoy a streamlined interface to discover and register for upcoming activities.

### Core Objectives:
- **Efficiency**: Automate the registration and approval workflow.
- **Transparency**: Provide real-time statistics and status tracking for all stakeholders.
- **Accessibility**: Deliver a responsive design that works across all modern devices.

## How It Works

The system operates through a structured lifecycle that ensures clarity and control at every stage:

1.  **Event Creation**: Administrators use the **Admin Module** to create and list new events with detailed information.
2.  **Discovery & Registration**: Users browse the **User Portal** to find events of interest and submit registration requests.
3.  **Approval Workflow**: Registrations land in a pending queue where Administrators can review, approve, or reject them based on capacity or eligibility.
4.  **Real-time Monitoring**: 
    - **Users** track their registration status (Approved/Pending/Rejected) on their personal dashboard.
    - **Admins** monitor overall system health, user growth, and event popularity through dynamic charts and metrics.
5.  **Account Management**: Both roles have dedicated portals for profile customization and security management.

## Technology Stack

Built using a modern web stack to ensure reliability, security, and scalability.

| Layer | Technology | Description |
| :--- | :--- | :--- |
| **Backend** | PHP 8.x | Core server-side logic and session management. |
| **Database** | MySQL | Relational data storage for users, events, and registrations. |
| **Frontend** | Bootstrap 5.3 | Responsive UI framework for a professional look and feel. |
| **Styling** | Custom Vanilla CSS | Tailored themes (Indigo for Users, Slate for Admins). |
| **Visualizations**| Chart.js | Interactive bar and doughnut charts for data insights. |
| **Icons** | FontAwesome 6 | High-quality vector icons for enhanced navigation. |
| **Typography** | Inter (Google) | Clean, highly readable professional typeface. |

## Project Architecture

```text
event-management-system/
├── admin/                  # Administrative Panel (Management & Analytics)
├── auth/                   # Authentication Logic (Secure Login/Register)
├── user/                   # User Member Portal (Discovery & Participation)
├── config/                 # System Configurations (Database Connections)
├── assets/                 # Global Static Assets (Styles & Resources)
├── includes/               # Shared Components (Headers, Footers, Utilities)
├── vendor/                 # Dependency Management (Composer)
└── database.sql            # Core Database Schema
```

## Installation Guide

Setup the project locally using environment like XAMPP, WAMP, or MAMP:

1.  **Deploy**: Clone or copy the project into your server's root directory (e.g., `htdocs/`).
2.  **Database Configuration**:
    - Access **phpMyAdmin** and create a database named `event_management`.
    - Import the provided `database.sql` file.
3.  **Environment Setup**: Verify `config/db.php` matches your local database credentials.
4.  **Launch**: Navigate to `http://localhost/event-management-system`.

## Contributors & Credits

This project was a collaborative effort, bringing together academic mentorship and technical execution.

- **Muz'ab Bashir**: Senior Developer & Idea Architect. Muz'ab initiated the project concept and provided the foundational codebase. As a senior developer with multiple active projects, he entrusted the completion of this system to his student and peer.
- **Mohamed Dahir Osman**: Developer & Student. Mohamed took over the project from Muz'ab Bashir, implementing the final features, refining the architecture, and bringing the system to its finished state.

*Developed with a commitment to quality and professional excellence.*
