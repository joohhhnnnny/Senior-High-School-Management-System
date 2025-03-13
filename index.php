<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISCP</title>
    <link rel="stylesheet" href="FINAL-CLIENT/client-css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Modal styles with scrollbar preservation */
        html {
            scroll-behavior: smooth;
            height: 100%;
        }

        body {
            min-height: 100%;
            overflow-x: hidden;
            position: relative;
            margin: 0;
        }

        /* This ensures the scrollbar is always shown to prevent layout shifts */
        html {
            overflow-y: scroll;
        }

        /* Modal styles */
        .modal {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            overflow-y: auto;
            padding-right: 0 !important; /* Prevent automatic padding adjustment */
        }

        .modal.show {
            opacity: 1;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            max-width: 500px;
            width: 90%;
            position: relative;
            transform: scale(0.7);
            transition: transform 0.3s ease;
            margin: 1.75rem auto; /* Add vertical margin for better spacing */
        }

        .modal.show .modal-content {
            transform: scale(1);
        }

        /* Close button styling */
        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        /* Modal buttons styling */
        .modal-buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .modal-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .modal-button:hover {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
    <div id="applicationModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Choose Application Type</h2>
            <div class="modal-buttons">
                <a href="FINAL-CLIENT/client-html/applyStudent.php" class="modal-button">Student</a>
                <a href="FINAL-CLIENT/client-html/applyProf.php" class="modal-button">Professor</a>
            </div>
        </div>
    </div>
    
    <header>
        <nav>
            <div class="logo">
                <img src="FINAL-CLIENT/client-images/iscp-logo.webp" alt="University Logo" class="logo-image">
                I S C P
            </div>
            <ul class="nav-links">
                <li><a href="#hero" class="nav-link">Home</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="#programs" class="nav-link">Programs</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
                <li><a href="FINAL-ADMIN/Portal-Main/main.php" class="nav-link">Portal</a></li>
            </ul>
            <div class="burger">
                <div class="line1"></div>
                <div class="line2"></div>
                <div class="line3"></div>
            </div>
        </nav>
    </header>

    <section id="hero" class="hero">
        <h5>Welcome to</h5>
        <h1>International State College of the Philippines</h1>
        <p>Aspins, your future begins here.</p>
        <a href="#" class="cta-button" id="openModal">Apply Now</a>
    </section>

    <section id="about" class="about">
        <h2>About Us</h2>
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/LB8YtwpfRd8?si=13PL5AhGL8kZLOez" title="YouTube video player" 
            frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
        <p>International State College of the Philippines is dedicated to providing an out-of-this-world educational experience.
            <br> We Aspinians envision registering our very own Biringan City into the International World Map.
        </p>
    </section>

    <section id="programs" class="programs">
        <h2>Our Programs</h2>
        <div class="program-list">
            <div class="program">
                <div class="program-image-container">
                    <img src="FINAL-CLIENT/client-images/ict.jpg" alt="Bachelor of Science" class="program-image">
                    <div class="program-text">
                        <h3>Information and Communication Technology</h3>
                        <p>Learn about various scientific principles and methods of computers.</p>
                    </div>
                </div>
            </div>
            <div class="program">
                <div class="program-image-container">
                    <img src="FINAL-CLIENT/client-images/aad.jpg" alt="Bachelor of Arts" class="program-image">
                    <div class="program-text">
                        <h3>Arts and Design</h3>
                        <p>Enhance your designing skills.</p>
                    </div>
                </div>
            </div>
            <div class="program">
                <div class="program-image-container">
                    <img src="FINAL-CLIENT/client-images/humss.jpg" alt="Master of Business Administration" class="program-image">
                    <div class="program-text">
                        <h3>Humanities and Social Sciences</h3>
                        <p>Explore the humanities and develop critical thinking skills.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer id="contact" class="footer">
        <div class="footer-container">
            <h2>Contact Us</h2>
            <div class="contact-info">
                <p><i class="fas fa-envelope"></i> aspinkayo@iscp.edu.ph</p>
                <p><i class="fas fa-phone"></i> (123) 456-7890</p>
                <p><i class="fas fa-map-marker-alt"></i> 666 Tondo St, Biringan City, ED 12345</p>
            </div>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 International State College of the Philippines. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="FINAL-CLIENT/client-js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById("applicationModal");
        const btn = document.getElementById("openModal");
        const span = document.getElementsByClassName("close")[0];
        let isModalOpen = false;
        let scrollY;

        // Calculate scrollbar width
        function getScrollbarWidth() {
            return window.innerWidth - document.documentElement.clientWidth;
        }

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!isModalOpen) {
                // Store current scroll position
                scrollY = window.scrollY;
                
                // Get scrollbar width before we hide overflow
                const scrollbarWidth = getScrollbarWidth();
                
                // Lock the body at current position while keeping the scrollbar width intact
                document.body.style.overflow = 'hidden';
                document.body.style.paddingRight = scrollbarWidth + 'px';
                
                // Show modal
                modal.style.display = "flex";
                
                // Force browser reflow before adding show class
                void modal.offsetWidth;
                
                modal.classList.add('show');
                isModalOpen = true;
            }
        });

        function closeModal() {
            if (isModalOpen) {
                // Remove show class first for animation
                modal.classList.remove('show');
                
                setTimeout(() => {
                    modal.style.display = "none";
                    
                    // Restore body scrolling and remove padding
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    
                    // No need to restore scroll position since we didn't move the page
                    
                    isModalOpen = false;
                }, 300);
            }
        }

        // Close with X button
        span.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeModal();
        });

        // Close when clicking outside
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Prevent clicks within modal from closing it
        modal.querySelector('.modal-content').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Close with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && isModalOpen) {
                closeModal();
            }
        });
    });
    </script>
</body>
</html>