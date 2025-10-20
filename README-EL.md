# Υβριδική Ενσωμάτωση στο Ascoos OS: Laravel, Symfony και Yii

## Εισαγωγή

Αυτό το έγγραφο τεκμηρίωσης περιγράφει την υβριδική ενσωμάτωση τριών δημοφιλών πλαισίων PHP (Laravel, Symfony και Yii) στο **Ascoos OS (Πυρήνας PHP Web 5.0)**, χρησιμοποιώντας τον ενοποιημένο **TEventHandler** για διαχείριση συμβάντων. Το παράδειγμα επιδεικνύει πώς να συνδυάσετε τα δυνατά σημεία κάθε πλαισίου σε ένα ενιαίο σύστημα, χωρίς συγκρούσεις, για εφαρμογές όπως συστήματα διαχείρισης περιεχομένου (CMS), Διεπαφές Εφαρμογών (API) ή πίνακες ελέγχου επιχειρήσεων.


## Κύρια Χαρακτηριστικά

- **Αρθρωτή Φόρτωση**: Φόρτωση πλαισίων μέσω αυτόματων φορτωτών (autoloaders), με δέσιμο σε παγκόσμιες μεταβλητές ($_GLOBALS) για εύκολη πρόσβαση.
- **Βασισμένο σε Συμβάντα**: Όλα τα συμβάντα (σύνδεση, αποσύνδεση, εγγραφή, ενημέρωση προφίλ) εκτελούνται διαπλατφορμικά, με κοινή καταγραφή.
- **Διαχείριση Σφαλμάτων**: Try-catch με καταγραφή σε όλα τα πλαίσια + Ascoos OS.
- **Υποστήριξη Πολυγλωσσικότητας**: Σχόλια και έγγραφα σε Ελληνικά/Αγγλικά για διεθνές κοινό.
- **Έτοιμο για Παραγωγή**: Χρήση ασφαλών πρακτικών (π.χ. Hash::make, bindValue).

## Προαπαιτούμενα

- **PHP 8.2.0+** με `strict_types=1`.
- **Ascoos OS** ή το [Ascoos Web Extended Studio 26](https://awes.ascoos.com).
- **Πακέτα Πλαισίων**: Ανεβάστε και αποσυμπιέστε τα ZIP των Laravel, Symfony και Yii (στον υποφάκελο `/libs/` του **Ascoos OS**).
  - Laravel: `laravel/vendor/autoload.php` + `bootstrap/app.php`.
  - Symfony: `symfony/vendor/autoload.php` + κλάση Kernel.
  - Yii: `yii/vendor/autoload.php` + `config/web.php`.
- **Βάση Δεδομένων**: Μια βάση δεδομένων (π.χ. MySQL) με πίνακα `users` (Στήλες: id, name, email, password, last_login, created_at).
- **Εξαρτήσεις**: Carbon (από Laravel), Facades του Illuminate.

## Ρύθμιση

1. **Δημιουργήστε τα Αρχεία Αυτόματης Φόρτωσης**: Χρησιμοποιήστε τις προηγούμενες μελέτες περίπτωσης για να φορτώσετε τα πλαίσια:
   - `laravel_autoload.php` (SEC00103)
   - `symfony_autoload.php` (SEC00101)
   - `yii_autoload.php` (SEC00106)

2. **Τοποθετήστε το Υβριδικό Σενάριο**: Τοποθετήστε το αρχείο `hybrid_init.php` σε οποιονδήποτε φάκελο θέλετε, αρκεί να έχετε ορίσει αυτόματη φόρτωση του **Ascoos OS** και να έχετε αλλάξει τις διαδρομές προς τα παραπάνω τρία αρχεία (Ρύθμιση/1).

3. **Εκτέλεση**: 
   ```bash
   php [path/]hybrid_init.php
   ```
   - Θα φορτώσει τα πλαίσια, θα καταχωρήσει συμβάντα και θα ενεργοποιήσει το `cms.login` ως παράδειγμα.

## Κώδικας

Ο πλήρης κώδικας βρίσκεται στο αρχείο [`hybrid_init.php`](hybrid_init.php). Τα σχόλια είναι σε Αγγλικά/Ελληνικά για ευκολία πολυγλωσσικής ανάγνωσης.

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

## Εξήγηση Κώδικα

### Φόρτωση
- **Αυτόματοι Φορτωτές**: Φορτώνει τα πλαίσια μέσω require_once, δημιουργώντας globals ($laravel_app, $symfony_kernel, $yii_app).
- **Ιδιότητες**: Ρυθμίσεις καταγραφής με ειδικό αρχείο για υβριδικά συμβάντα.

### Καταχωρημένα Συμβάντα
| Ονομασία Συμβάντος | Χρησιμοποιούμενα Πλαίσια | Περιγραφή | Παράδειγμα Ενεργοποίησης |
|--------------------|--------------------------|-----------|--------------------------|
| `cms.login` | Laravel (Αυθεντικοποίηση), Symfony (Φόρμα), Yii (Ενημέρωση Βάσης) | Αυθεντικοποίηση + φόρμα + ενημέρωση τελευταίας σύνδεσης. | `$eventHandler->trigger('cms.login', 'hybrid');` |
| `cms.logout` | Laravel (Αυθεντικοποίηση), Symfony (Καταγραφή), Yii (Καταγραφή) | Καταγραφή αποσύνδεσης. | `$eventHandler->trigger('cms.logout', 'hybrid');` |
| `cms.register` | Laravel (Βάση Δεδομένων + Κρυπτογράφηση + Carbon), Symfony (Αποστολή Email + Καταγραφή) | Εγγραφή χρήστη + αποστολή email. | `$eventHandler->trigger('cms.register', 'hybrid');` |
| `cms.profile.update` | Laravel (Αυθεντικοποίηση), Yii (Ενημέρωση Βάσης), Κοινή Καταγραφή | Ενημέρωση προφίλ. | `$eventHandler->trigger('cms.profile.update', 'hybrid');` |

### Χρήση
- **Ενεργοποίηση Συμβάντος**: Χρησιμοποιήστε `$eventHandler->trigger('όνομα.συμβάντος', 'hybrid');` από οπουδήποτε στην εφαρμογή.
- **Προσαρμογή**: Προσαρμόστε τα συμβάντα προσθέτοντας λογική (π.χ. πραγματικό αποστολέα email στην εγγραφή).
- **Δοκιμή**: Δοκιμάστε με δοκιμαστικό χρήστη: `php hybrid_init.php` – ελέγξτε τα αρχεία καταγραφής στο `hybrid_events.log`.

## Διόρθωση Προβλημάτων
- **Μη Εμφανιζόμενες Παγκόσμιες Μεταβλητές**: Βεβαιωθείτε ότι οι αυτόματοι φορτωτές εκτελέστηκαν πρώτα.
- **Σφάλματα Βάσης Δεδομένων**: Ρυθμίστε τη διαμόρφωση της βάσης σε κάθε πλαίσιο (π.χ. Laravel .env, Yii config/web.php).
- **Αποτυχίες Καταγραφής**: Ελέγξτε τα δικαιώματα πρόσβασης στο πραγματικό φάκελο του `$AOS_LOGS_PATH`.
- **Χωρίς Συμβάντα**: Αποσφαλμάτωση με `error_log` στο callback.

## Επέκταση
- **Προσθήκη Περισσότερων Πλαισίων**: Με την ίδια λογική, μπορείτε να προσθέσετε και άλλα πλαίσια, π.χ. το Phalcon μέσω του `phalcon_autoload.php`.
- **Συμβουλές Παραγωγής**: Χρησιμοποιήστε ουρές (Laravel Jobs) για ασύγχρονα συμβάντα.

## Άδεια Χρήσης
Αυτή η μελέτη καλύπτεται από την Ascoos General License (AGL).

---

*Έκδοση: 1.0 | Ημερομηνία: 19 Οκτωβρίου 2025*