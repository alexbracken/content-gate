document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('content-gate-form');
    if (!form) return;

    const contentGate = form.closest('.wp-block-content-gate-registration');
    const gatedContent = document.createElement('div');
    
    // Move all content after the block into gatedContent
    let nextElement = contentGate.nextElementSibling;
    while (nextElement) {
        const current = nextElement;
        nextElement = nextElement.nextElementSibling;
        gatedContent.appendChild(current);
    }
    
    contentGate.querySelector('.content-gate-content').appendChild(gatedContent);

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('action', 'content_gate_submit');

        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                form.parentElement.style.display = 'none';
                contentGate.querySelector('.content-gate-content').style.display = 'block';
            } else {
                alert(data.data);
            }
        });
    });
});
