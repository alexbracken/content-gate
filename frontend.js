jQuery(document).ready(function($) {
    $('.cg-gate-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $gate = $form.closest('.cg-content-gate');
        var $submitBtn = $form.find('input[type="submit"]');
        var name = $form.find('input[name="name"]').val();
        var email = $form.find('input[name="email"]').val();
        var postId = $('#cg-post-id').val();
        var recaptchaToken = $('#cg-recaptcha-token').length ? $('#cg-recaptcha-token').val() : '';
        
        // Show loading state
        $submitBtn.prop('disabled', true).val('Submitting...');
        
        // Add status message container if it doesn't exist
        if (!$gate.find('.cg-status-message').length) {
            $form.after('<div class="cg-status-message" style="display: none;"></div>');
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
                $form.hide();
                $('.cg-message').hide();
                
                // Show content with smooth transition
                var $content = $gate.find('.cg-gated-content');
                $content.css('display', 'block');
                // Brief timeout to allow the display change before starting transition
                setTimeout(function() {
                    $content.addClass('visible');
                }, 10);
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