document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.gcb-gated-content-block').forEach(function(block) {
        const postId = block.getAttribute('data-post-id');
        const formDiv = block.querySelector('.gcb-gate-form');
        const gatedSection = block.querySelector('.gcb-gated-section');

        // Get labels from data attributes
        const formTitle = formDiv.getAttribute('data-form-title') || 'Unlock this content';
        const nameLabel = formDiv.getAttribute('data-name-label') || 'Name';
        const emailLabel = formDiv.getAttribute('data-email-label') || 'Email';
        const rememberLabel = formDiv.getAttribute('data-remember-label') || 'Remember me for 30 days';
        const submitLabel = formDiv.getAttribute('data-submit-label') || 'Unlock';
        const privacyNotice = formDiv.getAttribute('data-privacy-notice') || 'Your information will not be used for any other purpose.';

        // Check for remember me cookie
        const cookieKey = 'gcb_remember_' + postId;
        if (localStorage.getItem(cookieKey)) {
            formDiv.style.display = 'none';
            gatedSection.style.display = '';
            return;
        }

        // Build form
        formDiv.innerHTML = `
            <form class="gcb-form">
                <h4>${formTitle}</h4>
                <label>${nameLabel}<br><input type="text" name="name" required></label><br>
                <label>${emailLabel}<br><input type="email" name="email" required></label><br>
                <label><input type="checkbox" name="remember"> ${rememberLabel}</label><br>
                <button type="submit">${submitLabel}</button>
                <div class="gcb-privacy">${privacyNotice}</div>
                <div class="gcb-error" style="color:red;display:none;"></div>
            </form>
        `;

        // Load reCAPTCHA v3
        if (typeof gcbRecaptcha !== 'undefined' && gcbRecaptcha.siteKey) {
            const script = document.createElement('script');
            script.src = 'https://www.google.com/recaptcha/api.js?render=' + gcbRecaptcha.siteKey;
            document.head.appendChild(script);
        }

        formDiv.querySelector('.gcb-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = this.name.value.trim();
            const email = this.email.value.trim();
            const remember = this.remember.checked;
            const errorDiv = this.querySelector('.gcb-error');
            errorDiv.style.display = 'none';

            if (!name || !email) {
                errorDiv.textContent = 'Please fill in all fields.';
                errorDiv.style.display = '';
                return;
            }

            // reCAPTCHA v3
            if (typeof grecaptcha !== 'undefined' && gcbRecaptcha.siteKey) {
                grecaptcha.ready(function() {
                    grecaptcha.execute(gcbRecaptcha.siteKey, {action: 'gcb_unlock'}).then(function(token) {
                        submitForm(name, email, remember, token);
                    });
                });
            } else {
                errorDiv.textContent = 'reCAPTCHA not loaded.';
                errorDiv.style.display = '';
            }
        });

        function submitForm(name, email, remember, recaptcha) {
            fetch('/wp-json/gcb/v1/submit', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    name, email, post_id: postId, recaptcha, remember
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    formDiv.style.display = 'none';
                    gatedSection.style.display = '';
                    if (remember) {
                        localStorage.setItem(cookieKey, '1');
                        // Set expiry (30 days)
                        const now = new Date();
                        now.setDate(now.getDate() + 30);
                        localStorage.setItem(cookieKey + '_expires', now.getTime());
                    }
                } else {
                    throw new Error(data.message || 'Submission failed.');
                }
            })
            .catch(err => {
                const errorDiv = formDiv.querySelector('.gcb-error');
                errorDiv.textContent = err.message;
                errorDiv.style.display = '';
            });
        }

        // Clean up expired remember me
        const expires = localStorage.getItem(cookieKey + '_expires');
        if (expires && Date.now() > parseInt(expires, 10)) {
            localStorage.removeItem(cookieKey);
            localStorage.removeItem(cookieKey + '_expires');
        }
    });
});
