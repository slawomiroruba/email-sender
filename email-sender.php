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

/**
 * Hook into the custom email sender scheduled event to send an email.
 *
 * @param string $to The recipient email address.
 * @param string $subject The subject of the email.
 * @param string $messageContent The content of the email message.
 *
 * This function sends an email with the specified recipient, subject, and message content.
 * The email is sent as HTML with UTF-8 encoding.
 */
add_action('custom_email_sender_scheduled_event', 'custom_email_sender_send_scheduled_email', 10, 3);

function custom_email_sender_send_scheduled_email($to, $subject, $messageContent) {
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    custom_wp_mail($to, $subject, $messageContent, $headers);
}

register_deactivation_hook(__FILE__, 'custom_email_sender_clear_scheduled_emails');

function custom_email_sender_clear_scheduled_emails() {
    $crons = _get_cron_array();
    foreach ($crons as $timestamp => $cron) {
        if (isset($cron['custom_email_sender_scheduled_event'])) {
            foreach ($cron['custom_email_sender_scheduled_event'] as $hook => $data) {
                wp_unschedule_event($timestamp, 'custom_email_sender_scheduled_event', $data['args']);
            }
        }
    }
}


// Funkcja aktywacji wtyczki
register_activation_hook(__FILE__, 'custom_email_sender_activation');
