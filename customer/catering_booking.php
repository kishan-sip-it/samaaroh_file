<?php 
include 'config/config.php';
include 'includes/header.php';

// Check if catering selection exists
if (!isset($_SESSION['catering_selection'])) {
    header('Location: ' . BASE_URL . 'customer/catering_menu.php');
    exit();
}

$catering_data = $_SESSION['catering_selection'];
$guest_count = $catering_data['guest_count'];
$selected_items = $catering_data['selected_items'];
$total_cost = $catering_data['total_cost'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Booking | Samaaroh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .heading { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-50">
    <?php include 'includes/navbar.php'; ?>

    <!-- Header Section -->
    <section class="bg-gradient-to-r from-rose-600 to-amber-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="heading text-4xl md:text-5xl font-bold mb-4">Catering Booking</h1>
            <p class="text-xl opacity-90 max-w-3xl mx-auto">
                Review your selection and complete the booking
            </p>
        </div>
    </section>

    <!-- Booking Summary -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Event Details -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Event Details</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Event Date</label>
                        <input type="date" id="eventDate" required 
                               class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Event Time</label>
                        <input type="time" id="eventTime" required 
                               class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Number of Guests</label>
                        <input type="number" id="guestCount" value="<?= $guest_count ?>" readonly 
                               class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl bg-stone-100">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Venue Location</label>
                        <input type="text" id="venueLocation" placeholder="Enter venue address" required 
                               class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Selected Menu Items -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Selected Menu Items</h2>
                <div class="space-y-4">
                    <?php foreach ($selected_items as $item): ?>
                    <div class="flex justify-between items-center p-4 bg-stone-50 rounded-xl">
                        <div>
                            <h3 class="font-semibold text-lg text-stone-800"><?= $item['name'] ?></h3>
                            <p class="text-stone-600">₹<?= number_format($item['price']) ?> per plate × <?= $guest_count ?> guests</p>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-xl text-rose-600">₹<?= number_format($item['total']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Total -->
                <div class="border-t-2 border-stone-200 mt-6 pt-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-semibold text-stone-800">Total Cost</h3>
                            <p class="text-stone-600">For <?= $guest_count ?> guests</p>
                        </div>
                        <div class="text-3xl font-bold text-rose-600">₹<?= number_format($total_cost) ?></div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="heading text-2xl font-bold mb-6 text-stone-800">Contact Information</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Contact Person</label>
                        <input type="text" id="contactPerson" required 
                               class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Mobile Number</label>
                        <input type="tel" id="mobileNumber" required 
                               class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Special Requirements</label>
                        <textarea id="specialRequirements" rows="4" 
                                  class="w-full px-4 py-3 border-2 border-stone-300 rounded-xl focus:border-rose-500 focus:outline-none"
                                  placeholder="Any dietary restrictions or special requests?"></textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <button onclick="window.history.back()" 
                        class="flex-1 bg-stone-200 text-stone-700 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-stone-300 transition">
                    Back to Menu
                </button>
                <button onclick="confirmBooking()" 
                        class="flex-1 bg-rose-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-rose-700 transition transform hover:scale-105 shadow-xl">
                    Confirm Booking
                </button>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
    function confirmBooking() {
        // Get form values
        const eventDate = document.getElementById('eventDate').value;
        const eventTime = document.getElementById('eventTime').value;
        const venueLocation = document.getElementById('venueLocation').value;
        const contactPerson = document.getElementById('contactPerson').value;
        const mobileNumber = document.getElementById('mobileNumber').value;
        const specialRequirements = document.getElementById('specialRequirements').value;

        // Validation
        if (!eventDate || !eventTime || !venueLocation || !contactPerson || !mobileNumber) {
            alert('Please fill in all required fields.');
            return;
        }

        // Create booking data
        const bookingData = {
            event_date: eventDate,
            event_time: eventTime,
            venue_location: venueLocation,
            contact_person: contactPerson,
            mobile_number: mobileNumber,
            special_requirements: specialRequirements,
            guest_count: <?= $guest_count ?>,
            selected_items: <?= json_encode($selected_items) ?>,
            total_cost: <?= $total_cost ?>
        };

        // Send booking request
        fetch('<?= BASE_URL ?>customer/process_catering_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bookingData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Catering booking confirmed! We will contact you soon.');
                window.location.href = '<?= BASE_URL ?>customer/dashboard.php';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing booking. Please try again.');
        });
    }

    // Set minimum date to today
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('eventDate').setAttribute('min', today);
    });
    </script>
</body>
</html>
