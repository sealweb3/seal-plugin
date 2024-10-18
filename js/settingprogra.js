document.addEventListener('DOMContentLoaded', () => {
    var sealNameInput = document.querySelector('input[name="s_mod_seal_nameprogram"]');
    if (sealNameInput) {
        sealNameInput.disabled = true;
    }
    var sealDescriptionTextarea = document.querySelector('textarea[name="s_mod_seal_descprogram"]');
    if (sealDescriptionTextarea) {
        sealDescriptionTextarea.disabled = true;
    }
    var sealwebsiteInput = document.querySelector('input[name="s_mod_seal_reqprogram"]');
    if (sealwebsiteInput) {
        sealwebsiteInput.disabled = true;
    }
    var sealwebsiteOption = document.querySelector('select[name="s_mod_seal_programmod"]');
    if (sealwebsiteOption) {
        sealwebsiteOption.disabled = true;
    }
    var defaultInfoElements = document.querySelectorAll('.form-defaultinfo');
    defaultInfoElements.forEach((element) => {
        element.style.display = 'none';
    });
});