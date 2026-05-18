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

        .offer-box {
            background-color: #fff4f7;
            border: 2px dashed #FF3E6C;
            border-radius: 8px;
            padding: 20px;
            margin: 20px auto;
            text-align: center;
        }

        .offer-box .offer-value {
            font-size: 36px;
            font-weight: bold;
            color: #FF3E6C;
            letter-spacing: 2px;
        }

        .offer-box .offer-label {
            font-size: 14px;
            color: #555;
            margin-top: 8px;
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
                <h1>We Miss You! 💫</h1>
            </td>
        </tr>

        <!-- Email Body -->
        <tr>
            <td class="email-body">
                <p>Hi {{ $name }},</p>
                <p>It's been a while since your last reading. We'd love to have you back!</p>

                @if (!empty($coupon_code))
                <div class="offer-box">
                    <div class="offer-value">{{ $coupon_code }}</div>
                    <div class="offer-label">
                        Use this code at checkout
                        @if (!empty($discount_percentage) && $discount_percentage > 0)
                            &mdash; <strong>{{ $discount_percentage }}% OFF</strong>
                        @endif
                        &nbsp;&middot;&nbsp; Valid for 7 days
                    </div>
                </div>
                <p>Open the Aadya app, choose your advisor, and enter the code above to claim your discount.</p>
                @else
                <p>Your next reading is waiting — reconnect with your advisor and get the guidance you deserve.</p>
                @endif

                <br>
                <p>Best regards,</p>
                <p>Team Aadya Universe</p>
            </td>
        </tr>

        <!-- Email Footer -->
        <tr>
            <td class="email-footer">
                &copy; {{ date('Y') }} Aadya Universe. All rights reserved.
            </td>
        </tr>
    </table>
</body>

</html>
