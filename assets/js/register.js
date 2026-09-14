document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('roleSelect');
    const shelterDocs = document.getElementById('shelter_docs');
    const docInput = document.getElementById('docInput');

    function toggleFields() {
        if (roleSelect.value === 'shelter') {
            shelterDocs.style.display = 'block';
            docInput.required = true;
        } else {
            shelterDocs.style.display = 'none';
            docInput.required = false;
            docInput.value = "";
        }
    }

    roleSelect.addEventListener('change', toggleFields);
    setTimeout(toggleFields, 50);
});

setTimeout(() => {
    const alert = document.getElementById('auto-alert');
    if (alert) {
        alert.style.transition = "opacity 0.6s ease";
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 600);
    }

    if (window.location.search) {
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
}, 5000);