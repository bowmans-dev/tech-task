# User Management System
## DDD / EDA
![System Diagram](./storage/app/public/screenshots/sign_in_screenshot.png)
![Create User Screenshot](./storage/app/public/screenshots/create_user_screenshot.png)
![Users List Screenshot](./storage/app/public/screenshots/users_list_screenshot.png)

---
# Set Up Guide
```bash
cp .env.example .env
```

```bash
php artisan key:generate
```
```bash
npm i
```
```bash
composer install
```

```bash
php artisan migrate
```
```bash
php artisan db:seed
```
```bash
Generates Users & Admin Login:
'email' => 'admin@example.com',
'password' => bcrypt('test1234'),
```
```bash
npm run dev
```
```bash
php artisan serve
```
#### ! Important (required to simulate password reset link email found in storage/laravel.log)
```bash
php artisan queue:work
```

---

# System Diagram
![System Diagram](./storage/app/public/screenshots/system_diagram.png)





## Backend dev tech task
### Objective
 Demonstrate your backend development skills by implementing a basic user management system.
 
### Task Description
 Build a CRUD (Create, Read, Update, Delete) application for managing users. The system should support the following functionality:
Required User Fields (for views and forms):
Name
Surname
Email
Phone
Country (selected from a predefined list)
Gender
Password
Repeat Password (for validation)
Optional Fields (not required for current implementation):
Selfie
Introduction
Additional Requirements:
Support image upload (e.g., for a user profile picture)
Enable country selection from a predefined country list

### Acceptance Criteria
- Fork the provided Git repository and implement the task within your fork
- Implement full CRUD functionality:
- Create user
- Update user
- View user details
- View user list
- Delete user
- Follow Test-Driven Development (TDD) principles
- Apply Domain-Driven Design (DDD) best practices
- Ensure code quality, readability, and maintainability
  
### Notes:
- Your submission will be evaluated based on code quality, adherence to best practices, and completeness of the task
- Thank you and good luck!