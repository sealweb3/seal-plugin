document.addEventListener('DOMContentLoaded', function() {
  var form = document.querySelector('form#adminsettings');
  var entityNameInput = document.getElementById('id_s_seal_entityname');
  var entityDescriptionInput = document.getElementById('id_s_seal_entitydescription');
  var contactWebsiteInput = document.getElementById('id_s_seal_contactwebsite');
  var adressListInput = document.getElementById('id_s_seal_adressList');

  function areFieldsFilled() {
    return entityNameInput.value.trim() !== '' &&
           entityDescriptionInput.value.trim() !== '' &&
           contactWebsiteInput.value.trim() !== '' &&
           adressListInput.value.trim() !== '';
  }

  var saveChangesButton = form.querySelector('button[type="submit"].btn.btn-primary');
  var attestationButton = document.getElementById('attestationButton');

  function updateButtonStyles() {
    var allFilled = areFieldsFilled();
    [saveChangesButton, attestationButton].forEach(function(button) {
      if (button) {
        if (allFilled) {
          button.classList.remove('btn-secondary');
          button.classList.add('btn-primary');
          button.disabled = false;
        } else {
          button.classList.remove('btn-primary');
          button.classList.add('btn-secondary');
          button.disabled = true;
        }
      }
    });
  }

  [entityNameInput, entityDescriptionInput, contactWebsiteInput, adressListInput].forEach(function(input) {
    input.addEventListener('input', updateButtonStyles);
  });

  updateButtonStyles();
});