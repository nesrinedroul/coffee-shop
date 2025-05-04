
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        this.classList.add('active');
        const tabId = this.getAttribute('data-tab');
        document.getElementById(tabId + '-tab').classList.add('active');
    });
});

// Image preview for file upload
const imageUpload = document.getElementById('image_upload');
const imagePreview = document.getElementById('image-preview');

imageUpload.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        
        reader.addEventListener('load', function() {
            if (!imagePreview) {
                const newPreview = document.createElement('img');
                newPreview.id = 'image-preview';
                newPreview.style.display = 'block';
                newPreview.src = this.result;
                this.parentNode.appendChild(newPreview);
            } else {
                imagePreview.style.display = 'block';
                imagePreview.src = this.result;
            }
        });
        
        reader.readAsDataURL(file);
    }
});

// Drag and drop functionality
const uploader = document.querySelector('.image-uploader');

uploader.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploader.style.borderColor = 'var(--primary)';
    uploader.style.backgroundColor = 'rgba(111, 78, 55, 0.05)';
});

uploader.addEventListener('dragleave', () => {
    uploader.style.borderColor = 'var(--border)';
    uploader.style.backgroundColor = 'transparent';
});

uploader.addEventListener('drop', (e) => {
    e.preventDefault();
    uploader.style.borderColor = 'var(--border)';
    uploader.style.backgroundColor = 'transparent';
    
    if (e.dataTransfer.files.length) {
        imageUpload.files = e.dataTransfer.files;
        const event = new Event('change');
        imageUpload.dispatchEvent(event);
    }
});