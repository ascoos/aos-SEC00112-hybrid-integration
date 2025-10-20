# Hybrid Integration in Ascoos OS: Laravel, Symfony, and Yii

## Introduction

This documentation describes the hybrid integration of three popular PHP frameworks (Laravel, Symfony, and Yii) into **Ascoos OS (PHP Web 5.0 Kernel)**, using the unified **TEventHandler** for event management. The example demonstrates how to combine the strengths of each framework into a single system without conflicts, for applications such as content management systems (CMS), application programming interfaces (API), or enterprise dashboards.

### Key Features
- **Modular Loading**: Loading frameworks via autoloaders, with binding to global variables ($GLOBALS) for easy access.
- **Event-Driven**: All events (login, logout, registration, profile update) execute cross-framework, with shared logging.
- **Error Handling**: Try-catch with logging across all frameworks + Ascoos OS.
- **Multilingual Support**: Comments and documents in Greek/English for an international audience.
- **Production-Ready**: Use of secure practices (e.g. Hash::make, bindValue).

## Prerequisites

- **PHP 8.2.0+** with `strict_types=1`.
- **Ascoos OS** or the [Ascoos Web Extended Studio 26](https://awes.ascoos.com).
- **Framework Packages**: Upload and unzip the ZIP files for Laravel, Symfony, and Yii (to the subfolder `/libs/` of **Ascoos OS**).
  - Laravel: `laravel/vendor/autoload.php` + `bootstrap/app.php`.
  - Symfony: `symfony/vendor/autoload.php` + Kernel class.
  - Yii: `yii/vendor/autoload.php` + `config/web.php`.
- **Database**: A database (e.g. MySQL) with a `users` table (Columns: id, name, email, password, last_login, created_at).
- **Dependencies**: Carbon (from Laravel), Illuminate Facades.

## Setup

1. **Create the Autoloader Files**: Use the previous case studies to load the frameworks:
   - `laravel_autoload.php` (SEC00103)
   - `symfony_autoload.php` (SEC00101)
   - `yii_autoload.php` (SEC00106)

2. **Place the Hybrid Script**: Place the `hybrid_init.php` file in any folder you want, as long as you have set up autoloading for **Ascoos OS** and have changed the paths to the three files above (Setup/1).

3. **Execution**: 
   ```bash
   php [path/]hybrid_init.php
   ```
   - It will load the frameworks, register events, and trigger `cms.login` as an example.

## Code

The full code is in the file [`hybrid_init.php`](hybrid_init.php). Comments are in English/Greek for ease of multilingual reading.

```php
<?php
/**
 * @ASCOOS-NAME         : Ascoos OS
 * @ASCOOS-VERSION      : 26.0.0
 * @ASCOOS-SUPPORT      : support@ascoos.com
 * @ASCOOS-BUGS         : https://issues.ascoos.com
 * 
 * @CASE-STUDY          : hybrid_init.php
 * @fileNo              : ASCOOS-OS-CASESTUDY-SEC00112
 * 
 * @desc <EN> Hybrid integration of Laravel, Symfony, and Yii within Ascoos OS using LibIn autoloaders and unified event handling.
 * @desc <GR> Υβριδική ενσωμάτωση των Laravel, Symfony και Yii στο Ascoos OS μέσω LibIn αυτόματων φορτωτών και ενοποιημένης διαχείρισης συμβάντων.
 * 
 * @since PHP 8.2.0+
 */
declare(strict_types=1);

use ASCOOS\OS\Kernel\Arrays\Events\TEventHandler;

// <EN> Loading via Ascoos OS autoloader
// <GR> Φόρτωση μέσω αυτόματου φορτωτή του Ascoos OS
global $conf, $AOS_LOGS_PATH;

// <EN> Settings for logging and events to manage logs, reports, and event triggers
// <GR> Ρυθμίσεις για καταγραφή και συμβάντα για τη διαχείριση αρχείων καταγραφής, αναφορών και ενεργοποίησης συμβάντων
$properties = [
    'cache' => $conf['cache'],
    'logs' => [
        'useLogger' => true,
        'dir' => $AOS_LOGS_PATH . '/',
        'file' => 'yii_loads.log'
    ]
];

// <EN> Load all framework autoloaders
// <GR> Φόρτωση όλων των αυτόματων φορτωτών των πλαισίων
require_once 'laravel_autoload.php';   // $GLOBALS['laravel_app']
require_once 'symfony_autoload.php';   // $GLOBALS['symfony_kernel']
require_once 'yii_autoload.php';       // $GLOBALS['yii_app']

// <EN> Load helper facades and classes
// <GR> Φόρτωση βοηθητικών προσόψεων και κλάσεων
use Illuminate\Support\Facades\Hash;   // <EN> Laravel facade for password hashing | <GR> Πρόσοψη του Laravel για κρυπτογράφηση κωδικού
use Illuminate\Support\Facades\Log;    // <EN> Laravel facade for logging | <GR> Πρόσοψη του Laravel για καταγραφή
use Illuminate\Support\Facades\DB;     // <EN> Laravel facade for DB access | <GR> Πρόσοψη του Laravel για πρόσβαση στη βάση δεδομένων
use Carbon\Carbon;                     // <EN> Carbon class for date/time | <GR> Κλάση Carbon για ημερομηνία/ώρα

// <EN> Access global framework instances
// <GR> Πρόσβαση στα παγκόσμια παραδείγματα των πλαισίων
$laravel_app    = $GLOBALS['laravel_app']    ?? null;
$symfony_kernel = $GLOBALS['symfony_kernel'] ?? null;
$yii_app        = $GLOBALS['yii_app']        ?? null;
?>
```

## Code Explanation

### Loading
- **Autoloaders**: Loads the frameworks via require_once, creating globals ($laravel_app, $symfony_kernel, $yii_app).
- **Properties**: Logging settings with a dedicated file for hybrid events.

### Registered Events
| Event Name | Used Frameworks | Description | Example Trigger |
|------------|-----------------|-------------|-----------------|
| `cms.login` | Laravel (Authentication), Symfony (Form), Yii (Database Update) | Authentication + form + update last_login. | `$eventHandler->trigger('cms.login', 'hybrid');` |
| `cms.logout` | Laravel (Authentication), Symfony (Logging), Yii (Logging) | Logout logging. | `$eventHandler->trigger('cms.logout', 'hybrid');` |
| `cms.register` | Laravel (Database + Hashing + Carbon), Symfony (Email Sending + Logging) | User registration + email. | `$eventHandler->trigger('cms.register', 'hybrid');` |
| `cms.profile.update` | Laravel (Authentication), Yii (Database Update), Shared Logging | Profile update. | `$eventHandler->trigger('cms.profile.update', 'hybrid');` |

### Usage
- **Event Trigger**: Use `$eventHandler->trigger('event.name', 'hybrid');` from anywhere in the app.
- **Customization**: Customize events by adding logic (e.g. real email sender in registration).
- **Testing**: Test with a dummy user: `php hybrid_init.php` – check logs in `hybrid_events.log`.

## Troubleshooting
- **Missing Global Variables**: Ensure autoloaders ran first.
- **Database Errors**: Set DB config in each framework (e.g. Laravel .env, Yii config/web.php).
- **Logging Failures**: Check permissions on the actual `$AOS_LOGS_PATH` folder.
- **No Events**: Debug with `error_log` in the callback.

## Extension
- **Add More Frameworks**: Using the same logic, you can add other frameworks, e.g. Phalcon via `phalcon_autoload.php`.
- **Production Tips**: Use queues (Laravel Jobs) for asynchronous events.

## License
This study is covered by the Ascoos General License (AGL).

---

*Version: 1.0 | Date: October 19, 2025*
