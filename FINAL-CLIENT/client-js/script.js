// Single DOMContentLoaded event listener to handle all initialization
document.addEventListener('DOMContentLoaded', function() {
    // Disable smooth scroll restoration on page load
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    
    // Force scroll to top immediately when page loads
    window.scrollTo(0, 0);

    // Initialize all event listeners
    initializeNavigation();

    if (window.innerWidth <= 768) {
        const programTexts = document.querySelectorAll('.program-text');
        
        programTexts.forEach(text => {
            text.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Close other open program texts
                programTexts.forEach(otherText => {
                    if (otherText !== text && otherText.classList.contains('active')) {
                        otherText.classList.remove('active');
                    }
                });
                
                // Toggle current program text
                this.classList.toggle('active');
            });
        });
    }

    // Update the program text click handler
    const programTexts = document.querySelectorAll('.program-text');
    
    programTexts.forEach(text => {
        // Ensure text is closed by default
        text.classList.remove('active');
        
        text.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // If this text is already open, close it
            if (this.classList.contains('active')) {
                this.classList.remove('active');
                return;
            }
            
            // Close any open program texts
            programTexts.forEach(otherText => {
                otherText.classList.remove('active');
            });
            
            // Open this text
            this.classList.add('active');
        });
    });

    // Handle program card clicks on mobile
    const programCards = document.querySelectorAll('.program');
    
    programCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                // Toggle active class on the clicked card
                programCards.forEach(otherCard => {
                    if (otherCard !== card && otherCard.classList.contains('active')) {
                        otherCard.classList.remove('active');
                    }
                });
                
                this.classList.toggle('active');
                e.stopPropagation();
            }
        });
    });

    // Close program text when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.program-text')) {
            programTexts.forEach(text => {
                text.classList.remove('active');
            });
        }
    });

    // Close program text when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && !e.target.closest('.program')) {
            programCards.forEach(card => {
                card.classList.remove('active');
            });
        }
    });

    // Mobile program card handling
    const programs = document.querySelectorAll('.program');
    let activeProgram = null;

    programs.forEach(program => {
        program.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.stopPropagation();
                
                if (activeProgram === this) {
                    // Close if clicking the same program
                    this.classList.remove('active');
                    activeProgram = null;
                } else {
                    // Close previous program if exists
                    if (activeProgram) {
                        activeProgram.classList.remove('active');
                    }
                    // Open clicked program
                    this.classList.add('active');
                    activeProgram = this;
                }
            }
        });
    });

    // Close active program when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && activeProgram && !e.target.closest('.program')) {
            activeProgram.classList.remove('active');
            activeProgram = null;
        }
    });

    // Reset on window resize
    window.addEventListener('resize', function() {
        if (activeProgram) {
            activeProgram.classList.remove('active');
            activeProgram = null;
        }
    });
});

// Update behavior on resize
window.addEventListener('resize', function() {
    const programTexts = document.querySelectorAll('.program-text');
    if (window.innerWidth <= 768) {
        programTexts.forEach(text => {
            text.classList.remove('active');
        });
    }
});

function initializeNavigation() {
    const ctaButton = document.querySelector('.cta-button');
    
    // CTA Button click handler
    if (ctaButton) {
        ctaButton.addEventListener('click', () => {
            alert('Redirecting to the application page...');
            window.location.href = '#admissions';
        });
    }

    // Manually adjusted scroll positions for better section viewing
    const scrollPositions = {
        hero: 0,
        about: window.innerWidth <= 768 ? 435 : 635,    // Adjusted values
        programs: window.innerWidth <= 768 ? 1040 : 1300, // Adjusted values
        contact: window.innerWidth <= 768 ? 2080 : 1800  // Added contact scroll position
    };

    // Navigation link handlers with updated positions
    document.querySelector('.nav-link[href="#hero"]').addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: scrollPositions.hero,
            behavior: 'smooth'
        });
        closeNavMenu(); // Close menu after clicking
    });

    document.querySelector('.nav-link[href="#about"]').addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: scrollPositions.about,
            behavior: 'smooth'
        });
        closeNavMenu(); // Close menu after clicking
    });

    document.querySelector('.nav-link[href="#programs"]').addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: scrollPositions.programs,
            behavior: 'smooth'
        });
        closeNavMenu(); // Close menu after clicking
    });

    document.querySelector('.nav-link[href="#contact"]').addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: scrollPositions.contact,
            behavior: 'smooth'
        });
        closeNavMenu(); // Close menu after clicking
    });

    // Initialize burger menu
    navSlide();
}

// Add this new function to close the nav menu
function closeNavMenu() {
    const nav = document.querySelector('.nav-links');
    const burger = document.querySelector('.burger');
    const navLinks = document.querySelectorAll('.nav-links li');
    
    nav.classList.remove('nav-active');
    burger.classList.remove('toggle');
    navLinks.forEach(link => {
        link.style.animation = '';
    });
}

function navSlide() {
    const burger = document.querySelector('.burger');
    const nav = document.querySelector('.nav-links');
    const navLinks = document.querySelectorAll('.nav-links li');

    // Toggle Navigation
    burger.addEventListener('click', () => {
        // Toggle nav
        nav.classList.toggle('nav-active');

        // Animate links
        navLinks.forEach((link, index) => {
            if (link.style.animation) {
                link.style.animation = '';
            } else {
                link.style.animation = `navLinkFade 0.5s ease forwards ${index / 7 + 0.3}s`;
            }
        });

        // Burger Animation
        burger.classList.toggle('toggle');
    });

    // Close mobile menu when a link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            nav.classList.remove('nav-active');
            burger.classList.remove('toggle');
            navLinks.forEach(link => {
                link.style.animation = '';
            });
        });
    });
}