# CareerCraft – AI-Driven Career Planner & Resume Analyzer

CareerCraft is a full-stack web application designed to help students and job seekers analyze their resume, identify skill gaps, and generate personalized career roadmaps using AI. The platform integrates Google Gemini API for intelligent analysis and provides a secure, user-friendly dashboard.

## 🚀 Features
- AI-based Resume Analysis (Gemini API)
- Skill Gap Detection & Personalized Career Suggestions
- Secure Authentication (Login/Signup)
- OTP-Based Password Reset using PHPMailer (SMTP)
- Resume Upload & PDF Parsing (smalot/pdfparser)
- Interactive Dashboard with Progress Tracking
- Responsive UI with Modern Design

## 🛠 Technologies Used
**Frontend:** HTML, CSS, JavaScript, AJAX, JSON  
**Backend:** PHP, MySQL, Apache (XAMPP)  
**AI Integration:** Google Gemini API  
**Email Services:** PHPMailer (SMTP)  
**Libraries:** Composer, smalot/pdfparser  
**Security:** Sessions, Password Hashing, Prepared Statements  
**Tools:** VS Code, Git, GitHub

## 📦 Installation
1. Clone the repository  
2. Import the SQL file into phpMyAdmin  
3. Configure `phpmailer_config.php` and `db.php` (not included for security)  
4. Run the project inside `htdocs` through `localhost`

## 🔒 Note
Sensitive files like `db.php`, `phpmailer_config.php`, `.env`, and user uploads are intentionally excluded from the repository using `.gitignore`.

## 📄 License
This project is for educational and personal development purposes.
