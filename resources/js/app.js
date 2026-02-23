import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Numeric Formatter
window.formatNumber = function(n) {
    if (!n) return "";
    let value = n.toString().replace(/\D/g, "");
    if (value.length > 1) value = value.replace(/^0+/, '');
    return value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

//  Global UI Initialization
window.initGlobalUI = function() {
    
    //  Handle Number Formatting
    document.querySelectorAll('.number-format').forEach(input => {
        input.value = window.formatNumber(input.value);
        input.addEventListener('focus', function() { if (this.value === '0') this.value = ''; });
        input.addEventListener('blur', function() { if (this.value === '') this.value = '0'; });
        input.addEventListener('input', (e) => {
            const pos = e.target.selectionStart;
            const oldLen = e.target.value.length;
            e.target.value = window.formatNumber(e.target.value);
            const newLen = e.target.value.length;
            e.target.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
        });
    });

    //  Handle Form Submissions (Loading + Commas + Multiple Buttons)
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            
            //  Get the exact button that triggered the submit
            const submitBtn = e.submitter; 

            //  Strip commas from numeric fields
            this.querySelectorAll('.number-format').forEach(input => {
                input.value = input.value.replace(/,/g, '') || 0;
            });

            //  Handle the Loading State
            if (submitBtn && submitBtn.hasAttribute('data-loading-text')) {
                
                
                if (submitBtn.name && submitBtn.value) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = submitBtn.name;
                    hiddenInput.value = submitBtn.value;
                    this.appendChild(hiddenInput);
                }

                
                submitBtn.disabled = true;
                submitBtn.innerHTML = submitBtn.getAttribute('data-loading-text');
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            }
        });
    });

    // Auto-fade alerts
    document.querySelectorAll('.auto-fade').forEach(alert => {
        setTimeout(() => {
            alert.classList.add('opacity-0');
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
};

document.addEventListener('DOMContentLoaded', window.initGlobalUI);