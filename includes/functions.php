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

    $timezone = get_option('timezone_string') ? new DateTimeZone(get_option('timezone_string')) : wp_timezone();
    $current_time = new DateTime('now', $timezone);


    echo '<div class="wrap custom-email-form" style="max-width:800px;">
        <h1>Wyślij e-mail</h1>
        <form method="POST" action="' . admin_url('admin-post.php') . '">
            <input type="hidden" name="action" value="send_custom_email">
            ' . wp_nonce_field('send_custom_email_action', 'custom_email_nonce', true, false) . '
            <table class="form-table">
                <tr>
                    <th><label for="custom_email_to">Do:</label></th>
                    <td>
                        <input type="text" name="custom_email_to" id="custom_email_to" class="regular-text" required>
                        <p class="description">Podaj oddzielone przecinkami adresy e-mail.</p>
                        <div id="custom_email_to_error" style="color:red; display:none;">Wprowadź prawidłowe adresy e-mail oddzielone przecinkami.</div>
                    </td>
                </tr>
                <script>
                document.addEventListener("DOMContentLoaded", function(){
                    var form = document.querySelector(".custom-email-form form");
                    form.addEventListener("submit", function(e){
                        var emailField = document.getElementById("custom_email_to");
                        var errorDiv = document.getElementById("custom_email_to_error");
                        var emails = emailField.value.split(",").map(function(email){
                            return email.trim();
                        }).filter(function(email){
                            return email.length > 0;
                        });
                        
                        var invalidEmails = [];
                        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        emails.forEach(function(email){
                            if(!re.test(email)){
                                invalidEmails.push(email);
                            }
                        });
                        
                        if(invalidEmails.length > 0){
                            e.preventDefault();
                            errorDiv.innerText = "Niepoprawne adresy email: " + invalidEmails.join(", ");
                            errorDiv.style.display = "block";
                        } else {
                            errorDiv.style.display = "none";
                        }
                    });
                });
                </script>
                <tr>
                    <th><label for="custom_email_schedule">Zaplanuj wysyłkę:</label></th>
                    <td><input type="datetime-local" name="custom_email_schedule" id="custom_email_schedule" class="regular-text" value="' . $current_time->format('Y-m-d\TH:i') . '">
                        <p class="description">Pozostaw puste, aby wysłać e-mail natychmiast.</p>
                    </td>
                </tr>

                <tr>
                    <th><label for="custom_email_subject">Temat:</label></th>
                    <td><input type="text" name="custom_email_subject" id="custom_email_subject" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="custom_email_content">Treść:</label></th>
                    <td>';
                    wp_editor('', 'custom_email_content', [
                        'textarea_name' => 'custom_email_content',
                        'teeny' => false,
                        'quicktags' => true,
                        'tinymce' => [
                            'forced_root_block' => 'p', // Wymusza domyślne dodawanie <p>
                        ]
                    ]);
                    echo '</td>
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

function custom_wp_mail($to, $subject, $messageContent, $headers = '', $attachments = []) {

    // Sprawdź czy $to, $subject i $message nie są puste
    if (empty($to) || empty($subject) || empty($messageContent)) {
        return false;
    }

    $messageContent = wp_specialchars_decode($messageContent, ENT_QUOTES);


    error_log('Decoded message: ' . $messageContent);

    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/email-template.php';
    $template = ob_get_clean();

    // Zastąp placeholdery
    $message = str_replace(
        ['{{to}}', '{{subject}}', '{{content}}', '{{logo}}'],
        [$to, $subject, $messageContent, get_option('custom_email_logo', '')],
        $template
    );

    if (is_array($headers)) {
        $headers = array_merge($headers, ['Content-Type: text/html; charset=UTF-8']);
    } else {
        $headers = ['Content-Type: text/html; charset=UTF-8'];
    }

    $sent = wp_mail($to, $subject, $message, $headers);

    // logowanie błędów  i powodu niepowodzenia
    if (!$sent) {
        error_log('Nie udało się wysłać wiadomości e-mail.');
    }

    return $sent;

}

function custom_email_sender_process_form() {
    if (!current_user_can('manage_options')) {
        wp_die('Brak dostępu.');
    }

    if (!isset($_POST['custom_email_nonce']) || !wp_verify_nonce($_POST['custom_email_nonce'], 'send_custom_email_action')) {
        wp_die('Nieprawidłowy odnośnik. Spróbuj ponownie.');
    }

    // Rozdziel adresy email oddzielone przecinkami i oczyść je
    $emailsInput = isset($_POST['custom_email_to']) ? $_POST['custom_email_to'] : '';
    $toEmails = array_filter(array_map('sanitize_email', explode(',', $emailsInput)));

    if (empty($toEmails)) {
        wp_die('Nie podano prawidłowych adresów e-mail.');
    }

    $subject        = sanitize_text_field($_POST['custom_email_subject']);
    $messageContent = wpautop(wp_kses_post($_POST['custom_email_content']));
    $scheduleTime   = sanitize_text_field($_POST['custom_email_schedule']);

    if (empty($subject) || empty($messageContent)) {
        wp_die('Brak tematu lub treści wiadomości. Proszę wypełnić oba pola.');
    }

    if (!empty($scheduleTime)) {
        // Przekształć czas lokalny na UTC
        $local_timestamp = strtotime($scheduleTime);
        $utc_offset = get_option('gmt_offset') * HOUR_IN_SECONDS;
        $utc_timestamp = $local_timestamp - $utc_offset;

        error_log('Local time: ' . date('Y-m-d H:i:s', $local_timestamp));
        error_log('UTC offset: ' . get_option('gmt_offset'));
        error_log('UTC time: ' . date('Y-m-d H:i:s', $utc_timestamp));

        if ($utc_timestamp && $utc_timestamp > time()) {
            // Zaplanuj wysyłkę dla każdego adresu osobno
            foreach ($toEmails as $email) {
                $encodedMessage = htmlspecialchars($messageContent, ENT_QUOTES, 'UTF-8');
                wp_schedule_single_event($utc_timestamp, 'custom_email_sender_scheduled_event', [
                    $email,
                    $subject,
                    $encodedMessage
                ]);
            }

            wp_redirect(add_query_arg(['sent' => 1], admin_url('admin.php?page=custom-email-sender')));
            exit;
        }
    }

    // Natychmiastowa wysyłka: wysyłaj e-maile pojedynczo
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $allSent = true;
    foreach ($toEmails as $email) {
        if (!custom_wp_mail($email, $subject, $messageContent, $headers)) {
            error_log('Wysyłka e-maila nie powiodła się dla: ' . $email);
            $allSent = false;
        }
    }

    if ($allSent) {
        wp_redirect(add_query_arg(['sent' => 1], admin_url('admin.php?page=custom-email-sender')));
        exit;
    } else {
        wp_die('Nie udało się wysłać jednej lub więcej wiadomości. Spróbuj ponownie.');
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

add_filter('woocommerce_available_payment_gateways', 'enable_free_orders');
function enable_free_orders($available_gateways) {
    if ( function_exists( 'WC' ) && WC()->cart && WC()->cart->get_cart_contents_count() > 0 && WC()->cart->total === 0 ) {
        // Wymuś domyślną metodę płatności dla darmowych zamówień.
        $available_gateways = array();
    }
    return $available_gateways;
}
add_action('woocommerce_order_status_pending_to_processing', 'complete_zero_value_orders');
add_action('woocommerce_order_status_pending_to_on-hold', 'complete_zero_value_orders');

function complete_zero_value_orders($order_id) {
    $order = wc_get_order($order_id);
    if ($order->get_total() == 0) {
        $order->update_status('completed');
    }
}
