<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        .terms-content { line-height: 1.8; }
        .terms-content h3 { @apply font-bold text-xl text-stone-800 mt-6 mb-3; }
        .terms-content p { @apply text-stone-600 mb-4; }
        .terms-content ul { @apply list-disc pl-6 text-stone-600 mb-4; }
    </style>

    <style>
/* Minimal fallback styles for offline demo */
body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
.btn { background: #e53e3e; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; }
.card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 10px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.alert { padding: 12px; border-radius: 4px; margin: 15px 0; }
.alert-error { background: #fee; border-left: 4px solid #c53030; color: #c53030; }
.alert-success { background: #efe; border-left: 4px solid #38a169; color: #38a169; }
</style>
</head>
<body class="bg-stone-50 min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h1 class="heading text-4xl font-bold text-stone-800">Terms of Service</h1>
            <p class="text-stone-500 mt-2">Last updated: March 7, 2026</p>
        </div>

        <div class="bg-white rounded-2xl border border-stone-200 p-8 terms-content">
            <p>Welcome to Samaaroh ("we," "our," or "us"). By accessing or using our wedding planning platform (the "Service"), you agree to comply with these Terms of Service.</p>
            
            <h3>1. Acceptance of Terms</h3>
            <p>By registering an account or using Samaaroh, you confirm you are at least 18 years old and agree to these Terms. If you disagree, please do not use our Service.</p>
            
            <h3>2. Service Description</h3>
            <p>Samaaroh is a platform connecting customers with wedding service providers (Dbagiwalas, party plots, caterers, etc.) in Nadiad, Gujarat. We facilitate bookings but are not responsible for service quality, cancellations, or disputes between users.</p>
            
            <h3>3. User Responsibilities</h3>
            <ul>
                <li>Provide accurate information during registration</li>
                <li>Keep account credentials confidential</li>
                <li>Use the platform only for legitimate wedding planning purposes</li>
                <li>Not spam providers or post false reviews</li>
            </ul>
            
            <h3>4. Booking Workflow</h3>
            <p>When customers book services:</p>
            <ul>
                <li>Providers have 12 hours to accept/reject requests</li>
                <li>Bookings are confirmed only after provider acceptance</li>
                <li>Customers can cancel before provider acceptance with no penalty</li>
                <li>After acceptance, cancellation terms are set by the provider</li>
            </ul>
            
            <h3>5. Payment Terms</h3>
            <p>Samaaroh currently operates as a demo platform for educational purposes. No real payments are processed. In future production versions:</p>
            <ul>
                <li>Payments will be processed securely through verified gateways</li>
                <li>Refund policies will be set by individual providers</li>
                <li>Samaaroh may charge platform fees (clearly disclosed)</li>
            </ul>
            
            <h3>6. Intellectual Property</h3>
            <p>All content on Samaaroh (logos, text, images) is owned by us or licensed. Users retain rights to their uploaded content but grant us license to display it on the platform.</p>
            
            <h3>7. Limitation of Liability</h3>
            <p>Samaaroh is provided "as is." We are not liable for:</p>
            <ul>
                <li>Service quality or provider conduct</li>
                <li>Booking cancellations or disputes</li>
                <li>Indirect damages from platform use</li>
                <li>Acts of God, war, or government actions</li>
            </ul>
            
            <h3>8. Termination</h3>
            <p>We may suspend or terminate accounts that violate these Terms. Users may delete accounts anytime via profile settings.</p>
            
            <h3>9. Governing Law</h3>
            <p>These Terms are governed by laws of Gujarat, India. Any disputes will be resolved in Nadiad courts.</p>
            
            <h3>10. Changes to Terms</h3>
            <p>We may update these Terms. Continued use after changes constitutes acceptance. Check this page periodically for updates.</p>
            
            <h3>11. Contact</h3>
            <p>Questions? Contact us at support@samaaroh.com or +91 98765 43210 (Nadiad office).</p>
            
            <div class="mt-12 pt-8 border-t border-stone-200 text-center">
                <p class="text-stone-500">
                    By using Samaaroh, you acknowledge you have read, understood, and agree to these Terms of Service.
                </p>
                <a href="<?= BASE_URL ?>" class="text-rose-600 hover:underline font-medium mt-4 inline-block">
                    Return to Homepage
                </a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>