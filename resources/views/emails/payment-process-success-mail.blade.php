@php use App\Enum\SubscriptionBillingCycleEnum; @endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Welcome!</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="70" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="70" cy="20" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
            z-index: 1;
        }

        .checkmark {
            width: 40px;
            height: 40px;
            border: 3px solid white;
            border-radius: 50%;
            position: relative;
        }

        .checkmark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .header h1 {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .payment-summary {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            border-left: 5px solid #4facfe;
        }

        .payment-summary h3 {
            color: #4facfe;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .payment-summary h3::before {
            content: '💳';
            margin-right: 10px;
            font-size: 20px;
        }

        .payment-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(79, 172, 254, 0.1);
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
            font-weight: 700;
        }

        .amount {
            font-size: 24px;
            color: #4facfe;
            font-weight: 800;
        }

        .features-section {
            margin: 30px 0;
        }

        .features-title {
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .feature-card {
            background: white;
            border: 2px solid #f0f4ff;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-2px);
            border-color: #4facfe;
        }

        .feature-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .feature-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .feature-desc {
            font-size: 14px;
            color: #666;
        }

        .cta-section {
            text-align: center;
            margin: 40px 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            margin: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        }

        .secondary-button {
            background: transparent;
            border: 2px solid #4facfe;
            color: #4facfe;
        }

        .secondary-button:hover {
            background: #4facfe;
            color: white;
        }

        .next-steps {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            border-left: 5px solid #f39c12;
        }

        .next-steps h3 {
            color: #f39c12;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .next-steps h3::before {
            content: '🚀';
            margin-right: 10px;
            font-size: 20px;
        }

        .support-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            margin: 30px 0;
        }

        .support-section h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .support-section p {
            color: #666;
            margin-bottom: 20px;
        }

        .footer {
            background: #333;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .footer-logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #4facfe;
        }

        .footer p {
            color: #999;
            font-size: 14px;
            margin: 5px 0;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            color: #4facfe;
            text-decoration: none;
            margin: 0 10px;
            font-size: 18px;
        }

        .invoice-section {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4eeea 100%);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            border-left: 5px solid #28a745;
        }

        .invoice-section h3 {
            color: #28a745;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .invoice-section h3::before {
            content: '📄';
            margin-right: 10px;
            font-size: 20px;
        }

        @media (max-width: 600px) {
            .payment-details {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .cta-button {
                display: block;
                margin: 10px 0;
            }

            .header h1 {
                font-size: 28px;
            }

            .content {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header Section -->
    <div class="header">
        <div class="success-icon">
            <div class="checkmark"></div>
        </div>
        <h1>Payment Successful! 🎉</h1>
        <p>Welcome to Your Premium Experience</p>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="greeting">
            Hi {{$invoice->tenant?->owner?->name}}! 👋
        </div>

        <p style="font-size: 16px; color: #666; margin-bottom: 20px;">
            Fantastic news! Your payment has been processed successfully and your
            <strong>{{$invoice->subscription->plan_name}}</strong> subscription is now active. You're all set to enjoy
            our premium features!
        </p>

        <!-- Payment Summary -->
        <div class="payment-summary">
            <h3>Payment Summary</h3>
            <div class="payment-details">
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="detail-label">Plan</span>
                    <span class="detail-value">{{$invoice->subscription->plan_name}}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Amount</span>
                    <span class="detail-value amount">{{$invoice->total}}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Billing</span>
                    <span
                        class="detail-value">{{SubscriptionBillingCycleEnum::from($invoice->subscription->billing_cycle)->getLabel()}}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value" style="color: #28a745;">✅ Active</span>
                </div>
            </div>
        </div>

        <!-- Invoice Section -->
        <div class="invoice-section">
            <h3>Your Receipt</h3>
            <p style="color: #333; margin: 0;">
                Your payment receipt has been generated and is available for download.
                Keep this for your records and expense reporting.
            </p>
            <div style="margin-top: 15px;">
                <a href="#" class="cta-button" style="font-size: 14px; padding: 10px 25px;">
                    📄 Download Invoice PDF
                </a>
            </div>
        </div>

        <!-- Support Section -->
        <div class="support-section">
            <h3>Need Help? We're Here! 💬</h3>
            <p>Our friendly support team is ready to help you get the most out of your subscription.</p>
            <a href="#" class="cta-button secondary-button">Contact Support</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-logo">YourApp</div>
        <p>Thank you for choosing us for your business needs!</p>
        <div class="social-links">
            <a href="#">Twitter</a>
            <a href="#">Facebook</a>
            <a href="#">LinkedIn</a>
        </div>
        <p>This email was sent to john@example.com</p>
        <p>You can <a href="#" style="color: #4facfe;">manage your email preferences</a> or <a href="#"
                                                                                               style="color: #4facfe;">unsubscribe</a>
            at any time.</p>
        <p style="margin-top: 20px; font-size: 12px;">
            © 2025 YourApp Inc. All rights reserved.<br>
            123 Business Street, City, State 12345
        </p>
    </div>
</div>
</body>
</html>
