document.addEventListener('DOMContentLoaded', () => {
    var sealNameInput = document.querySelector('input[name="s_mod_seal_name"]');
    if (sealNameInput) {
        sealNameInput.disabled = true;
    }
    var sealDescriptionTextarea = document.querySelector('textarea[name="s_mod_seal_description"]');
    if (sealDescriptionTextarea) {
        sealDescriptionTextarea.disabled = true;
    }
    var sealwebsiteInput = document.querySelector('input[name="s_mod_seal_website"]');
    if (sealwebsiteInput) {
        sealwebsiteInput.disabled = true;
    }
    var sealadressListTextarea = document.querySelector('textarea[name="s_mod_seal_adressList"]');
    if (sealadressListTextarea) {
        sealadressListTextarea.disabled = true;
    }
    var defaultInfoElements = document.querySelectorAll('.form-defaultinfo');
    defaultInfoElements.forEach((element) => {
        element.style.display = 'none';
    });
});