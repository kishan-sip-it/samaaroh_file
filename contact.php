<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
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

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <?php displayAlert(); ?>

        <div class="text-center mb-16">
            <h1 class="heading text-4xl md:text-5xl font-bold text-stone-800">Get in Touch</h1>
            <p class="text-stone-500 mt-4 max-w-2xl mx-auto">
                Have questions about your wedding planning in Nadiad? We're here to help!
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-lg">
                <h2 class="text-2xl font-bold text-stone-800 mb-6">Send Us a Message</h2>
                
                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Your Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required 
                               class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Phone Number (Optional)</label>
                        <input type="tel" name="phone" 
                               class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                               placeholder="10-digit mobile number"
                               pattern="[0-9]{10}"
                               maxlength="10">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Subject <span class="text-rose-500">*</span></label>
                        <select name="subject" required 
                                class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent bg-white">
                            <option value="">Select Subject</option>
                            <option value="booking">Booking Inquiry</option>
                            <option value="provider">Become a Provider</option>
                            <option value="technical">Technical Support</option>
                            <option value="general">General Question</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Message <span class="text-rose-500">*</span></label>
                        <textarea name="message" rows="5" required 
                                  class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                  placeholder="Tell us about your wedding plans in Nadiad..."></textarea>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 rounded-xl text-lg transition">
                        Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div class="space-y-8">
                <div class="bg-white rounded-2xl border border-stone-200 p-8">
                    <h2 class="text-2xl font-bold text-stone-800 mb-6">Contact Information</h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-rose-600 text-xl">📍</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-stone-800">Our Office</h3>
                                <p class="text-stone-600 mt-1">
                                    Samaaroh Wedding Planning<br>
                                    Near Sangath Circle, Nadiad<br>
                                    Gujarat 387001
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-rose-600 text-xl">📱</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-stone-800">Phone Support</h3>
                                <p class="text-stone-600 mt-1">
                                    <a href="tel:+919876543210" class="hover:text-rose-600 transition">+91 98765 43210</a><br>
                                    <span class="text-sm text-stone-500">Mon-Sat: 9 AM - 8 PM</span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-rose-600 text-xl">✉️</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-stone-800">Email</h3>
                                <p class="text-stone-600 mt-1">
                                    <a href="mailto:support@samaaroh.com" class="hover:text-rose-600 transition">support@samaaroh.com</a><br>
                                    <span class="text-sm text-stone-500">Response within 24 hours</span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-rose-600 text-xl">🕒</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-stone-800">Office Hours</h3>
                                <p class="text-stone-600 mt-1">
                                    Monday - Saturday: 9:00 AM - 8:00 PM<br>
                                    Sunday: 10:00 AM - 6:00 PM<br>
                                    <span class="text-sm text-amber-600">Closed on major Gujarati festivals</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-amber-50 rounded-2xl border border-amber-200 p-6">
                    <h3 class="font-bold text-lg text-amber-800 mb-3">💡 Wedding Planning Tips</h3>
                    <ul class="text-amber-700 text-sm space-y-2">
                        <li>• Book Das Bagiwala services at least 3 months before your wedding date</li>
                        <li>• Visit party plots in Sangath/Mahudi Road area for best availability</li>
                        <li>• Confirm catering menu 1 month before wedding</li>
                        <li>• Schedule pre-wedding photoshoot 2 weeks before ceremony</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
