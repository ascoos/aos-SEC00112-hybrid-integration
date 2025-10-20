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
 * @desc <GR> Υβριδική ενσωμάτωση των Laravel, Symfony και Yii στο Ascoos OS μέσω LibIn autoloaders και ενοποιημένης διαχείρισης συμβάντων.
 * 
 * @since PHP 8.2.0+
 */
declare(strict_types=1);

use ASCOOS\OS\Kernel\Arrays\Events\TEventHandler;

// <EN> Loading via Ascoos OS autoloader
// <GR> Φόρτωση μέσω Ascoos OS autoloader
global $conf, $AOS_LOGS_PATH;

// <EN> Settings for logging and events to manage logs, reports, and event triggers
// <GR> Ρυθμίσεις για logging και συμβάντα για τη διαχείριση logs, αναφορών και εκπομπής συμβάντων
$properties = [
    'cache' => $conf['cache'],
    'logs' => [
        'useLogger' => true,
        'dir' => $AOS_LOGS_PATH . '/',
        'file' => 'yii_loads.log'
    ]
];

// <EN> Load all framework autoloaders
// <GR> Φόρτωση όλων των autoloaders των frameworks
require_once 'laravel_autoload.php';   // $GLOBALS['laravel_app']
require_once 'symfony_autoload.php';   // $GLOBALS['symfony_kernel']
require_once 'yii_autoload.php';       // $GLOBALS['yii_app']

// <EN> Load helper facades and classes
// <GR> Φόρτωση βοηθητικών facades και κλάσεων
use Illuminate\Support\Facades\Hash;   // <EN> Laravel facade for password hashing | <GR> Facade του Laravel για hash κωδικού
use Illuminate\Support\Facades\Log;    // <EN> Laravel facade for logging | <GR> Facade του Laravel για logging
use Illuminate\Support\Facades\DB;     // <EN> Laravel facade for DB access | <GR> Facade του Laravel για πρόσβαση στη βάση
use Carbon\Carbon;                     // <EN> Carbon class for date/time | <GR> Κλάση Carbon για ημερομηνία/ώρα

// <EN> Access global framework instances
// <GR> Πρόσβαση στα global instances των frameworks
$laravel_app    = $GLOBALS['laravel_app']    ?? null;
$symfony_kernel = $GLOBALS['symfony_kernel'] ?? null;
$yii_app        = $GLOBALS['yii_app']        ?? null;

// <EN> Initialize Ascoos OS event handler for logging
// <GR> Αρχικοποίηση του Ascoos OS event handler για logging
$eventHandler =& TEventHandler::getInstance([], $properties);

// <EN> Register hybrid login event
// <GR> Καταχώριση υβριδικού συμβάντος login
$eventHandler->register('cms.login', 'hybrid', function () use ($laravel_app, $symfony_kernel, $yii_app, $eventHandler) {
    try {
        // <EN> Authenticate user via Laravel
        // <GR> Αυθεντικοποίηση χρήστη μέσω Laravel
        $user = $laravel_app->make('auth')->user();

        if ($user) {
            // <EN> Create Symfony form
            // <GR> Δημιουργία φόρμας μέσω Symfony
            $form = $symfony_kernel->getContainer()->get('form.factory')->createBuilder()->getForm();

            // <EN> Update login timestamp via Yii
            // <GR> Ενημέρωση χρονικού στιγμής login μέσω Yii
            $yii_app->db->createCommand("UPDATE users SET last_login = NOW() WHERE id = :id")
                ->bindValue(':id', $user->id)
                ->execute();

            // <EN> Log success across frameworks
            // <GR> Καταγραφή επιτυχίας σε όλα τα frameworks
            Log::info("Hybrid login: user ID {$user->id}");
            $symfony_kernel->getContainer()->get('logger')->info("Hybrid login executed");
            \Yii::info("Hybrid login executed for user ID {$user->id}", __METHOD__);
        }
    } catch (Exception $e) {
        // <EN> Log error
        // <GR> Καταγραφή σφάλματος
        $eventHandler->logger->log("Hybrid login error: " . $e->getMessage(), $eventHandler::DEBUG_LEVEL_ERROR);
    }
});

// <EN> Register hybrid logout event
// <GR> Καταχώριση υβριδικού συμβάντος logout
$eventHandler->register('cms.logout', 'hybrid', function () use ($laravel_app, $symfony_kernel, $yii_app, $eventHandler) {
    try {
        $user = $laravel_app->make('auth')->user();
        if ($user) {
            Log::info("User {$user->email} logged out");
            $symfony_kernel->getContainer()->get('logger')->info("Logout event for {$user->email}");
            \Yii::info("Logout event for {$user->email}", __METHOD__);
        }
    } catch (Exception $e) {
        $eventHandler->logger->log("Hybrid logout error: " . $e->getMessage(), $eventHandler::DEBUG_LEVEL_ERROR);
    }
});

// <EN> Register hybrid registration event
// <GR> Καταχώριση υβριδικού συμβάντος εγγραφής
$eventHandler->register('cms.register', 'hybrid', function () use ($laravel_app, $symfony_kernel, $eventHandler) {
    try {
        // <EN> Create new user via Laravel DB
        // <GR> Δημιουργία νέου χρήστη μέσω Laravel DB
        $userId = DB::table('users')->insertGetId([
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => Hash::make('secret'),     // <EN> Secure password hash | <GR> Ασφαλές hash κωδικού
            'created_at' => Carbon::now(),          // <EN> Timestamp | <GR> Χρονική σήμανση
        ]);

        // <EN> Send welcome email via Symfony (dummy logic)
        // <GR> Αποστολή email καλωσορίσματος μέσω Symfony (προσομοίωση)
        $mailer = $symfony_kernel->getContainer()->get('mailer');
        // $mailer->send(...); // Προσαρμόζεται ανάλογα με το setup

        // <EN> Log success
        // <GR> Καταγραφή επιτυχίας
        Log::info("User registered with ID {$userId}");
        $symfony_kernel->getContainer()->get('logger')->info("User registration completed");
        $eventHandler->logger->log("Hybrid registration successful for user ID {$userId}", $eventHandler::DEBUG_LEVEL_INFO);

    } catch (Exception $e) {
        Log::error("Hybrid register error: " . $e->getMessage());
        $symfony_kernel->getContainer()->get('logger')->error("Hybrid register error: " . $e->getMessage());
        $eventHandler->logger->log("Hybrid register error: " . $e->getMessage(), $eventHandler::DEBUG_LEVEL_ERROR);
    }
});

// <EN> Register hybrid profile update event
// <GR> Καταχώριση υβριδικού συμβάντος ενημέρωσης προφίλ
$eventHandler->register('cms.profile.update', 'hybrid', function () use ($laravel_app, $yii_app, $eventHandler) {
    try {
        $user = $laravel_app->make('auth')->user();
        if ($user) {
            $yii_app->db->createCommand("UPDATE users SET name = :name WHERE id = :id")
                ->bindValues([':name' => 'Updated Name', ':id' => $user->id])
                ->execute();

            Log::info("Profile updated for user {$user->id}");
            \Yii::info("Profile updated for user {$user->id}", __METHOD__);
            $eventHandler->logger->log("Hybrid profile update successful for user ID {$user->id}", $eventHandler::DEBUG_LEVEL_INFO);
        }
    } catch (Exception $e) {
        $eventHandler->logger->log("Hybrid profile update error: " . $e->getMessage(), $eventHandler::DEBUG_LEVEL_ERROR);
    }
});

// <EN> Trigger login event as example
// <GR> Ενεργοποίηση συμβάντος login ως παράδειγμα
$eventHandler->trigger('cms.login', 'hybrid');

$eventHandler->Free();
?>