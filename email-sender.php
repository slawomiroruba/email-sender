<?php
/**
 * Plugin Name: Custom Email Sender
 * Description: Prosty klient pocztowy w WordPress z możliwością korzystania z własnego szablonu e-maila.
 * Version: 1.0
 * Author: Sławomir
 */

// Wymuszenie UTF-8 dla wszystkich maili w WordPress
add_filter( 'wp_mail_charset', function( $charset ) {
    return 'UTF-8';
});

// Dodanie menu w panelu admina
add_action('admin_menu', 'custom_email_sender_menu');
function custom_email_sender_menu() {
    add_menu_page(
        'Wyślij e-mail', 
        'Wyślij e-mail',
        'manage_options',
        'custom-email-sender',
        'custom_email_sender_page',
        'dashicons-email-alt',
        6
    );
}

// Dodanie stylów CSS tylko na stronie wtyczki
add_action('admin_enqueue_scripts', 'custom_email_sender_enqueue_styles');
function custom_email_sender_enqueue_styles($hook) {
    if ($hook !== 'toplevel_page_custom-email-sender') {
        return;
    }
    wp_enqueue_style('custom-email-sender-styles', plugin_dir_url(__FILE__) . 'styles.css');
}

// Funkcja generująca treść strony w panelu admina
function custom_email_sender_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Obsługa wysyłania formularza
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom_email_send'])) {
        $to             = sanitize_email($_POST['custom_email_to']);
        $subject        = sanitize_text_field($_POST['custom_email_subject']);
        $messageContent = wp_kses_post($_POST['custom_email_content']);

        // Wczytanie szablonu e-maila
        ob_start();
        include plugin_dir_path(__FILE__) . 'email-template.php';
        $message = ob_get_clean();

        // Zamiana placeholderów w szablonie
        $message = str_replace(
            array('{{subject}}', '{{content}}'),
            array($subject, $messageContent),
            $message
        );

        // Nagłówki
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Wysłanie e-maila (temat musi być $encoded_subject)
        wp_mail($to, $subject, $message, $headers);

        echo '<div class="updated"><p>E-mail został wysłany!</p></div>';
    }

    // Formularz do tworzenia i wysyłania e-maila
    echo '<div class="wrap custom-email-form" style="max-width:800px;">
        <h1>Wyślij e-mail</h1>
        <form method="POST">
            <table class="form-table">
                <tr>
                    <th><label for="custom_email_to">Do:</label></th>
                    <td><input type="email" name="custom_email_to" id="custom_email_to" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="custom_email_subject">Temat:</label></th>
                    <td><input type="text" name="custom_email_subject" id="custom_email_subject" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="custom_email_content">Treść:</label></th>
                    <td>';
                        wp_editor('', 'custom_email_content', ['textarea_name' => 'custom_email_content']);
    echo '      </td>
                </tr>
            </table>
            <div class="form-submit-wrapper">
                <input type="submit" name="custom_email_send" id="custom_email_send" class="button button-primary" value="Wyślij">
            </div>
        </form>
    </div>';
}

// Funkcja aktywacji wtyczki – tworzy plik szablonu i plik CSS, jeśli nie istnieją
register_activation_hook(__FILE__, 'custom_email_sender_activation');
function custom_email_sender_activation() {
    $template_file = plugin_dir_path(__FILE__) . 'email-template.php';
    if (!file_exists($template_file)) {
        $default_template = "<html>\n<head>\n    <title>{{subject}}</title>\n    <meta charset=\"UTF-8\">\n</head>\n<body>\n    <div>{{content}}</div>\n</body>\n</html>";
        file_put_contents($template_file, $default_template);
    }

    $css_file = plugin_dir_path(__FILE__) . 'styles.css';
    if (!file_exists($css_file)) {
        $default_css = ".custom-email-form {\n    background: #fff;\n    padding: 20px;\n    border-radius: 8px;\n    box-shadow: 0 2px 5px rgba(0,0,0,0.1);\n}\n.custom-email-form h1 {\n    color: #333;\n    margin-bottom: 20px;\n}\n.custom-email-form .form-table th {\n    text-align: left;\n    font-weight: bold;\n    padding: 10px 0;\n}\n.custom-email-form .form-table td {\n    padding: 10px 0;\n}\n.custom-email-form .button-primary {\n    background: #0073aa;\n    color: #fff;\n    border: none;\n    padding: 10px 20px;\n    border-radius: 5px;\n    cursor: pointer;\n}\n.custom-email-form .button-primary:hover {\n    background: #005177;\n}\n.form-submit-wrapper {\n    margin-top: 20px;\n    text-align: right;\n}";
        file_put_contents($css_file, $default_css);
    }
}