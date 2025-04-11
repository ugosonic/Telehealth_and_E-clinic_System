document.addEventListener('DOMContentLoaded', function () {
    // Populate country dropdown on page load
    print_country("country");

    document.getElementById('disability').addEventListener('change', function () {
        var specifyInput = document.getElementById('disability_specify');
        if (this.value === 'Yes') {
            specifyInput.style.display = 'block';
        } else {
            specifyInput.style.display = 'none';
            specifyInput.value = '';
        }
    });

    document.getElementById('id_type').addEventListener('change', function () {
        var idUpload = document.getElementById('id_upload');
        var backIdUpload = document.getElementById('back_id_upload');

        if (this.value === 'Driving Licence' || this.value === 'Residence Card or Permit') {
            idUpload.style.display = 'block';
            backIdUpload.style.display = 'block';
        } else if (this.value === 'International Passport') {
            idUpload.style.display = 'block';
            backIdUpload.style.display = 'none';
        } else {
            idUpload.style.display = 'none';
            backIdUpload.style.display = 'none';
        }
    });

    document.getElementById('file_required').addEventListener('change', function () {
        var idTypeSelect = document.getElementById('id_type');
        var idUpload = document.getElementById('id_upload');
        var backIdUpload = document.getElementById('back_id_upload');

        if (this.checked) {
            idTypeSelect.setAttribute('required', 'required');
            idTypeSelect.dispatchEvent(new Event('change'));
        } else {
            idTypeSelect.removeAttribute('required');
            idUpload.style.display = 'none';
            backIdUpload.style.display = 'none';
        }
    });
});
