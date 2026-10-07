document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('previewContent');
    if (!box) return;
    var params = new URLSearchParams(window.location.search);
    var note = params.get('note');
    if (!note && window.location.hash) {
        note = decodeURIComponent(window.location.hash.slice(1));
    }
    if (note) {
        box.textContent = note;
    }
});