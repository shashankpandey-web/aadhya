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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            font-size: 26px;
        }
        .email-body {
            padding: 30px 20px;
            color: #333333;
            line-height: 1.6;
        }
        .order-box {
            background-color: #fff8f9;
            border: 1px dashed #FF3E6C;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 20px 0;
            text-align: center;
        }
        .order-box .order-id {
            font-size: 22px;
            font-weight: bold;
            color: #FF3E6C;
        }
        .time-remaining {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            padding: 12px 16px;
            margin: 20px 0;
            font-size: 15px;
            color: #856404;
        }
        .cta-button {
            display: inline-block;
            background-color: #FF3E6C;
            color: #ffffff;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
            margin: 10px 0;
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
    <table class="email-container" align="center" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;">
        <tr>
            <td class="email-header">
                <h1>⏰ Reading Deadline Reminder</h1>
            </td>
        </tr>
        <tr>
            <td class="email-body">
                <p>Hi {{ $name }},</p>
                <p>This is a reminder that your client is waiting for their reading. Please submit it before the deadline.</p>

                <div class="order-box">
                    <div style="font-size:13px;color:#888;margin-bottom:4px;">Order Number</div>
                    <div class="order-id">#{{ $order_id }}</div>
                </div>

                <div class="time-remaining">
                    ⚠️ <strong>{{ $time_remaining }}</strong> to submit this reading.
                </div>

                <p>Please log in to the Aadya app and upload your reading as soon as possible to avoid any delays for your client.</p>


                <p>If you have already submitted the reading, please disregard this email.</p>

                <p>Thank you for your commitment to your clients!</p>
                <p>Best regards,<br>Team Aadya Universe</p>
            </td>
        </tr>
        <tr>
            <td class="email-footer">
                &copy; {{ date('Y') }} Aadya Universe. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
