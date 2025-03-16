jQuery(document).ready(function($) {
    // Listen for the wpcf7mailsent event triggered by Contact Form 7 on successful submission.
    document.addEventListener('wpcf7mailsent', function(event) {
        // Locate the content gate container that wraps the form.
        var $gate = $(event.target).closest('.cg-content-gate');
        if ( $gate.length ) {
            // Extract submitted inputs from the event details.
            var inputs = event.detail.inputs;
            var data = {
                action: 'cg_handle_form_submission',
                nonce: cg_ajax.nonce,
                name: '',
                email: ''
            };

            inputs.forEach(function(input) {
                if ( input.name === 'your-name' ) {
                    data.name = input.value;
                }
                if ( input.name === 'your-email' ) {
                    data.email = input.value;
                }
            });

            // Make the AJAX call to our custom handler.
            $.post(cg_ajax.ajax_url, data, function(response) {
                if ( response.success ) {
                    $gate.find('.cg-gate-form').hide();
                    $gate.find('.cg-gated-content').show();
                } else {
                    alert('Validation failed: ' + response.data);
                }
            });
        }
    }, false);
});
