
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .code-box {
            background-color: #f7fafc;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
        }
        .verification-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #2d3748;
        }
        .warning {
            color: #718096;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                @if($type == \App\Enum\VerificationCodeType::RESET_PASSWORD->value)
                    Password Reset Code
                @else
                    Email Verification
                @endif
            </h1>
        </div>

        <p>Hello {{ $userName ?? 'there' }},</p>

        @if($type == \App\Enum\VerificationCodeType::RESET_PASSWORD->value)
            <p>You have requested to reset your password. Use the verification code below to proceed with your password reset:</p>
        @else
            <p>Thank you for registering with CRM. To verify your email address, please use the following verification code:</p>
        @endif

        <div class="code-box">
            <div class="verification-code">{{ $code }}</div>
        </div>

        <p>This code will expire in 15 minutes for security purposes.</p>

        <div class="warning">
            <p>⚠️ If you didn't request this code, please ignore this email or contact our support team if you have concerns.</p>
        </div>

        <p>Best regards,<br>The CRM Team</p>
    </div>
</body>
</html>
