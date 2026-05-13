<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            border: 1px solid #eaeaea;
            background-color: #ffffff;
            border-radius: 8px;
        }

        .email-header {
            background-color: #e74c3c;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .email-body {
            padding: 20px;
            color: #333333;
            line-height: 1.6;
        }

        .email-footer {
            text-align: center;
            padding: 10px;
            background-color: #f4f4f9;
            font-size: 12px;
            color: #777777;
        }
    </style>
</head>

<body>
    <table class="email-container" align="center" cellpadding="0" cellspacing="0"
        style="width: 100%; max-width: 600px; border: 1px solid #eaeaea;">
        <!-- Email Header -->
        <tr>
            <td class="email-header" style="background-color: #e74c3c; padding: 20px; color: white; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">Account Access Restricted</h1>
            </td>
        </tr>

        <!-- Email Body -->
        <tr>
            <td class="email-body" style="padding: 20px; color: #333333;">
                <p>Dear {{ $name }},</p>
                <p>
                    We noticed that your account on {{ get_setting_data('web_site_name', 'content') }} is currently
                    inactive. As a result, you are unable to log in at this time.
                </p>
                <p>
                    To reactivate your account and regain access, please contact our
                    support team or follow the reactivation process available on our
                    website.
                </p>
                <p>
                    If you believe this is a mistake or have any questions, feel free
                    to reach out to us at {{ get_setting_data('support_site_email', 'content') }}.
                </p>

                <p>Best regards,</p>
                <p>
                    <strong> {{ get_setting_data('web_site_name', 'content') }} Team</strong>
                </p>
            </td>
        </tr>

        <!-- Email Footer -->
        <tr>
            <td class="email-footer"
                style="text-align: center; padding: 10px; background-color: #f4f4f9; font-size: 12px; color: #777777;">
                <p>&copy; {{ date('Y') }} {{ get_setting_data('support_site_email', 'content') }}. All rights
                    reserved.</p>
                <p>All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>

</html>
