jQuery(document).ready(function($) {
    // Check if user has already submitted form
    function checkUserAccess(postId) {
        // Skip if remember feature is disabled
        if (cg_ajax.remember_user !== 'yes') {
            return false;
        }
        
        // Try to get stored submissions from localStorage
        const storedSubmissions = localStorage.getItem('cg_submissions');
        if (storedSubmissions) {
            const submissions = JSON.parse(storedSubmissions);
            // Return true if this post is already unlocked
            if (submissions.includes(parseInt(postId))) {
                return true;
            }
        }
        
        // Check for cookie fallback (for older browsers)
        const cookieValue = getCookie('cg_access');
        if (cookieValue && cookieValue === '1') {
            return true;
        }
        
        return false;
    }
    
    // Helper function to get cookie value
    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }
    
    // Set cookie with expiration
    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
    }
    
    // Handle remembering the user submission
    function rememberSubmission(postId) {
        // Skip if remember feature is disabled
        if (cg_ajax.remember_user !== 'yes') {
            return;
        }
        
        // Store in localStorage
        let submissions = [];
        const storedSubmissions = localStorage.getItem('cg_submissions');
        
        if (storedSubmissions) {
            submissions = JSON.parse(storedSubmissions);
        }
        
        if (!submissions.includes(parseInt(postId))) {
            submissions.push(parseInt(postId));
        }
        
        localStorage.setItem('cg_submissions', JSON.stringify(submissions));
        
        // Set cookie as fallback
        const cookieDays = parseInt(cg_ajax.cookie_days) || 30;
        setCookie('cg_access', '1', cookieDays);
    }
    
    // Function to reveal encrypted content
    function revealEncryptedContent($gate) {
        const $content = $gate.find('.cg-gated-content');
        const encryptedData = $gate.find('#cg-content-data').val();
        
        if (encryptedData) {
            // Decode the base64 content
            try {
                const decodedContent = atob(encryptedData);
                // Insert the content
                $content.html(decodedContent);
            } catch (e) {
                console.error('Error decoding content:', e);
                $content.html('<p>Error loading content. Please refresh the page.</p>');
            }
        }
        
        // Display the content with animation
        $content.css('display', 'block');
        setTimeout(function() {
            $content.addClass('visible');
        }, 10);
    }

    // Auto-unlock content if user is already remembered
    $('.cg-content-gate').each(function() {
        const $gate = $(this);
        const postId = $gate.find('#cg-post-id').val();
        
        if (checkUserAccess(postId)) {
            // User already has access, show content
            $gate.find('.cg-gate-form').hide();
            $gate.find('.cg-message').hide();
            
            // Decrypt and display content
            revealEncryptedContent($gate);
        }
    });

    // Form submission handler
    $('.cg-gate-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $gate = $form.closest('.cg-content-gate');
        var $submitBtn = $form.find('input[type="submit"]');
        var name = $form.find('input[name="name"]').val();
        var email = $form.find('input[name="email"]').val();
        var postId = $gate.find('#cg-post-id').val();
        var recaptchaToken = $gate.find('#cg-recaptcha-token').length ? $gate.find('#cg-recaptcha-token').val() : '';
        
        // Show loading state
        $submitBtn.prop('disabled', true).val('Submitting...');
        
        // Add status message container if it doesn't exist
        if (!$gate.find('.cg-status-message').length) {
            $form.after('<div class="cg-status-message" role="alert" style="display: none;"></div>');
        }
        var $status = $gate.find('.cg-status-message');
        
        // Hide any previous status messages
        $status.hide();

        $.post(cg_ajax.ajax_url, {
            action: 'cg_handle_form_submission',
            nonce: cg_ajax.nonce,
            name: name,
            email: email,
            post_id: postId,
            recaptcha: recaptchaToken,
            website: $form.find('input[name="website"]').val() // Honeypot field
        }, function(response) {
            if (response.success) {
                // Remember this user for future gated content
                rememberSubmission(postId);
                
                $form.hide();
                $gate.find('.cg-message').hide();
                
                // Decrypt and show content
                revealEncryptedContent($gate);
            } else {
                $status.removeClass('cg-success').addClass('cg-error')
                       .text(response.data || 'An error occurred. Please try again.')
                       .show();
                $submitBtn.prop('disabled', false).val('Submit');
            }
        }).fail(function() {
            $status.removeClass('cg-success').addClass('cg-error')
                   .text('Connection error. Please try again.')
                   .show();
            $submitBtn.prop('disabled', false).val('Submit');
        });
    });
});