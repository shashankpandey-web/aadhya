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
            background: linear-gradient(90deg, #4caf50, #087f23);
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

        .info-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .info-table th {
            background-color: #f4f4f9;
            color: #555;
        }

        .cta-button {
            display: inline-block;
            background-color: #4caf50;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.2);
            transition: transform 0.2s;
        }

        .cta-button:hover {
            background-color: #087f23;
            transform: translateY(-2px);
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
                <h1>New Advisor Registered 🎉</h1>
                <p>Check the details below to review the registration.</p>
            </td>
        </tr>

        <!-- Email Body -->
        <tr>
            <td class="email-body">
                <p>Hello Admin,</p>
                <p>
                    A new advisor has registered on <strong>{{ get_setting_data('web_site_name', 'content') }}</strong>.
                    Please find their details
                    below:
                </p>

                <!-- Advisor Details Table -->
                <table class="info-table" align="center">
                    <tr>
                        <th>Advisor Name</th>
                        <td>{{ $advisor_name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $advisor_email }}</td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td>{{ $dob }}</td>
                    </tr>
                    <tr>
                        <th>Phone Number</th>
                        <td>{{ $phone_number }}</td>
                    </tr>
                    <tr>
                        <th>Registration Date</th>
                        <td>{{ $register_date }}</td>
                    </tr>
                </table>

                <p>
                    Please log in to the admin panel to review or take further action on this registration.
                </p>
                <p>
                    <a href="{{ env('APP_URL') }}admin" class="cta-button">Go to Admin Panel</a>
                </p>
            </td>
        </tr>

        <!-- Email Footer -->
        <tr>
            <td class="email-footer">
                <p>&copy; {{ date('Y') }} {{ get_setting_data('web_site_name', 'content') }}. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
