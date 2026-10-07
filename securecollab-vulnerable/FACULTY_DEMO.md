# SecureCollab — Faculty Demonstration & Viva Script

This document provides a step-by-step presentation script to demonstrate the 5 web application security vulnerabilities and their mitigations to faculty, evaluators, and auditors.

---

## 🎯 DEMONSTRATION FLOW OVERVIEW
For each vulnerability, follow this 4-step testing routine:
1. **Show Feature in Action** on the normal application interface.
2. **Execute Attack Payload** on `securecollab-vulnerable` and observe the vulnerability exploit.
3. **Inspect Vulnerable vs Secure Code** side-by-side to highlight the exact code flaw.
4. **Execute Same Attack Payload** on `securecollab-secure` and prove that the attack is mitigated.

---

## 1️⃣ DEMO 1: REFLECTED XSS (Cross-Site Scripting)

### • Real Feature
**Project Search** (`/projects/search.php?q=...`)

### • Vulnerable Implementation (`securecollab-vulnerable/projects/search.php`)
```php
// VULNERABLE: Direct echo of user input without output encoding
<span class="badge bg-primary"><?php echo $query; ?></span>
```

### • Secure Implementation (`securecollab-secure/projects/search.php`)
```php
// SECURE: Contextual HTML output encoding
<span class="badge bg-primary">
    <?php echo htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"); ?>
</span>
```

### • Step-by-Step Testing Procedure
1. Open Vulnerable App: `http://localhost/securecollab-vulnerable/auth/login.php` (Log in as `user@securecollab.local`).
2. In the top navbar search box (or URL), type payload:
   `<script>alert('Reflected-XSS-Exploited')</script>`
3. Press **Enter**.
4. **Result on Vulnerable App**: A JavaScript alert box pops up showing `"Reflected-XSS-Exploited"`.
5. Now open Secure App: `http://localhost/securecollab-secure/auth/login.php`.
6. Search the exact same payload: `<script>alert('Reflected-XSS-Exploited')</script>`.
7. **Result on Secure App**: The script does NOT execute. Instead, the literal HTML code is safely displayed as harmless text on screen.

### • Viva Explanation
> *"Reflected XSS occurs when untrusted user input from HTTP GET parameters is rendered directly into the HTML response without output encoding. In the secure version, we use `htmlspecialchars()` with `ENT_QUOTES` which translates special characters like `<` and `>` into HTML entities (`&lt;` and `&gt;`), neutralizing script execution."*

---

## 2️⃣ DEMO 2: STORED XSS (Persistent XSS)

### • Real Feature
**Project Discussion Comments** (`/discussions/view.php?id=1`)

### • Vulnerable Implementation (`securecollab-vulnerable/discussions/view.php`)
```php
// VULNERABLE: Stored comment rendered directly in DOM without escaping
<div class="text-dark border-top pt-2 mt-2">
    <?php echo $c['comment']; ?>
</div>
```

### • Secure Implementation (`securecollab-secure/discussions/view.php`)
```php
// SECURE: Contextual HTML encoding applied at render time
<div class="text-dark border-top pt-2 mt-2">
    <?php echo nl2br(htmlspecialchars($c['comment'], ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")); ?>
</div>
```

### • Step-by-Step Testing Procedure
1. Open Vulnerable App: `http://localhost/securecollab-vulnerable/discussions/view.php?id=1`.
2. Scroll to **Add a Comment** box and paste payload:
   `<img src="x" onerror="alert('Stored-XSS-In-Database')">`
3. Click **Post Comment**.
4. **Result on Vulnerable App**: Comment is stored in MySQL. Whenever any user visits this discussion topic, the payload triggers an alert box `"Stored-XSS-In-Database"`.
5. Open Secure App: `http://localhost/securecollab-secure/discussions/view.php?id=1`.
6. Post the exact same comment payload: `<img src="x" onerror="alert('Stored-XSS-In-Database')">`.
7. **Result on Secure App**: Comment is saved in MySQL, but rendered safely as plaintext `<img src="x" onerror="...">` without triggering any script execution.

### • Viva Explanation
> *"Stored XSS is severe because malicious input is permanently stored in the database and executed automatically in the browser of every user who views the page. The secure version applies output encoding upon rendering, separating executable markup from user data."*

---

## 3️⃣ DEMO 3: DOM-BASED XSS

### • Real Feature
**Dynamic Project Preview** (`/projects/view.php?id=1#...` or `?preview_note=...`)

### • Vulnerable Implementation (`securecollab-vulnerable/assets/js/preview.js`)
```javascript
// VULNERABLE: Unsafe DOM sink allows client-side script injection
document.getElementById('previewContent').innerHTML = previewParam;
```

### • Secure Implementation (`securecollab-secure/assets/js/preview.js`)
```javascript
// SECURE: Safe DOM assignment treats input strictly as text
document.getElementById('previewContent').textContent = previewParam;
```

### • Step-by-Step Testing Procedure
1. Open Vulnerable App: `http://localhost/securecollab-vulnerable/projects/view.php?id=1#<img src=x onerror=alert('DOM-XSS-Triggered')>`.
2. Press **Enter** to load URL.
3. **Result on Vulnerable App**: Client-side JavaScript reads the location hash and assigns it via `.innerHTML`, triggering the alert box `"DOM-XSS-Triggered"`.
4. Open Secure App: `http://localhost/securecollab-secure/projects/view.php?id=1#<img src=x onerror=alert('DOM-XSS-Triggered')>`.
5. **Result on Secure App**: The script assigns the value via `.textContent`, displaying `<img src=x onerror=alert('DOM-XSS-Triggered')>` as plain text inside the preview box.

### • Viva Explanation
> *"DOM XSS occurs entirely on the client side when data from a user-controlled source (like location.hash) flows into an unsafe execution sink (like innerHTML). In the secure version, using textContent ensures that browser DOM parsers do not interpret the string as HTML tags."*

---

## 4️⃣ DEMO 4: CSRF (Cross-Site Request Forgery)

### • Real Feature
**Update Account Email** (`/profile/index.php` -> `/profile/change_email.php`)

### • Vulnerable Implementation (`securecollab-vulnerable/profile/change_email.php`)
```php
// VULNERABLE: Accepts state-changing POST request relying only on session cookies
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEmail = $_POST['email'];
    // Updates email without CSRF token verification
}
```

### • Secure Implementation (`securecollab-secure/profile/change_email.php` & `includes/functions.php`)
```php
// SECURE: Generates random token and verifies with hash_equals
if (!verify_csrf_token()) {
    http_response_code(403);
    die("Forbidden: Invalid Anti-CSRF Token");
}
```

### • Step-by-Step Testing Procedure
1. Open Vulnerable App and log in as `user@securecollab.local`.
2. Open a new tab and navigate to local attack PoC:
   `http://localhost/securecollab-vulnerable/demos/csrf_poc.html`
3. Click **Claim Free Prize (Trigger CSRF Attack)**.
4. Check User Profile in Vulnerable App (`http://localhost/securecollab-vulnerable/profile/index.php`).
5. **Result on Vulnerable App**: Email address has been unauthorizedly changed to `hacked_user@attacker-domain.com`.
6. Now log in to Secure App: `http://localhost/securecollab-secure/auth/login.php`.
7. Open local attack PoC for secure app:
   `http://localhost/securecollab-secure/demos/csrf_poc.html`
8. Click the attack button.
9. **Result on Secure App**: Request is blocked with **403 Forbidden: Invalid Anti-CSRF Token**, protecting the user's email address.

### • Viva Explanation
> *"CSRF tricks an authenticated user's browser into sending forged HTTP requests to a vulnerable application. We mitigate CSRF by injecting a unique, unpredictable anti-CSRF token stored in the user session and validating it on POST requests using timing-safe `hash_equals()` comparison."*

---

## 5️⃣ DEMO 5: CLICKJACKING (UI Redressing)

### • Real Feature
**Archive Project Action Page** (`/projects/archive.php?id=1`)

### • Vulnerable Implementation (`securecollab-vulnerable/includes/header.php`)
```php
// VULNERABLE: Lacks HTTP response security headers
// Page can be freely framed inside external <iframe> elements
```

### • Secure Implementation (`securecollab-secure/includes/header.php`)
```php
// SECURE: Sets framing protection headers
header("X-Frame-Options: SAMEORIGIN");
header("Content-Security-Policy: frame-ancestors 'self';");
```

### • Step-by-Step Testing Procedure
1. Open Clickjacking Test Page for Vulnerable App:
   `http://localhost/securecollab-vulnerable/demos/clickjacking_poc.html`
2. **Result on Vulnerable App**: The project archive page renders cleanly inside the external iframe.
3. Open Clickjacking Test Page for Secure App:
   `http://localhost/securecollab-secure/demos/clickjacking_poc.html`
4. **Result on Secure App**: Browser refuses to display the page in an iframe and displays a framing violation error.

### • Viva Explanation
> *"Clickjacking tricks users into clicking transparent buttons overlaid over legitimate framed web pages. We mitigate this using `X-Frame-Options: SAMEORIGIN` and W3C CSP `frame-ancestors 'self'`, telling modern web browsers never to render our site inside external iframes."*