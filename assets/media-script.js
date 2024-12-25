jQuery(document).ready(function ($) {
    $('#upload_logo_button').on('click', function (e) {
        e.preventDefault();
        var mediaUploader;

        // Jeśli uploader już istnieje, otwórz go ponownie
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Tworzenie nowej instancji wp.media
        mediaUploader = wp.media({
            title: 'Wybierz logo', // Tytuł okna
            button: {
                text: 'Użyj tego logo' // Tekst przycisku
            },
            multiple: false // Wybór jednego pliku
        });

        // Po wybraniu pliku
        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#custom_email_logo').val(attachment.url); // Ustaw URL w polu tekstowym
        });

        mediaUploader.open();
    });
});
