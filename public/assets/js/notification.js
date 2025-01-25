document.getElementById('notification-banner').addEventListener('click', function(event) {
    const notificationBanner = document.getElementById('modal-card');
    if(!modalCard.contains(event.target)){
        toggleModal();
    }
});

function toggleModal() {
    const modal = document.getElementById('modal');

    if (modal.hasAttribute('open')) {
        modal.removeAttribute('open');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
    } else {
        modal.setAttribute('open', '');
        modal.classList.remove('opacity-0');
        modal.classList.add('opacity-100');
    }
}
