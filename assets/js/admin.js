document.addEventListener("DOMContentLoaded", function () {

    // Auto-close alert
    const alertBox = document.getElementById('auto-close-alert');

    if (alertBox) {
        setTimeout(() => {
            if (typeof bootstrap !== 'undefined') {
                const bsAlert = new bootstrap.Alert(alertBox);
                bsAlert.close();
            } else {
                alertBox.style.transition = "opacity 0.5s ease";
                alertBox.style.opacity = "0";

                setTimeout(() => {
                    alertBox.remove();
                }, 500);
            }
        }, 3000);
    }

    // Remove message parameters from URL
    if (window.history.replaceState) {
        const url = new URL(window.location);

        url.searchParams.delete('success');
        url.searchParams.delete('error');
        url.searchParams.delete('msg');

        window.history.replaceState(
            {},
            document.title,
            url.pathname + url.search
        );
    }

});