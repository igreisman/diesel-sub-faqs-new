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
        
        function tryExpand(retries) {
            if (hash && hash.startsWith('#faq-collapse-')) {
                const targetDiv = document.querySelector(hash);
                if (targetDiv) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                        new bootstrap.Collapse(targetDiv, { toggle: true });
                    } else if (typeof $ !== 'undefined' && $.fn.collapse) {
                        $(targetDiv).collapse('show');
                    } else {
                        targetDiv.classList.add('show');
                    }
                    setTimeout(function() {
                        targetDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 300);
                    if (typeof gtag !== 'undefined') {
                        const faqNumber = hash.replace('#faq-collapse-', '');
                        gtag('event', 'faq_video_landing', {
                            'faq_number': faqNumber,
                            'source': 'youtube_short'
                        });
                    }
                } else if (retries > 0) {
                    setTimeout(function() { tryExpand(retries - 1); }, 200);
                }
            }
        }
        tryExpand(10);
    });
})();
