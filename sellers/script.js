document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.nav-links a');
    const contentDiv = document.getElementById('content');

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetUrl = this.getAttribute('data-target');

            fetch(targetUrl)
                .then(response => response.text())
                .then(data => {
                    contentDiv.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error loading content:', error);
                    contentDiv.innerHTML = '<p>Không thể tải nội dung.</p>';
                });
        });
    });
});
