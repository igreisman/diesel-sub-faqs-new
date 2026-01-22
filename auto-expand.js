/**
 * Auto-expand FAQ from URL hash
 * Works with Bootstrap collapse components
 * Place this at the bottom of your FAQ page, after Bootstrap JS loads
 */

(function() {
    // Wait for page to fully load
    window.addEventListener('load', function() {
        // Get the hash from URL (e.g., #faq-collapse-858)
        const hash = window.location.hash;
        
        if (hash && hash.startsWith('#faq-collapse-')) {
            // Find the collapse element
            const targetDiv = document.querySelector(hash);
            
            if (targetDiv) {
                // If using Bootstrap 5
                if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                    const collapseInstance = new bootstrap.Collapse(targetDiv, {
                        toggle: true
                    });
                }
                // If using Bootstrap 4 or jQuery Bootstrap
                else if (typeof $ !== 'undefined' && $.fn.collapse) {
                    $(targetDiv).collapse('show');
                }
                // Fallback: manually add the 'show' class
                else {
                    targetDiv.classList.add('show');
                }
                
                // Scroll to the element after a brief delay (let collapse animation complete)
                setTimeout(function() {
                    targetDiv.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }, 300);
                
                // Optional: Track in Google Analytics
                if (typeof gtag !== 'undefined') {
                    const faqNumber = hash.replace('#faq-collapse-', '');
                    gtag('event', 'faq_video_landing', {
                        'faq_number': faqNumber,
                        'source': 'youtube_short'
                    });
                }
            }
        }
    });
})();
