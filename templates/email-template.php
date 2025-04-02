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
            margin: 30px auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background-color: #fb4c0c;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 100px;
            height: auto;
            margin-bottom: 10px;
        }
        .email-body {
            padding: 20px;
            color: #333333;
            line-height: 1.6;
            white-space: normal; /* Zapewnia prawidłowe wyświetlanie formatowania */
        }
        .email-footer {
            background-color: #f9f9f9;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #666666;
            border-top: 1px solid #dddddd;
        }
        .email-body a,
        .email-footer a {
            color: #fb4c0c;
            text-decoration: none;
        }
        .email-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{logo}}" alt="Logo" style="max-width: 100px; height: auto; margin-bottom: 10px;">
        </div>
        <div class="email-body">
            {{content}}
        </div>
        <div class="email-footer">
            <p>Dziękujemy za bycie z nami!</p>
            <a href="<?php echo esc_url(home_url()); ?>">Odwiedź naszą stronę</a>
        </div>
    </div>
</body>
</html>
