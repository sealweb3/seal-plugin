document.addEventListener('DOMContentLoaded', () => {
    var buttons = document.querySelectorAll('button.btn.btn-primary');
    buttons.forEach((button) => {
        if (!button.querySelector('i')) {
            button.style.display = 'none';
        }
    });
    var defaultInfoElements = document.querySelectorAll('.form-defaultinfo');
    defaultInfoElements.forEach((element) => {
        element.style.display = 'none';
    });
});