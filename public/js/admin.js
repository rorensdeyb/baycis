// Admin Dashboard Logic

function toggleTheme() {
    const body = document.body;
    const themeIcon = document.querySelector('#theme-toggle i');
    
    // Check if we are currently in dark mode
    if (body.getAttribute('data-theme') === 'dark') {
        // Switch to Light Mode
        body.removeAttribute('data-theme');
        themeIcon.classList.remove('bi-sun');
        themeIcon.classList.add('bi-moon'); // Change icon to moon
    } else {
        // Switch to Dark Mode
        body.setAttribute('data-theme', 'dark');
        themeIcon.classList.remove('bi-moon');
        themeIcon.classList.add('bi-sun'); // Change icon to sun
    }
}
// --- NETWORK CONNECTION MONITORING ---

window.addEventListener('offline', () => {
    console.log("Connection lost! Redirecting to offline screen...");
    // Grab the exact URL the user is currently on
    const currentUrl = encodeURIComponent(window.location.href);
    // Send it to the offline page as a parameter
    window.location.href = '/offline?returnTo=' + currentUrl;
});


function showNotify(message, type = 'success') {
    const toastElement = document.getElementById('liveToast');
    const toastHeader = document.getElementById('toastHeader');
    const toastMessage = document.getElementById('toastMessage');
    
    // Set colors based on type
    toastHeader.className = `d-flex align-items-center p-2 rounded-3 ${type}`;
    toastMessage.innerText = message;

    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
}