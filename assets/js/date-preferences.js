/**
 * Date Preferences Module
 * Allows users to select their preferred date format across the site
 */

const DatePreferences = {
    // Available date formats
    formats: {
        'full': { label: 'Full (January 15, 2024, 3:45 PM)', example: 'January 15, 2024, 3:45 PM' },
        'short': { label: 'Short (Jan 15, 2024, 3:45 PM)', example: 'Jan 15, 2024, 3:45 PM' },
        'numeric-us': { label: 'Numeric US (01/15/2024 3:45 PM)', example: '01/15/2024 3:45 PM' },
        'numeric-intl': { label: 'Numeric Intl (15/01/2024 15:45)', example: '15/01/2024 15:45' },
        'iso': { label: 'ISO 8601 (2024-01-15 15:45)', example: '2024-01-15 15:45' }
    },

    // Get current preference from localStorage
    getPreference() {
        return localStorage.getItem('dateFormat') || 'full';
    },

    // Set preference in localStorage
    setPreference(format) {
        localStorage.setItem('dateFormat', format);
        this.updateAllDates();
        this.adjustColumnWidths();
    },

    // Format a date string according to preference
    formatDate(dateString, format) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;

        format = format || this.getPreference();

        const options = {
            'full': {
                year: 'numeric', month: 'long', day: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true
            },
            'short': {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true
            },
            'numeric-us': {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: 'numeric', minute: '2-digit', hour12: true
            },
            'numeric-intl': {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', hour12: false
            },
            'iso': {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', hour12: false
            }
        };

        let formatted = date.toLocaleString('en-US', options[format]);

        // Special formatting for ISO and numeric-intl
        if (format === 'iso') {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            formatted = `${year}-${month}-${day} ${hours}:${minutes}`;
        } else if (format === 'numeric-intl') {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            formatted = `${day}/${month}/${year} ${hours}:${minutes}`;
        }

        return formatted;
    },

    // Update all dates on the page
    updateAllDates() {
        const format = this.getPreference();
        document.querySelectorAll('[data-date]').forEach(element => {
            const dateString = element.getAttribute('data-date');
            element.textContent = this.formatDate(dateString, format);
        });
    },

    // Adjust column widths based on date format
    adjustColumnWidths() {
        const format = this.getPreference();
        const widths = {
            'full': '200px',
            'short': '170px',
            'numeric-us': '155px',
            'numeric-intl': '155px',
            'iso': '145px'
        };

        // Update Created and Updated column widths
        document.querySelectorAll('th:has([data-date]), td:has([data-date])').forEach(cell => {
            cell.style.width = widths[format];
            cell.style.minWidth = widths[format];
        });
    },

    // Create the preferences widget
    createWidget() {
        const container = document.getElementById('date-preferences-container');
        if (!container) return;

        const currentFormat = this.getPreference();
        const currentLabel = this.formats[currentFormat].label.split(' (')[0];

        const widget = document.createElement('li');
        widget.className = 'nav-item dropdown';
        widget.innerHTML = `
            <a class="nav-link dropdown-toggle" href="#" id="dateFormatDropdown" role="button" 
               data-bs-toggle="dropdown" aria-expanded="false">
                <i class="far fa-calendar"></i> ${currentLabel}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dateFormatDropdown">
                <li><h6 class="dropdown-header">Date Format</h6></li>
                ${Object.entries(this.formats).map(([key, value]) => `
                    <li>
                        <a class="dropdown-item ${key === currentFormat ? 'active' : ''}" 
                           href="#" data-format="${key}">
                            ${value.label}
                        </a>
                    </li>
                `).join('')}
            </ul>
        `;

        container.appendChild(widget);

        // Add event listeners
        widget.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const format = e.target.getAttribute('data-format');
                this.setPreference(format);
                
                // Update dropdown text
                const dropdownToggle = widget.querySelector('#dateFormatDropdown');
                const newLabel = this.formats[format].label.split(' (')[0];
                dropdownToggle.innerHTML = `<i class="far fa-calendar"></i> ${newLabel}`;
                
                // Update active state
                widget.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
                e.target.classList.add('active');
            });
        });
    },

    // Initialize the module
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.createWidget();
                this.updateAllDates();
                this.adjustColumnWidths();
            });
        } else {
            this.createWidget();
            this.updateAllDates();
            this.adjustColumnWidths();
        }
    }
};

// Initialize when script loads
DatePreferences.init();
