document.addEventListener('DOMContentLoaded', function() {
    const csvInput = document.getElementById('csvInput');
    const uploadForm = document.getElementById('uploadForm');
    const calculateForm = document.getElementById('calculateForm');
    const loader = document.getElementById('loader-overlay');

    const showLoader = () => { if(loader) loader.style.display = 'flex'; };

    if (csvInput && uploadForm) {
        csvInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                showLoader();
                uploadForm.submit();
            }
        });
    }

    if (calculateForm) {
        calculateForm.addEventListener('submit', showLoader);
    }
});
