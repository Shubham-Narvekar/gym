// document.addEventListener('DOMContentLoaded', function() {
//     // Mobile menu toggle
//     const menuToggle = document.querySelector('.menu-toggle');
//     const navLinks = document.querySelector('.nav-links');

//     menuToggle.addEventListener('click', function() {
//         navLinks.classList.toggle('active');
//     });

//     // Smooth scrolling for navigation links
//     document.querySelectorAll('a[href^="#"]').forEach(anchor => {
//         anchor.addEventListener('click', function (e) {
//             e.preventDefault();
//             const target = document.querySelector(this.getAttribute('href'));
//             if (target) {
//                 target.scrollIntoView({
//                     behavior: 'smooth'
//                 });
//                 // Close mobile menu if open
//                 navLinks.classList.remove('active');
//             }
//         });
//     });

//     // Scroll animations for features
//     const featureCards = document.querySelectorAll('.feature-card');
//     const priceCards = document.querySelectorAll('.price-card');

//     const observerOptions = {
//         threshold: 0.2,
//         rootMargin: '0px'
//     };

//     const fadeInOnScroll = new IntersectionObserver((entries, observer) => {
//         entries.forEach(entry => {
//             if (entry.isIntersecting) {
//                 entry.target.style.opacity = '1';
//                 entry.target.style.transform = 'translateY(0)';
//                 observer.unobserve(entry.target);
//             }
//         });
//     }, observerOptions);

//     // Initialize features with opacity 0
//     featureCards.forEach(card => {
//         card.style.opacity = '0';
//         card.style.transform = 'translateY(20px)';
//         card.style.transition = 'opacity 0.5s, transform 0.5s';
//         fadeInOnScroll.observe(card);
//     });

//     // Initialize price cards with opacity 0
//     priceCards.forEach(card => {
//         card.style.opacity = '0';
//         card.style.transform = 'translateY(20px)';
//         card.style.transition = 'opacity 0.5s, transform 0.5s';
//         fadeInOnScroll.observe(card);
//     });

//     // // Plan button click handlers
//     // const planButtons = document.querySelectorAll('.plan-button');
//     // planButtons.forEach(button => {
//     //     button.addEventListener('click', function() {
//     //         const plan = this.closest('.price-card').querySelector('h3').textContent;
//     //         alert(`Thank you for choosing the ${plan} plan! We'll redirect you to the registration page.`);
//     //     });
//     // });
// });