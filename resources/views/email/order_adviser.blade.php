<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9fc;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            border: 1px solid #e0e0e0;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: #FF3E6C;
            color: #ffffff;
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px 10px 0 0;
        }

        .email-header h1 {
            margin: 0;
            font-size: 28px;
        }

        .email-body {
            padding: 20px;
            color: #333333;
            text-align: center;
            line-height: 1.6;
        }

        .email-body p {
            margin: 15px 0;
        }

        .feature-box {
            background-color: #f9f9fc;
            padding: 15px 20px;
            margin: 15px auto;
            border: 1px dashed #ddd;
            border-radius: 8px;
            color: #555;
            text-align: left;
        }

        .email-footer {
            text-align: center;
            padding: 15px;
            background-color: #f4f4f9;
            font-size: 12px;
            color: #777777;
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>

<body>
    <table class="email-container" align="center" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 600px;">
        <!-- Email Header -->
        <tr>
            <td class="email-header">
                <h1>Congratulations! 🎉</h1>
            </td>
        </tr>

        <!-- Email Body -->
        <tr>
            <td class="email-body">
                <p>Hi {{ $name }},</p>
                <p>You have received a new order! Please review and process it promptly.</p>
                <br>
                <p>Best regards,</p>
                <p>Team Aadya Universe</p>
            </td>
        </tr>
    </table>
</body>

</html>
