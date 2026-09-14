if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

setTimeout(function () {
    const alert = document.querySelector('.alert');

    if (alert) {
        alert.style.transition = "opacity 0.5s ease";
        alert.style.opacity = "0";

        setTimeout(function () {
            alert.remove();
        }, 500);
    }
}, 4000);




document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editPetForm');
    const fileInput = document.querySelector('input[name="pet_image"]');
    const submitBtn = document.getElementById('submitBtn');

    const initialData = new FormData(form);
    submitBtn.disabled = true;

    const checkChanges = () => {
        const currentData = new FormData(form);
        let hasChanged = false;

        for (let [key, value] of currentData.entries()) {
            if (key === 'pet_image') {
                if (fileInput.files.length > 0) {
                    hasChanged = true;
                    break;
                }
                continue;
            }

            if (value !== initialData.get(key)) {
                hasChanged = true;
                break;
            }
        }

        submitBtn.disabled = !hasChanged;
    };

    form.addEventListener('input', checkChanges);
    fileInput.addEventListener('change', checkChanges);
});




setTimeout(() => {
    const url = new URL(window.location.href);
    url.searchParams.delete('success');
    url.searchParams.delete('msg');
    window.history.replaceState({}, document.title, url.pathname);

    const alert = document.getElementById('auto-alert');

    if (alert) {
        alert.style.transition = "opacity 0.6s ease";
        alert.style.opacity = "0";

        setTimeout(() => alert.remove(), 600);
    }
}, 3000);




if (window.history.replaceState) {
    const url = new URL(window.location.href);
    url.searchParams.delete('success');
    url.searchParams.delete('error');
    window.history.replaceState({ path: url.href }, '', url.href);
}

setTimeout(function () {
    let alert = document.getElementById('status-alert');

    if (alert) {
        alert.style.transition = "opacity 0.5s ease";
        alert.style.opacity = "0";

        setTimeout(() => alert.remove(), 500);
    }
}, 4000);