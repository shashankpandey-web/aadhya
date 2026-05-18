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
            background: linear-gradient(135deg, #FF3E6C, #a855f7);
            color: #ffffff;
            text-align: center;
            padding: 30px 20px;
            border-radius: 10px 10px 0 0;
        }

        .email-header h1 {
            margin: 0 0 6px;
            font-size: 28px;
        }

        .email-header p {
            margin: 0;
            font-size: 15px;
            opacity: 0.9;
        }

        .email-body {
            padding: 28px 24px;
            color: #333333;
            text-align: center;
            line-height: 1.7;
        }

        .email-body p {
            margin: 12px 0;
        }

        .offer-box {
            background-color: #fdf4ff;
            border: 2px dashed #a855f7;
            border-radius: 10px;
            padding: 24px 20px;
            margin: 24px auto;
            text-align: center;
        }

        .offer-box .offer-badge {
            display: inline-block;
            background: linear-gradient(135deg, #FF3E6C, #a855f7);
            color: #fff;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 20px;
            padding: 4px 14px;
            margin-bottom: 14px;
        }

        .offer-box .offer-discount {
            font-size: 52px;
            font-weight: bold;
            color: #FF3E6C;
            line-height: 1;
        }

        .offer-box .offer-discount span {
            font-size: 24px;
            vertical-align: super;
        }

        .offer-box .offer-subtitle {
            font-size: 14px;
            color: #666;
            margin: 6px 0 16px;
        }

        .offer-box .coupon-code {
            display: inline-block;
            background: #fff;
            border: 2px solid #a855f7;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #a855f7;
        }

        .offer-box .coupon-hint {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }

        .steps {
            text-align: left;
            background: #f9f9fc;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 20px 0;
        }

        .steps p {
            margin: 6px 0;
            font-size: 14px;
            color: #444;
        }

        .steps strong {
            color: #FF3E6C;
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

        <!-- Header -->
        <tr>
            <td class="email-header">
                <h1>Welcome to Aadya Universe! 🎉</h1>
                <p>Your spiritual journey starts with a special gift</p>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td class="email-body">
                <p>Hi <strong>{{ $name }}</strong>,</p>
                <p>
                    We're thrilled to have you with us. To celebrate your first reading,
                    we've got an exclusive offer just for you:
                </p>

                <div class="offer-box">
                    <div class="offer-badge">First Reading Offer</div>

                    <div class="offer-discount">
                        <span>%</span>{{ $discount_percentage }}<span>&nbsp;OFF</span>
                    </div>
                    <div class="offer-subtitle">on your first reading</div>

                    <div class="coupon-code">{{ $coupon_code }}</div>
                    <div class="coupon-hint">Enter this code at checkout</div>
                </div>

                <div class="steps">
                    <p><strong>Step 1 &rarr;</strong> Open the Aadya app</p>
                    <p><strong>Step 2 &rarr;</strong> Pick your advisor and service</p>
                    <p><strong>Step 3 &rarr;</strong> Enter code <strong>{{ $coupon_code }}</strong> before confirming</p>
                    <p><strong>Step 4 &rarr;</strong> Enjoy {{ $discount_percentage }}% off your first reading!</p>
                </div>

                <p style="font-size: 12px; color: #999;">
                    This offer is valid for first-time readings only and cannot be combined with other promotions.
                </p>

                <br>
                <p>Warm regards,</p>
                <p><strong>Team Aadya Universe</strong></p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td class="email-footer">
                &copy; {{ date('Y') }} Aadya Universe. All rights reserved.
            </td>
        </tr>

    </table>
</body>

</html>
