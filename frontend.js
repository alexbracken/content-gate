jQuery(document).ready(function($) {
    $('.cg-gate-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $gate = $form.closest('.cg-content-gate');
        var name = $form.find('input[name="name"]').val();
        var email = $form.find('input[name="email"]').val();

        $.post(cg_ajax.ajax_url, {
            action: 'cg_handle_form_submission',
            nonce: cg_ajax.nonce,
            name: name,
            email: email
        }, function(response) {
            if (response.success) {
                $form.hide();
                $gate.find('.cg-gated-content').show();
            } else {
                alert(response.data);
            }
        });
    });
});