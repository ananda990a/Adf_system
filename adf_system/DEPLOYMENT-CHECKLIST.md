# DEPLOYMENT GUIDE - ADF System to Hosting

## Status: READY FOR PRODUCTION ✅

### Changes Deployed:
1. **CRITICAL FIX**: Permission query bug in `includes/auth.php`
   - Fixed SQL JOIN issue with menu_code
   - Sandra and all users now see all 9 menus
   
2. **Features**:
   - 3-step user setup wizard (create users → assign businesses → set permissions)
   - Multi-business support with business switcher
   - Sandra user with full access (sandra/sandra123)

---

## DEPLOYMENT STEPS

### Step 1: Pull Latest Code from GitHub
```bash
cd /home/xxx/public_html/adf_system
git pull origin main
```

**Expected Output:**
```
Updating a68bb9c..e3e069d
Fast-forward
 includes/auth.php | 10 +++++-----
 1 file changed, 5 insertions(+), 5 deletions(-)
```

---

### Step 2: Verify Database Setup on Hosting

**Required Databases:**
- `adfb2574_adf` (master database) - **MUST EXIST**
- `adfb2574_narayana_hotel` (business database)
- `adfb2574_narayana_benscafe` (business database)

**To create adf_system on hosting** (if missing):
```bash
# Via SSH:
mysql -u adfb2574_adfsystem -p@Nnoc2025 << EOF
-- Check if adf_system exists
SHOW DATABASES LIKE 'adfb2574_adf%';

-- If it doesn't exist, create it
CREATE DATABASE IF NOT EXISTS adfb2574_adf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF
```

**Or via cPanel:**
1. Go to MySQL Databases
2. Create database: `adfb2574_adf`
3. Add user `adfb2574_adfsystem` with password `@Nnoc2025`
4. Grant ALL privileges

---

### Step 3: Verify config/config.php

Check production database credentials are set correctly:

```php
if ($isProduction) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'adfb2574_adf');           // ✅ Master database
    define('DB_USER', 'adfb2574_adfsystem');
    define('DB_PASS', '@Nnoc2025');
}
```

---

### Step 4: Create Master Database Schema

**Run this SQL on hosting to setup master database:**

```sql
-- In adfb2574_adf database

-- Businesses table
CREATE TABLE IF NOT EXISTS businesses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_code VARCHAR(100) UNIQUE,
    business_name VARCHAR(255),
    business_type VARCHAR(100),
    database_name VARCHAR(100),
    owner_id INT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    full_name VARCHAR(255),
    email VARCHAR(255),
    role_id INT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(100),
    role_code VARCHAR(100) UNIQUE,
    description TEXT,
    is_system_role BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- User-Business Assignment
CREATE TABLE IF NOT EXISTS user_business_assignment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    business_id INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_business (user_id, business_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (business_id) REFERENCES businesses(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- User-Menu Permissions ⭐ MOST IMPORTANT FOR FIX
CREATE TABLE IF NOT EXISTS user_menu_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    business_id INT,
    menu_code VARCHAR(100),              -- ✅ KEY FIELD (not menu_id)
    can_view BOOLEAN DEFAULT 0,
    can_create BOOLEAN DEFAULT 0,
    can_edit BOOLEAN DEFAULT 0,
    can_delete BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_perm (user_id, business_id, menu_code),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (business_id) REFERENCES businesses(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### Step 5: Insert Test Data

```sql
-- Insert Businesses
INSERT INTO businesses (business_code, business_name, business_type, database_name) 
VALUES 
    ('NARAYANAHOTEL', 'Narayana Hotel', 'hotel', 'adfb2574_narayana_hotel'),
    ('BENSCAFE', 'Bens Cafe', 'restaurant', 'adfb2574_benscafe');

-- Insert Roles
INSERT INTO roles (role_name, role_code, is_system_role)
VALUES 
    ('Admin', 'admin', 1),
    ('Manager', 'manager', 1),
    ('Accountant', 'accountant', 1),
    ('Staff', 'staff', 1);

-- Insert Sandra User (password: sandra123)
INSERT INTO users (username, password, full_name, email, role_id, is_active)
VALUES 
    ('sandra', '$2y$10$...', 'Sandra', 'sandra@example.com', 3, 1);
    -- Get the hashed password from local database or use: password_hash('sandra123', PASSWORD_BCRYPT)

-- Assign Sandra to both businesses
INSERT INTO user_business_assignment (user_id, business_id)
SELECT 7, id FROM businesses WHERE business_code IN ('NARAYANAHOTEL', 'BENSCAFE');

-- Grant Sandra permissions for all 9 menus in both businesses
INSERT INTO user_menu_permissions (user_id, business_id, menu_code, can_view, can_create)
SELECT 
    7,
    b.id,
    menus.code,
    1,
    1
FROM businesses b
CROSS JOIN (
    SELECT 'dashboard' as code
    UNION SELECT 'cashbook'
    UNION SELECT 'divisions'
    UNION SELECT 'frontdesk'
    UNION SELECT 'sales'
    UNION SELECT 'procurement'
    UNION SELECT 'reports'
    UNION SELECT 'settings'
    UNION SELECT 'users'
) menus;
```

---

### Step 6: Test Login on Hosting

1. **Clear browser cookies** for hosting domain
2. **Go to**: `https://yourdomain.com/adf_system/login.php`
3. **Login as**: `sandra` / `sandra123`
4. **Verify**: All 9 menus appear in sidebar:
   - ✅ Dashboard
   - ✅ Buku Kas (Cashbook)
   - ✅ Divisions
   - ✅ Front Desk
   - ✅ Sales
   - ✅ PO & SHOOP (Procurement)
   - ✅ Reports
   - ✅ Settings
   - ✅ Users

---

### Step 7: Test User Setup Wizard

1. **Admin login** (or use developer dashboard)
2. **Go to**: `/adf_system/developer/index.php`
3. **Test**: 
   - Create new user
   - Assign to businesses
   - Set permissions
   - Verify user can login

---

## 🔧 TROUBLESHOOTING

### Issue: "You don't have access to any business"
**Solution:**
- Check `user_business_assignment` table
- Ensure user is linked to at least one business
- Ensure `user_menu_permissions` has entries for that user+business

### Issue: Still only seeing 4 menus
**Solution:**
1. **Clear session**: User must logout and login again
2. **Check**: `user_menu_permissions` table has all 9 menu codes
3. **Verify** `includes/auth.php` line 237-248 is using direct `menu_code` query (not JOIN)
4. **Check PHP logs** for fallback warnings

### Issue: Login fails with "User tidak terdaftar"
**Solution:**
- User must exist in `adfb2574_adf.users` table
- Check username matches exactly (case-sensitive)
- Verify user has `is_active = 1`

---

## 📋 VERIFICATION CHECKLIST

Before going live:
- [ ] All databases created (`adfb2574_adf`, `adfb2574_narayana_hotel`, `adfb2574_benscafe`)
- [ ] Master database schema imported
- [ ] Businesses table has NARAYANAHOTEL and BENSCAFE
- [ ] Sandra user created with hashed password
- [ ] Sandra assigned to both businesses
- [ ] Permission entries for all 9 menus for Sandra
- [ ] Sandra can login
- [ ] All 9 menus visible in sidebar
- [ ] Menu permission fix is in place (includes/auth.php using menu_code directly)

---

## 🎯 WHAT WAS FIXED

### The Problem:
Sandra could login but only saw 4 menus instead of 9. Other users had similar issues.

### Root Cause:
`includes/auth.php` function `hasPermission()` was using a broken SQL JOIN:
```php
// BROKEN:
SELECT p.can_view FROM user_menu_permissions p
JOIN menu_items m ON p.menu_id = m.id  ❌ menu_id doesn't exist!
WHERE ... AND m.menu_code = ?
```

### The Solution:
```php
// FIXED:
SELECT can_view FROM user_menu_permissions  ✅ Direct query
WHERE ... AND menu_code = ?
```

Now permission checks work correctly and users see all menus!

---

## 📞 SUPPORT

If issues occur, check:
1. PHP error logs
2. `user_menu_permissions` table - confirm it has entries
3. Session browser cookies - clear and retry login
4. Database credentials in `config/config.php`

---

**Deployment Date:** 2026-02-10  
**Version:** 2.0.0  
**Status:** ✅ PRODUCTION READY
