// js/session_timeout.js

let idleTime = 0;
const IDLE_WARNING_LIMIT = 13 * 60; // 13 Minutes (Warns 2 mins before the 15-min server timeout)
let warningModalActive = false;
let countdownInterval = null;

// Reset idle timer on user interaction events
['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart'].forEach(eventType => {
    document.addEventListener(eventType, () => {
        if (!warningModalActive) {
            idleTime = 0;
        }
    }, true);
});

// Increment idle timer every 1 second
setInterval(() => {
    if (warningModalActive) return; // Don't increment while modal is open
    
    idleTime++;
    if (idleTime >= IDLE_WARNING_LIMIT) {
        triggerInactivityWarning();
    }
}, 1000);

function triggerInactivityWarning() {
    warningModalActive = true;
    let timeLeft = 120; // 2 minutes countdown matching the buffer

    Swal.fire({
        title: 'Inactivity Warning',
        html: `You have been inactive for a while. For security, your session will automatically expire in <b id="session-countdown" class="text-rose-600 font-mono text-lg">120</b> seconds.`,
        icon: 'warning',
        iconColor: '#f59e0b',
        showCancelButton: true,
        confirmButtonText: 'Stay Logged In',
        cancelButtonText: 'Log Out Now',
        confirmButtonColor: '#046a38',
        cancelButtonColor: '#64748b',
        allowOutsideClick: false,
        allowEscapeKey: false,
        background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
        didOpen: () => {
            const countdownEl = document.getElementById('session-countdown');
            countdownInterval = setInterval(() => {
                timeLeft--;
                if (countdownEl) countdownEl.innerText = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'logout.php';
                }
            }, 1000);
        }
    }).then((result) => {
        clearInterval(countdownInterval);
        warningModalActive = false;
        idleTime = 0;

        if (result.isConfirmed) {
            // Ping server to keep session alive
            fetch('api/session_keepalive.php')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        window.location.href = 'login.php?timeout=1';
                    }
                })
                .catch(() => {});
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.location.href = 'logout.php';
        }
    });
}