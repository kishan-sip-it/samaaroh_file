<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/svg+xml" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>favicon.svg" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h1 class="heading text-4xl md:text-5xl font-bold text-stone-800">Terms of Service</h1>
            <p class="text-stone-500 mt-4">
                Last updated: March 2026 | Effective Date: January 1, 2026
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm">
            <div class="prose prose-stone max-w-none">
                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">1. Acceptance of Terms</h2>
                    <p class="text-stone-600 mb-4">
                        By accessing and using Samaaroh ("the Platform"), you accept and agree to be bound by these Terms of Service ("Terms"). 
                        If you do not agree to these Terms, please do not use our Platform.
                    </p>
                    <p class="text-stone-600">
                        Samaaroh is a wedding planning platform connecting families with verified wedding service providers 
                        including bagiwala, party plots, caterers, photographers, and decorators.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">2. Platform Services</h2>
                    <p class="text-stone-600 mb-4">
                        Samaaroh provides the following services:
                    </p>
                    <ul class="list-disc pl-6 text-stone-600 space-y-2">
                        <li>Directory of verified wedding service providers in Nadiad</li>
                        <li>Online booking system for wedding services and packages</li>
                        <li>12-hour vendor acceptance guarantee</li>
                        <li>Secure payment processing</li>
                        <li>Customer support and dispute resolution</li>
                        <li>Wedding planning resources and guidance</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">3. User Accounts</h2>
                    <h3 class="font-bold text-lg text-stone-700 mb-3">3.1 Registration</h3>
                    <p class="text-stone-600 mb-4">
                        To use our Platform, you must create an account and provide accurate, complete, and current information. 
                        You are responsible for maintaining the confidentiality of your account credentials.
                    </p>
                    
                    <h3 class="font-bold text-lg text-stone-700 mb-3">3.2 User Types</h3>
                    <ul class="list-disc pl-6 text-stone-600 space-y-2">
                        <li><strong>Customers:</strong> Families planning weddings seeking vendor services</li>
                        <li><strong>Providers:</strong> Wedding service vendors offering services on the Platform</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">4. Provider Verification</h2>
                    <p class="text-stone-600 mb-4">
                        All service providers on Samaaroh undergo strict verification including:
                    </p>
                    <ul class="list-disc pl-6 text-stone-600 space-y-2">
                        <li>Business registration and license verification</li>
                        <li>Identity verification of business owners</li>
                        <li>Service quality and customer reference checks</li>
                        <li>Insurance and compliance verification</li>
                    </ul>
                    <p class="text-stone-600">
                        Samaaroh reserves the right to remove any provider who fails to maintain our quality standards.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">5. Booking and Payment Terms</h2>
                    <h3 class="font-bold text-lg text-stone-700 mb-3">5.1 Booking Process</h3>
                    <p class="text-stone-600 mb-4">
                        When you book a service through Samaaroh:
                    </p>
                    <ul class="list-disc pl-6 text-stone-600 space-y-2">
                        <li>You submit a booking request with specific requirements</li>
                        <li>Provider has 12 hours to accept or reject the request</li>
                        <li>Upon acceptance, payment is processed securely</li>
                        <li>Booking confirmation is sent to both parties</li>
                    </ul>
                    
                    <h3 class="font-bold text-lg text-stone-700 mb-3">5.2 Payment Terms</h3>
                    <ul class="list-disc pl-6 text-stone-600 space-y-2">
                        <li>Prices displayed are inclusive of all taxes</li>
                        <li>Payment is processed through secure payment gateways</li>
                        <li>Refunds are subject to provider's cancellation policy</li>
                        <li>Samaaroh charges a service fee as indicated during booking</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">6. Cancellation and Refunds</h2>
                    <h3 class="font-bold text-lg text-stone-700 mb-3">6.1 Customer Cancellations</h3>
                    <p class="text-stone-600 mb-4">
                        Customers may cancel bookings with the following refund policy:
                    </p>
                    <ul class="list-disc pl-6 text-stone-600 space-y-2">
                        <li>30+ days before event: Full refund minus service fee</li>
                        <li>15-29 days before event: 75% refund</li>
                        <li>7-14 days before event: 50% refund</li>
                        <li>Less than 7 days: No refund</li>
                    </ul>
                    
                    <h3 class="font-bold text-lg text-stone-700 mb-3">6.2 Provider Cancellations</h3>
                    <p class="text-stone-600">
                        If a provider cancels an accepted booking, Samaaroh will help arrange a replacement provider of equal quality at the same price, or provide a full refund including service fees.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">7. User Responsibilities</h2>
                    <h3 class="font-bold text-lg text-stone-700 mb-3">7.1 Customer Responsibilities</h3>
                    <ul class="list-disc pl-6 text-stone-600 space-y-2">
                        <li>Provide accurate information for bookings</li>
                        <li>Make payments on time</li>
                        <li>Communicate respectfully with providers</li>
                        <li>Follow venue and service guidelines</li>
                        <li>Report issues promptly to Samaaroh support</li>
                    </ul>
                    
                    <h3 class="font-bold text-lg text-stone-700 mb-3">7.2 Provider Responsibilities</h3>
                    <ul class="list-disc pl-6 text-stone-600 space-y-2">
                        <li>Honor confirmed bookings and pricing</li>
                        <li>Provide services as described and agreed</li>
                        <li>Maintain professional conduct and quality</li>
                        <li>Respond to booking requests within 12 hours</li>
                        <li>Keep service information accurate and updated</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">8. Intellectual Property</h2>
                    <p class="text-stone-600 mb-4">
                        All content on Samaaroh, including text, graphics, logos, images, and software, is the property of Samaaroh 
                        and protected by intellectual property laws. Users may not use, reproduce, or distribute our content without 
                        prior written permission.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">9. Privacy and Data Protection</h2>
                    <p class="text-stone-600 mb-4">
                        Your privacy is important to us. Our collection and use of personal information is governed by our 
                        <a href="<?= BASE_URL ?>privacy.php" class="text-rose-600 hover:text-rose-700 underline">Privacy Policy</a>. 
                        By using Samaaroh, you consent to the collection and use of your information as described therein.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">10. Dispute Resolution</h2>
                    <p class="text-stone-600 mb-4">
                        In case of disputes between customers and providers, Samaaroh will:
                    </p>
                    <ul class="list-disc pl-6 text-stone-600 space-y-2">
                        <li>Mediate discussions between parties</li>
                        <li>Provide evidence and documentation as available</li>
                        <li>Make final decisions based on platform policies</li>
                        <li>Arrange replacement services when appropriate</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">11. Limitation of Liability</h2>
                    <p class="text-stone-600 mb-4">
                        Samaaroh is not liable for any indirect, incidental, or consequential damages arising from the use of our Platform. 
                        Our total liability to any user shall not exceed the amount paid for the specific service in question.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">12. Service Modifications</h2>
                    <p class="text-stone-600">
                        Samaaroh reserves the right to modify, suspend, or discontinue any aspect of our Platform at any time. 
                        We will notify users of significant changes via email or Platform notifications.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="heading text-2xl font-bold text-stone-800 mb-4">13. Contact Information</h2>
                    <p class="text-stone-600 mb-4">
                        For questions about these Terms of Service, please contact us:
                    </p>
                    <div class="bg-stone-50 rounded-xl p-4 mt-4">
                        <p class="text-stone-600"><strong>Email:</strong> legal@samaaroh.com</p>
                        <p class="text-stone-600"><strong>Phone:</strong> +91 98765 43210</p>
                        <p class="text-stone-600"><strong>Address:</strong> Available upon request</p>
                    </div>
                </section>

                <section class="mb-8">
                    <p class="text-stone-600 text-sm">
                        <strong>Effective Date:</strong> January 1, 2026<br>
                        <strong>Last Updated:</strong> March 2026
                    </p>
                </section>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="<?= BASE_URL ?>" class="inline-block bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-semibold transition">
                Back to Home
            </a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
