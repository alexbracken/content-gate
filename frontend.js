jQuery(function($){
    $('.cg-content-gate').each(function(){
        var $gate = $(this);
        var postId = $gate.find('.cg-post-id').val();
        var unlocked = localStorage.getItem('cg_unlocked_' + postId);

        if (unlocked) {
            showContent($gate);
        }

        $gate.find('.cg-gate-form').on('submit', function(e){
            e.preventDefault();
            var formData = {
                name: $(this).find('input[name="name"]').val(),
                email: $(this).find('input[name="email"]').val(),
                post_id: postId
            };

            // Send form data via AJAX
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'cg_save_submission',
                    data: formData
                },
                success: function(response) {
                    if (response.success) {
                        localStorage.setItem('cg_unlocked_' + postId, '1');
                        showContent($gate);
                    }
                }
            });
        });

        function showContent($gate) {
            var $form = $gate.find('.cg-gate-form');
            var $content = $gate.find('.cg-gated-content');
            var encoded = $content.data('content');
            $form.hide();
            $content.html(atob(encoded)).show();
        }
    });
});