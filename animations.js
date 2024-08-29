document.addEventListener('DOMContentLoaded', function() {
    gsap.from(".animate-text", {duration: 1, opacity: 0, y: -50, ease: "power3.out"});
    
    gsap.from(".animate-row", {
        duration: 0.8, 
        opacity: 0, 
        x: -50, 
        stagger: 0.1, 
        ease: "power3.out"
    });
    
    gsap.from(".animate-button", {
        duration: 0.8, 
        opacity: 0, 
        y: 50, 
        stagger: 0.2, 
        ease: "power3.out", 
        delay: 0.5
    });
});

// Tablo satırlarına hover efekti
const tableRows = document.querySelectorAll('.table tbody tr');
tableRows.forEach(row => {
    row.addEventListener('mouseenter', () => {
        gsap.to(row, {duration: 0.3, backgroundColor: 'rgba(155, 89, 182, 0.1)', ease: 'power1.out'});
    });
    row.addEventListener('mouseleave', () => {
        gsap.to(row, {duration: 0.3, backgroundColor: 'transparent', ease: 'power1.out'});
    });
});

// Butonlara hover efekti
const buttons = document.querySelectorAll('.btn-custom');
buttons.forEach(button => {
    button.addEventListener('mouseenter', () => {
        gsap.to(button, {duration: 0.3, y: -3, boxShadow: '0 4px 15px rgba(0, 0, 0, 0.2)', ease: 'power1.out'});
    });
    button.addEventListener('mouseleave', () => {
        gsap.to(button, {duration: 0.3, y: 0, boxShadow: '0 0 0 rgba(0, 0, 0, 0)', ease: 'power1.out'});
    });
});