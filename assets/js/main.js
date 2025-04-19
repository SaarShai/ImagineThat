// Tab switching for dashboard
window.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(tc => tc.style.display = 'none');
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).style.display = 'block';
        });
    });
});
