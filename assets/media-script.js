jQuery(document).ready(function ($) {
    $('#upload_logo_button').on('click', function (e) {
        e.preventDefault();

        var mediaUploader = wp.media({
            title: 'Wybierz logo',
            button: {
                text: 'Użyj tego logo'
            },
            multiple: false
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#custom_email_logo').val(attachment.url);
        });

        mediaUploader.open();
    });
});
