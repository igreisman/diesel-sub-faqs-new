    </main>

    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>A comprehensive resource for diesel-electric submarine information, with detailed focus on World War II US submarines.</p>
                </div>
                <div class="col-md-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light">Home</a></li>
                        <li><a href="search.php" class="text-light">Search</a></li>
                        <li><a href="about.php" class="text-light">About</a></li>
                        <li><a href="videos.php" class="text-light">Videos</a></li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted d-block">Last updated<a class="text-muted" href="admin/login.php" style="text-decoration:none; cursor: default;">:</a> <?php echo date('F j, Y'); ?></small>
                    <small class="text-muted">Webmaster: Irving Greisman</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    
    <!-- Exit Intent Feedback System -->
    <script>
    let exitIntentShown = false;
    
    function showExitIntentFeedback() {
        if (exitIntentShown || localStorage.getItem('exit_feedback_shown')) return;
        
        exitIntentShown = true;
        localStorage.setItem('exit_feedback_shown', 'true');
        
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'exitFeedbackModal';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            Wait! Before you go...
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Did you find what you were looking for?</strong></p>
                        <p>Your feedback helps us improve this submarine knowledge base for everyone!</p>
                        <div class="d-grid gap-2">
                            <button onclick="quickFeedback('helpful')" class="btn btn-success">
                                <i class="fas fa-thumbs-up"></i> Yes, very helpful!
                            </button>
                            <button onclick="quickFeedback('partial')" class="btn btn-warning">
                                <i class="fas fa-meh"></i> Partially, but could be better
                            </button>
                            <button onclick="quickFeedback('not_helpful')" class="btn btn-danger">
                                <i class="fas fa-thumbs-down"></i> No, I need more help
                            </button>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="feedback.php" class="btn btn-link">Share detailed feedback</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
    
    function quickFeedback(type) {
        // Send quick feedback
        fetch('api/quick-feedback.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                type: 'exit_intent',
                value: type,
                page: window.location.pathname
            })
        });
        
        // Show thank you and close
        document.querySelector('#exitFeedbackModal .modal-body').innerHTML = `
            <div class="text-center">
                <i class="fas fa-heart text-danger fa-3x mb-3"></i>
                <h4>Thank you!</h4>
                <p>Your feedback helps us improve. Safe sailing! ⚓</p>
            </div>
        `;
        
        setTimeout(() => {
            bootstrap.Modal.getInstance(document.getElementById('exitFeedbackModal')).hide();
        }, 2000);
    }
    
    // Detect exit intent (mouse leaving top of viewport)
    document.addEventListener('mouseout', function(e) {
        // Only trigger if mouse leaves through the top of the viewport
        // This typically means user is heading to close tab or navigate away
        if (!e.relatedTarget && e.clientY <= 10) {
            showExitIntentFeedback();
        }
    });
    </script>

    <!-- Auto-adjust table column widths based on rendered content -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const tables = Array.from(document.querySelectorAll('table.md-table, table.table'));
            if (!tables.length) return;

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            tables.forEach(table => {
                // prefer header/body cells; ignore tables used for layout if they have role or aria-hidden
                const rows = [];
                const thead = table.querySelectorAll('thead tr');
                const tbody = table.querySelectorAll('tbody tr');
                thead.forEach(r => rows.push(r));
                tbody.forEach(r => rows.push(r));
                if (!rows.length) return;

                // Determine column count (max cells in any row)
                let colCount = 0;
                rows.forEach(r => { colCount = Math.max(colCount, r.children.length); });
                if (colCount === 0) return;

                // Use table font metrics (fallback to body)
                const style = window.getComputedStyle(table);
                const font = `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
                ctx.font = font;

                // Compute max width per column (pixels)
                const maxWidths = new Array(colCount).fill(0);
                rows.forEach(r => {
                    Array.from(r.children).forEach((cell, idx) => {
                        const text = (cell.innerText || cell.textContent || '').trim();
                        // small guard for empty cells
                        const measure = text ? ctx.measureText(text).width : 10;
                        // include cell horizontal padding
                        const cs = window.getComputedStyle(cell);
                        const padding = parseFloat(cs.paddingLeft || 0) + parseFloat(cs.paddingRight || 0);
                        const total = measure + padding;
                        if (total > maxWidths[idx]) maxWidths[idx] = total;
                    });
                });

                const contentTotalPx = maxWidths.reduce((s, v) => s + v, 0) || 1;
                const parent = table.parentElement || table.closest('.container') || document.body;
                const parentWidth = parent ? parent.clientWidth : window.innerWidth;

                // If the content total is less than the parent width, constrain the table
                const shouldUsePixelWidths = contentTotalPx < parentWidth;

                if (shouldUsePixelWidths) {
                    // Apply fixed pixel widths and set table width to content width
                    let colgroup = table.querySelector('colgroup');
                    if (!colgroup) {
                        colgroup = document.createElement('colgroup');
                        table.insertBefore(colgroup, table.firstChild);
                    } else {
                        while (colgroup.firstChild) colgroup.removeChild(colgroup.firstChild);
                    }
                    // Limit any single column to a maximum fraction of the parent width
                    const maxColFraction = 0.7; // no column larger than 70% of parent
                    const maxColPx = Math.max(100, Math.round(parentWidth * maxColFraction));

                    // Build capped widths array
                    let capped = [];
                    let cappedTotal = 0;
                    for (let i = 0; i < colCount; i++) {
                        const raw = Math.max(20, Math.round(maxWidths[i]));
                        const w = Math.min(raw, maxColPx);
                        capped.push(w);
                        cappedTotal += w;
                    }

                    // If capped total exceeds parentWidth, scale down proportionally
                    let finalWidths = capped;
                    if (cappedTotal > parentWidth) {
                        const scale = parentWidth / cappedTotal;
                        finalWidths = capped.map(w => Math.max(20, Math.round(w * scale)));
                        cappedTotal = finalWidths.reduce((s, v) => s + v, 0);
                    }

                    for (let i = 0; i < colCount; i++) {
                        const col = document.createElement('col');
                        col.style.width = finalWidths[i] + 'px';
                        colgroup.appendChild(col);
                    }

                    // Set explicit table width to final measured width (bounded by parent)
                    const tableWidthPx = Math.min(Math.round(cappedTotal), parentWidth);
                    table.style.width = tableWidthPx + 'px';
                    table.style.maxWidth = '100%';
                    table.style.marginLeft = 'auto';
                    table.style.marginRight = 'auto';
                } else {
                    // Use percentage widths as before
                    const total = contentTotalPx || 1;
                    const minPct = 5;
                    let widths = maxWidths.map(w => Math.max(minPct, Math.round((w / total) * 100)));
                    const sum = widths.reduce((s, v) => s + v, 0);
                    if (sum !== 100) {
                        const diff = 100 - sum;
                        let maxIdx = 0; let maxVal = widths[0];
                        for (let i = 1; i < widths.length; i++) if (widths[i] > maxVal) { maxVal = widths[i]; maxIdx = i; }
                        widths[maxIdx] += diff;
                    }

                    let colgroup = table.querySelector('colgroup');
                    if (!colgroup) {
                        colgroup = document.createElement('colgroup');
                        table.insertBefore(colgroup, table.firstChild);
                    } else {
                        while (colgroup.firstChild) colgroup.removeChild(colgroup.firstChild);
                    }

                    for (let i = 0; i < colCount; i++) {
                        const col = document.createElement('col');
                        col.style.width = widths[i] + '%';
                        colgroup.appendChild(col);
                    }

                    // Ensure table can expand to full width when necessary
                    table.style.width = '';
                    table.style.maxWidth = '100%';
                }
            });
        } catch (e) {
            console.warn('Table width adjustment failed', e);
        }
    });
    </script>
</body>
</html>
