<?php
if (!defined('ABSPATH')) {
    exit;
}

// Tworzenie menu w panelu admina
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

    add_submenu_page(
        'custom-email-sender',
        'Ustawienia e-maila',
        'Ustawienia',
        'manage_options',
        'custom-email-sender-settings',
        'custom_email_sender_settings_page'
    );
}

function custom_email_sender_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Sprawdzenie, czy w URL jest parametr "sent"
    if (isset($_GET['sent']) && $_GET['sent'] == 1) {
        echo '<div class="notice notice-success is-dismissible">
            <p>E-mail został wysłany pomyślnie!</p>
        </div>';
    }

    echo '<div class="wrap custom-email-form" style="max-width:800px;">
        <h1>Wyślij e-mail</h1>
        <form method="POST" action="' . admin_url('admin-post.php') . '">
            <input type="hidden" name="action" value="send_custom_email">
            ' . wp_nonce_field('send_custom_email_action', 'custom_email_nonce', true, false) . '
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


add_action('admin_enqueue_scripts', 'custom_email_sender_enqueue_media_scripts');
function custom_email_sender_enqueue_media_scripts($hook) {
    // Pobierz nazwę strony z parametru "page"
    $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

    // Sprawdź, czy aktualna strona to "custom-email-sender-settings"
    if ($current_page === 'custom-email-sender-settings') {
        wp_enqueue_media(); // Ładowanie skryptów WordPress Media Library
        wp_enqueue_script(
            'custom-email-media-script',
            plugin_dir_url(__DIR__) . 'assets/media-script.js',
            array('jquery'), // Zależności
            false,
            true // Ładowanie w stopce
        );
    }
}




function custom_email_sender_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Obsługa zapisu
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom_email_logo'])) {
        if (!isset($_POST['custom_email_nonce']) || !wp_verify_nonce($_POST['custom_email_nonce'], 'save_email_settings')) {
            wp_die('Nieprawidłowy odnośnik. Spróbuj ponownie.');
        }

        $logo_url = esc_url_raw($_POST['custom_email_logo']);
        update_option('custom_email_logo', $logo_url);

        echo '<div class="updated"><p>Logo zostało zapisane!</p></div>';
    }

    $current_logo = get_option('custom_email_logo', '');

    // Formularz
    echo '<div class="wrap">
        <h1>Ustawienia e-maila</h1>
        <form method="POST" action="">
            ' . wp_nonce_field('save_email_settings', 'custom_email_nonce', true, false) . '
            <table class="form-table">
                <tr>
                    <th><label for="custom_email_logo">Logo (URL):</label></th>
                    <td>
                        <input type="text" name="custom_email_logo" id="custom_email_logo" value="' . esc_attr($current_logo) . '" class="regular-text">
                        <button type="button" class="button" id="upload_logo_button">Wybierz z mediów</button>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" value="Zapisz" class="button button-primary">
            </p>
        </form>
    </div>';
}

function custom_email_sender_process_form() {
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu.');
    }

    if (!isset($_POST['custom_email_nonce']) || !wp_verify_nonce($_POST['custom_email_nonce'], 'send_custom_email_action')) {
        wp_die('Nieprawidłowy odnośnik. Spróbuj ponownie.');
    }

    $to             = sanitize_email($_POST['custom_email_to']);
    $subject        = sanitize_text_field($_POST['custom_email_subject']);
    $messageContent = wp_kses_post($_POST['custom_email_content']);

    if (empty($subject) || empty($messageContent)) {
        wp_die('Brak tematu lub treści wiadomości. Proszę wypełnić oba pola.');
    }

    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/email-template.php';
    $template = ob_get_clean();

    // Zamiana placeholderów na dynamiczne treści
    $message = str_replace(
        ['{{subject}}', '{{content}}'],
        [esc_html($subject), wpautop($messageContent)],
        $template
    );

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // Wysłanie wiadomości e-mail
    if (wp_mail($to, $subject, $message, $headers)) {
        wp_redirect(admin_url('admin.php?page=custom-email-sender&sent=1'));
        exit;
    } else {
        wp_die('Wystąpił błąd podczas wysyłania e-maila. Spróbuj ponownie.');
    }
}



// Funkcja aktywacji wtyczki
function custom_email_sender_activation() {
    $template_file = plugin_dir_path(__FILE__) . '../templates/email-template.php';
    if (!file_exists($template_file)) {
        $default_template = "<html>\n<head>\n    <title>{{subject}}</title>\n    <meta charset=\"UTF-8\">\n</head>\n<body>\n    <div>{{content}}</div>\n</body>\n</html>";
        file_put_contents($template_file, $default_template);
    }

    $css_file = plugin_dir_path(__FILE__) . '../assets/styles.css';
    if (!file_exists($css_file)) {
        $default_css = ".custom-email-form {\n    background: #fff;\n    padding: 20px;\n    border-radius: 8px;\n    box-shadow: 0 2px 5px rgba(0,0,0,0.1);\n}\n.custom-email-form h1 {\n    color: #333;\n    margin-bottom: 20px;\n}\n.custom-email-form .form-table th {\n    text-align: left;\n    font-weight: bold;\n    padding: 10px 0;\n}\n.custom-email-form .form-table td {\n    padding: 10px 0;\n}\n.custom-email-form .button-primary {\n    background: #0073aa;\n    color: #fff;\n    border: none;\n    padding: 10px 20px;\n    border-radius: 5px;\n    cursor: pointer;\n}\n.custom-email-form .button-primary:hover {\n    background: #005177;\n}\n.form-submit-wrapper {\n    margin-top: 20px;\n    text-align: right;\n}";
        file_put_contents($css_file, $default_css);
    }
}

// Ładowanie stylów i skryptów
function custom_email_sender_enqueue_assets($hook) {
    if ($hook === 'toplevel_page_custom-email-sender') {
        wp_enqueue_style('custom-email-sender-styles', plugin_dir_url(__FILE__) . '../assets/styles.css');
    }
}
