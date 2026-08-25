<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dunes Discovery Tourism')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f4f4;
            padding: 20px 0;
        }
        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #F58F43;
            padding: 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 150px;
            height: auto;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 10px 0 0 0;
            font-size: 24px;
            font-weight: normal;
        }
        .email-body {
            padding: 30px;
            color: #333333;
            font-size: 16px;
            line-height: 1.6;
        }
        .email-footer {
            background-color: #eeeeee;
            padding: 20px;
            text-align: center;
            color: #777777;
            font-size: 14px;
        }
        .email-footer a {
            color: #F58F43;
            text-decoration: none;
        }
        .btn {
            display: inline-block;
            background-color: #F58F43;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 20px;
        }
        blockquote {
            border-left: 4px solid #d2a13b;
            margin: 0;
            padding-left: 15px;
            color: #555555;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
        }
        .blockquote-green {
            border-left-color: #25d366;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <div class="email-header">
                <img src="https://dunesdiscoverytourism.com/images/logo.png" alt="Dunes Discovery Tourism Logo">
                <h1>Dunes Discovery Tourism</h1>
            </div>
            
            <div class="email-body">
                @yield('content')
            </div>
            
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} <a href="https://dunesdiscoverytourism.com">Dunes Discovery Tourism</a>. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
