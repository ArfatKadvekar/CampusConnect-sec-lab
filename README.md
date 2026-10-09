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

---

## Multi-User Password Security Demonstration (`/lab-demo/`)

A dedicated presentation dashboard designed for instructors to demonstrate password security concepts to first-year computer engineering students on a projector.

### Key Learning Objectives

1. **Why predictable passwords are vulnerable:** Weak, college-themed passwords (`campus2026`) appear at the front of targeted candidate lists.
2. **Complexity ≠ Unpredictability:** Passwords with symbols and capitals (`Campus@2026`) follow common patterns that dictionary generators easily target.
3. **Entropy & Randomness:** Sufficiently long, random strings (`rN7#kP9!wB2$xT5&`) and multi-word passphrases (`falcon-river-blue-matrix`) resist dictionary-based candidate searching.
4. **Candidate Dataset Dynamics:** Candidate searches evaluate whether a target exists in the attacker's dataset—a "not found" result indicates the wordlist was exhausted, not that a password is universally unbreakable.
5. **Defensive Authentication Controls:** How server-side bcrypt hashing, login rate limiting (max 5 attempts), 15-minute account lockouts, and Multi-Factor Authentication (MFA) mitigate guessing attacks.

---

### Demonstration Accounts Reference

All demo accounts are synthetic, disposable lab accounts isolated from production participants and administrators.

| Code | Fictional Username | Display Name | Category | Length | Pattern Characteristics | Dataset Status |
|:---:|:---|:---|:---|:---:|:---|:---|
| **A** | `demo_student_01` | Student Account A | Predictable | 10 chars | Weak campus word + year (`campus2026`) | **Found at #4,999** (19.99%) |
| **B** | `demo_student_02` | Student Account B | Modified Pattern | 11 chars | Capital + Symbol + Year (`Campus@2026`) | **Found at #12,000** (48.00%) |
| **C** | `demo_student_03` | Student Account C | Random String | 16 chars | High-entropy random alphanumeric + symbols | **Not in dataset** (0 / 25,001) |
| **D** | `demo_student_04` | Student Account D | Passphrase | 24 chars | 4-word Diceware passphrase | **Not in dataset** (0 / 25,001) |

*Bonus Benchmark:* Entry #25,000 (`V7q!2mL#9xR@4pZ`) is provided for full-dataset stress testing.

---

### Candidate Wordlist Verification

- **Location:** `data/campusconnect_demo_wordlist.txt`
- **Total Valid Lines:** `25,001`
- **Duplicate Entries:** `0`
- **Blank Lines:** `0`
- **Verified Target Positions:**
  - `campus2026` at Line **4,999**
  - `Campus@2026` at Line **12,000**
  - `V7q!2mL#9xR@4pZ` at Line **25,000**
  - Accounts C and D are completely absent from the dataset.

---

### Instructor Presentation Sequence (10 Steps)

1. **Introduce CampusConnect Portal:** Walk students through the legitimate public event registration portal (`/`).
2. **Explain Demonstration Accounts:** Open `/lab-demo/` and review the 4 accounts, discussing predictability vs. entropy.
3. **Run Predictable Password Demo (Account A):** Launch the candidate search and watch candidates stream by.
4. **Observe Live Timer & Rate:** Note real candidates evaluated per second on the instructor machine.
5. **Reveal Candidate Position:** Show that Account A was discovered at #4,999 (< 20% of dataset).
6. **Repeat with Modified Pattern (Account B):** Show that despite having uppercase and symbols, `Campus@2026` was cracked at #12,000 because pattern generators target campus years.
7. **Demonstrate Random Password (Account C):** Run all 25,001 entries to completion. Emphasize: *"Target not found in the tested candidate set. This proves candidate lists only succeed if the target is in the dictionary."*
8. **Switch to Protected Authentication (Scenario B):** Transition to Experiment 2 (Online Authentication Controls).
9. **Demonstrate Rate Limiting & Lockout:** Fire 5 consecutive failed login attempts; observe the transition from HTTP 401 to HTTP 429 lockout (15 minutes).
10. **Summarize Defense-in-Depth:** Explain that strong passwords must be coupled with bcrypt hashing, lockout policies, and MFA.

---

### Resetting Between Demonstrations

Click the **"Reset Demo State"** button on the top right of `/lab-demo/`. This:
- Clears demo failed attempts and unlocks all demo accounts
- Truncates disposable demo audit logs (`demo_login_attempts`)
- Clears session-recorded simulation timings
- **Preserves all real participant registrations and admin logs intact.**

---

## License

This project is provided for **educational purposes only** under the MIT License.

---

*CampusConnect is a fictional student organization. Any resemblance to real organizations is coincidental.*

