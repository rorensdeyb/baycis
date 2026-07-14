// Borrower Dashboard Logic

// 1. Mobile Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
}

// 2. Theme Toggle (Light/Dark)
function toggleTheme() {
    const body = document.body;
    const themeIcon = document.querySelector('#theme-toggle i');
    
    if (body.getAttribute('data-theme') === 'dark') {
        body.removeAttribute('data-theme');
        if(themeIcon) {
            themeIcon.classList.remove('bi-sun');
            themeIcon.classList.add('bi-moon');
        }
    } else {
        body.setAttribute('data-theme', 'dark');
        if(themeIcon) {
            themeIcon.classList.remove('bi-moon');
            themeIcon.classList.add('bi-sun');
        }
    }
}

// 3. Network Connection Monitoring (Instant Offline Redirection)
window.addEventListener('offline', () => {
    console.log("Connection lost! Redirecting to offline screen...");
    const currentUrl = encodeURIComponent(window.location.href);
    window.location.href = '/offline?returnTo=' + currentUrl;
});

// Check on initial load just in case
if (!navigator.onLine) {
    const currentUrl = encodeURIComponent(window.location.href);
    window.location.href = '/offline?returnTo=' + currentUrl;
}