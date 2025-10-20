<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - InnoVision Agricultural Platform</title>
    <link rel="stylesheet" href="../styles/contact.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-seedling"></i>
                <span>InnoVision</span>
            </div>
            <div class="nav-links">
                <a href="../index.html">Home</a>
                <a href="about.php">About</a>
                <a href="features.php">Features</a>
                <a href="contact.php" class="active">Contact</a>
            </div>
        </div>
    </nav>

    <main class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Get in <span class="highlight">Touch</span></h1>
                <p class="hero-subtitle">
                    We'd love to hear from you. Send us a message and we'll respond as soon as possible.
                </p>
            </div>
        </div>
    </main>

    <section class="contact-section">
        <div class="container">
            <div class="contact-content">
                <div class="contact-info">
                    <h2>Contact Information</h2>
                    <p>Reach out to us through any of the following channels:</p>
                    
                    <div class="info-cards">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <h3>Address</h3>
                                <p>123 Agricultural Innovation Center<br>
                                Farm Technology District<br>
                                Metro Manila, Philippines 1000</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <h3>Phone</h3>
                                <p>+63 2 1234 5678<br>
                                +63 917 123 4567</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <h3>Email</h3>
                                <p>info@innovision.com<br>
                                support@innovision.com</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-content">
                                <h3>Business Hours</h3>
                                <p>Monday - Friday: 8:00 AM - 6:00 PM<br>
                                Saturday: 9:00 AM - 4:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h2>Send us a Message</h2>
                    <form action="contact.php" method="POST" class="form">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="general">General Inquiry</option>
                                <option value="support">Technical Support</option>
                                <option value="partnership">Partnership</option>
                                <option value="feedback">Feedback</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required placeholder="Tell us how we can help you..."></textarea>
                        </div>
                        
                        <button type="submit" name="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3>How do I register as a farmer?</h3>
                    <p>Simply click on "Register as Seller" and fill out the registration form with your farming details and valid identification documents.</p>
                </div>
                
                <div class="faq-item">
                    <h3>How do I register as a buyer?</h3>
                    <p>Click on "Register as Buyer" and complete the registration process with your contact information and supporting documents.</p>
                </div>
                
                <div class="faq-item">
                    <h3>Is the platform free to use?</h3>
                    <p>Yes, InnoVision is free for both farmers and buyers. We only charge a small commission on successful transactions.</p>
                </div>
                
                <div class="faq-item">
                    <h3>How do I verify my account?</h3>
                    <p>After registration, you'll receive an email with verification instructions. Complete the verification process to access all platform features.</p>
                </div>
                
                <div class="faq-item">
                    <h3>What payment methods are accepted?</h3>
                    <p>We accept various payment methods including bank transfers, digital wallets, and cash on delivery for certain transactions.</p>
                </div>
                
                <div class="faq-item">
                    <h3>How do I contact customer support?</h3>
                    <p>You can reach our support team through the contact form above, email us at support@innovision.com, or call our hotline during business hours.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-seedling"></i>
                        <span>InnoVision</span>
                    </div>
                    <p>Empowering agricultural communities through technology and innovation.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../index.html">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="features.php">Features</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Registration</h4>
                    <ul>
                        <li><a href="../authentication/login.php">Login</a></li>
                        <li><a href="../authentication/seller/req.php">Seller Registration</a></li>
                        <li><a href="../authentication/buyer/req.php">Buyer Registration</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 InnoVision. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>

<?php
// Handle contact form submission
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "<script>alert('Please fill in all required fields.');</script>";
    } else {
        // Here you would typically send an email or save to database
        // For now, we'll just show a success message
        echo "<script>alert('Thank you for your message! We will get back to you soon.');</script>";
    }
}
?>
