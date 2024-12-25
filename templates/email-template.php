<html>
<head>
    <title>{{subject}}</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .email-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #0073aa;
        }
        .email-header img {
            max-width: 200px;
            height: auto;
        }
        .email-body {
            padding: 20px 0;
            color: #333333;
        }
        .email-footer {
            text-align: center;
            margin-top: 20px;
            color: #666666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="<?php echo esc_url(get_option('custom_email_logo', '')); ?>" alt="Logo" style="max-width: 200px; height: auto; margin-bottom: 20px;">
        </div>
        <div class="email-body">
            <h1 style="text-align: center;">{{subject}}</h1>
            <div>{{content}}</div>
        </div>
        <div class="email-footer">
            <small>Stopka e-maila, np. nazwa firmy, kontakt, itp.</small>
        </div>
    </div>
</body>
</html>
