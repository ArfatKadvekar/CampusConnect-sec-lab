# CampusConnect Security Lab

> **This project is a deliberately vulnerable educational security lab representing a fictional student organization.
> It does not represent or reproduce the security posture of any real college, university, club, or organization.
> All names, events, credentials, and data are entirely fictional.**

---

## Overview

**CampusConnect Security Lab** is a locally runnable PHP/MySQL web application built for classroom use as an educational cybersecurity demonstration.

The application simulates a fictional student-organization event-registration portal.
It is intentionally configured with weak authentication to allow an instructor to demonstrate the real-world impact of:

- **Publicly reachable administrative interfaces**
- **Weak authentication credentials**
- **Lack of rate limiting and account lockout**

The same application includes a **hardened configuration** that demonstrates basic defensive controls, enabling a direct side-by-side comparison.

---

## Safety Notice

This application must **ONLY** be run in an **isolated local environment** (e.g., a Kali Linux VM or an isolated lab network).

Do **NOT**:
- Deploy this application to the internet or any shared network
- Use real credentials, real names, or real email addresses
- Use this to target real systems, real organizations, or real people
- Leave it running after the demonstration

This lab does **not** implement:
- Credential theft or exfiltration
- Malware, persistence, or stealth
- External system interaction
- Real credential collection

---

## Scenario

A fictional student organization (**CampusConnect**) maintains its own event-registration website.

The website includes:
- Public event listings
- A participant registration and login portal
- A **publicly reachable administrator login page** at `/admin/`
- An administrator account protected by a weak password

During the demonstration, the audience observes how an attacker can:
1. Discover the admin login page
2. Make repeated authentication attempts without throttling (vulnerable mode)
3. Gain unauthorized access to the administration dashboard

The **hardened configuration** shows how rate limiting, lockout, and a strong password prevent the same attack.

**Learning objective:**

> Publicly reachable administrative interface + weak authentication = unauthorized access

---

## Architecture

```
campusconnect-security-lab/
├── index.php               <- Public homepage with events
├── register.php            <- Participant registration form
├── login.php               <- Participant login
├── logout.php              <- Participant logout
├── admin/
│   ├── index.php           <- Admin login (publicly reachable -- intentional)
│   ├── dashboard.php       <- Admin dashboard (protected)
│   └── logout.php          <- Admin logout
├── config/
│   ├── config.php          <- LAB_MODE toggle + site constants
│   └── database.php        <- PDO connection helper
├── includes/
│   ├── header.php          <- Shared HTML header / navbar
│   ├── footer.php          <- Shared HTML footer
│   └── auth.php            <- Authentication + rate-limiting logic
├── assets/
│   ├── css/style.css       <- Full dark-mode stylesheet
│   └── js/app.js           <- Client-side JS
├── database/
│   ├── schema.sql          <- Table definitions
│   └── seed.sql            <- Fictional demo data
├── .env.example
├── .gitignore
└── README.md
```

---

## Technologies

| Layer      | Technology                              |
|------------|-----------------------------------------|
| Web server | Apache (XAMPP / LAMP / Kali)            |
| Language   | PHP 7.4+                                |
| Database   | MariaDB / MySQL                         |
| Frontend   | HTML5, CSS3 (Vanilla), JavaScript ES6   |
| Fonts      | Google Fonts (Inter, Plus Jakarta Sans) |

No external PHP frameworks are used. The code is intentionally simple for a student audience.

---

## Installation

### Prerequisites

- Apache with PHP 7.4+ enabled
- MariaDB or MySQL running locally
- A browser

### Step 1 -- Copy the project

```bash
# On Kali Linux with XAMPP:
cp -r campusconnect-security-lab /opt/lampp/htdocs/

# Or with standard Apache:
cp -r campusconnect-security-lab /var/www/html/
```

### Step 2 -- Database setup

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p campusconnect_lab < database/seed.sql
```

### Step 3 -- Configure the application

Open `config/config.php` and verify:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'campusconnect_lab');
define('DB_USER', 'root');
define('DB_PASS', '');   // Your local root password if set

define('SITE_URL', 'http://localhost/campusconnect-security-lab');

define('LAB_MODE', true);
```

### Step 4 -- Start Apache

```bash
# XAMPP on Kali
sudo /opt/lampp/lampp start

# Standard Apache
sudo systemctl start apache2 mysql
```

### Step 5 -- Open in browser

```
http://localhost/campusconnect-security-lab/
```

---

## LAB_MODE Configuration

Edit `config/config.php`:

```php
define('LAB_MODE', true);   // Vulnerable -- for demonstration
define('LAB_MODE', false);  // Hardened -- for comparison
```

| Setting              | Admin Password      | Rate Limiting | Lockout     | Login Logging |
|----------------------|---------------------|---------------|-------------|---------------|
| LAB_MODE = true      | Weak (admin123)     | None          | None        | None          |
| LAB_MODE = false     | Strong (set by you) | 5 attempts    | 15 minutes  | Recorded      |

**Important:** The strong password for hardened mode is set inside `config/config.php`.
Change it to a unique passphrase before each demonstration.

---

## Demo Flow

### Phase 1 -- Context (5 min)

- Show the public homepage
- Walk through the event listing and registration form
- Explain that the organization runs its own website

### Phase 2 -- Vulnerability discovery (5 min)

- Navigate to `http://localhost/campusconnect-security-lab/admin/`
- Point out that the admin login is publicly accessible with no protection

### Phase 3 -- Vulnerable mode demonstration (10 min)

With `LAB_MODE = true`:

- Attempt login with incorrect credentials -- fails instantly, no throttle
- Attempt login with `admin` / `admin123` -- immediate success
- Show the admin dashboard with participant data

Discussion points:
- Why is the admin interface publicly reachable?
- Why does a weak password make discovery trivial?
- What can an attacker do with admin access?

### Phase 4 -- Hardened mode comparison (10 min)

1. Logout from admin
2. Change `LAB_MODE` to `false` in `config/config.php`
3. Attempt repeated failed logins -- observe the lockout message
4. Open the Security Log tab in the dashboard -- show recorded attempts

Discussion points:
- How rate limiting prevents brute-force
- How account lockout mitigates credential guessing
- Why password strength matters even with lockout

---

## Security Lessons

| #  | Lesson                                                                                  |
|----|-----------------------------------------------------------------------------------------|
| 1  | Administrative interfaces should not be publicly reachable without additional controls  |
| 2  | Weak passwords are trivially bypassed by guessing or automated tools                    |
| 3  | Rate limiting and account lockout are essential first-line defenses                     |
| 4  | All authentication events should be logged for monitoring and incident response         |
| 5  | Defence-in-depth: strong passwords + rate limiting + monitoring work best together      |

---

## Hardened Configuration Details

When `LAB_MODE = false`:

- **Strong password** -- configured in `config/config.php`
- **Rate limiting** -- 5 failed attempts per IP within 15 minutes triggers lockout
- **Account lockout** -- login form is disabled for the locked IP for 15 minutes
- **Attempt logging** -- every admin login attempt recorded in `admin_login_attempts`
- **Session hardening** -- httponly, samesite=Lax cookie flags; session ID regenerated on login

---

## Fictional Data Reference

All participant names, email addresses, and events are entirely fictional.

**Admin credentials (LAB_MODE only):**

| Username | Password   |
|----------|------------|
| admin    | admin123   |

These credentials exist only for the demonstration and are not derived from any real account.

**Participant login (demo):**

All seeded participants use the password `demo1234`.
Their emails follow the pattern `name@example-college.edu` -- this is a non-existent domain.

---

## License

This project is provided for **educational purposes only** under the MIT License.

---

*CampusConnect is a fictional student organization. Any resemblance to real organizations is coincidental.*
