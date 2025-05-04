jQuery(document).ready(function($) {
    $('#scg-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'scg_verify_user',
            name: $(this).find('input[name="name"]').val(),
            email: $(this).find('input[name="email"]').val(),
            post_id: $(this).find('input[name="post_id"]').val(),
            remember: $(this).find('input[name="remember"]').is(':checked') ? 1 : 0
        };

        $.post(scgAjax.ajaxurl, formData)
            .done(function(response) {
                if (response.success) {
                    $('.scg-form-container').slideUp();
                    $('.scg-hidden-content').slideDown();
                } else {
                    alert('Error: Please try again.');
                }
            })
            .fail(function() {
                alert('Error: Please try again.');
            });
    });
});