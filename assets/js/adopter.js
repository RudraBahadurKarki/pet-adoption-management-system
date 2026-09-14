if (window.history.replaceState) {
    const url = new URL(window.location);
    url.searchParams.delete('success');
    url.searchParams.delete('error');
    window.history.replaceState({}, document.title, url.pathname + url.search);
}
