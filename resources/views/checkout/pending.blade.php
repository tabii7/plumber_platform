<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Pending - Professional Plumbing Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .pending-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .pending-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #ffc107, #ff9800);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
            font-size: 2rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .plan-badge {
            background: linear-gradient(45deg, #0066ff, #0052cc);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
            margin: 20px 0;
        }
        .btn-custom {
            background: linear-gradient(45deg, #0066ff, #0052cc);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: transform 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .btn-outline {
            background: transparent;
            border: 2px solid #0066ff;
            color: #0066ff;
        }
        .btn-outline:hover {
            background: #0066ff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="pending-card">
        <div class="pending-icon">
            <i class="fas fa-clock"></i>
        </div>
        
        <h1 style="color: #333; margin-bottom: 20px;">Payment Processing</h1>
        
        <p style="color: #666; font-size: 1.1rem; margin-bottom: 30px;">
            Your payment is being processed. This may take a few minutes to complete.
        </p>

        <div class="plan-badge">
            {{ $plan_details['name'] }}
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 15px; margin: 30px 0;">
            <h4 style="color: #333; margin-bottom: 15px;">Payment Details</h4>
            <div style="text-align: left;">
                <p><strong>Plan:</strong> {{ $plan_details['name'] }}</p>
                <p><strong>Amount:</strong> €{{ number_format($plan_details['price'], 2) }}</p>
                <p><strong>Status:</strong> <span style="color: #ffc107;">Processing</span></p>
                <p><strong>Payment ID:</strong> {{ $payment->id }}</p>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <a href="{{ route('dashboard') }}" class="btn-custom">
                <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
            </a>
            <a href="{{ route('welcome') }}" class="btn-custom btn-outline">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="color: #666; font-size: 0.9rem;">
                You will receive an email confirmation once the payment is completed.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
