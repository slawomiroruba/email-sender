<?php
/**
 * Plugin Name: Custom Email Sender
 * Description: Prosty klient pocztowy w WordPress z możliwością korzystania z własnego szablonu e-maila.
 * Version: 1.0
 * Author: Sławomir
 */

// Wymuszenie UTF-8 dla wszystkich maili w WordPress
add_filter('wp_mail_charset', function ($charset) {
    return 'UTF-8';
});

// Ładowanie plików wtyczki
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// Dodanie menu w panelu admina
add_action('admin_menu', 'custom_email_sender_menu');

// Załadowanie stylów i skryptów
add_action('admin_enqueue_scripts', 'custom_email_sender_enqueue_assets');

// Obsługa wysyłania formularza
add_action('admin_post_send_custom_email', 'custom_email_sender_process_form');

// Funkcja aktywacji wtyczki
register_activation_hook(__FILE__, 'custom_email_sender_activation');
