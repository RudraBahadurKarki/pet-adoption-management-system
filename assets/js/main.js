document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get('registration') === 'registered_pending') {
        const modalElement = document.getElementById('regSuccessModal');
        const myModal = new bootstrap.Modal(modalElement);

        document.getElementById('modalIconContainer').innerHTML = '<i class="fas fa-user-clock fa-4x text-warning"></i>';
        document.getElementById('modalTitle').innerText = "Approval Pending";
        document.getElementById('modalMessage').innerText = " Our admins are still reviewing your documents. Please check back later.";

        myModal.show();
    }

    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }, 4000);
    }

    setTimeout(() => {
        const url = new URL(window.location);
        ['success', 'error', 'registration', 'email'].forEach(p => url.searchParams.delete(p));
        window.history.replaceState({}, document.title, url.pathname + url.search);
    }, 1500);
});

window.addEventListener("pageshow", function (event) {
    var historyTraversal = event.persisted ||
        (typeof window.performance != "undefined" &&
            window.performance.navigation.type === 2);

    if (historyTraversal) {
        window.location.reload();
    }
});