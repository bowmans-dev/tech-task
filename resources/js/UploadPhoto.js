document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.upload-component').forEach(component => {
        const fileInput = component.querySelector('input[type="file"]');
        const fileNameElement = component.querySelector('.file-name');
        const filePreviewElement = component.querySelector('.file-preview');

        fileInput.addEventListener('change', function () {
            const file = fileInput.files[0];
            if (file) {
                console.log("File selected:", file.name); // Debug the file name
                fileNameElement.textContent = `Selected file: ${file.name}`;

                const reader = new FileReader();
                reader.onload = function (e) {
                    filePreviewElement.src = e.target.result;
                    filePreviewElement.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                console.log("No file selected");
                fileNameElement.textContent = '';
                filePreviewElement.classList.add('hidden');
                filePreviewElement.src = '#';
            }
        });
    });
});
