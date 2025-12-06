// Simple Upload Script
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');
    const analyzeBtn = document.getElementById('analyze-btn');
    const messages = document.getElementById('messages');
    let selectedFile = null;

    if (!fileInput || !analyzeBtn || !messages) {
        console.error('Required elements not found');
        return;
    }

    // File input change handler
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        handleFileSelection(file);
    });

    // Drag and drop handlers
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.add('bg-primary', 'bg-opacity-5');
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove('bg-primary', 'bg-opacity-5');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove('bg-primary', 'bg-opacity-5');
        
        const file = e.dataTransfer.files[0];
        if (file) {
            handleFileSelection(file);
        }
    });

    // Handle file selection
    function handleFileSelection(file) {
        // Validate extension
        if (!file.name.match(/\.(pdf|docx)$/i)) {
            messages.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-700"><i class="fas fa-exclamation-circle mr-2"></i>Hanya file PDF atau DOCX yang diperbolehkan</p></div>';
            selectedFile = null;
            analyzeBtn.style.display = 'none';
            return;
        }
        
        // Validate size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            messages.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-700"><i class="fas fa-exclamation-circle mr-2"></i>Ukuran file maksimal 5MB</p></div>';
            selectedFile = null;
            analyzeBtn.style.display = 'none';
            return;
        }
        
        selectedFile = file;
        const sizeKB = (file.size / 1024).toFixed(0);
        messages.innerHTML = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-700 font-medium"><i class="fas fa-check-circle mr-2"></i>File siap: ${file.name} (${sizeKB} KB)</p>
            </div>
        `;
        analyzeBtn.style.display = 'block';
    }
    
    // Analyze button click handler
    analyzeBtn.addEventListener('click', async () => {
        if (!selectedFile) {
            messages.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-700"><i class="fas fa-exclamation-circle mr-2"></i>Silakan pilih file terlebih dahulu</p></div>';
            return;
        }
        
        analyzeBtn.disabled = true;
        analyzeBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        messages.innerHTML = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4"><p class="text-blue-700"><i class="fas fa-cloud-upload-alt mr-2"></i>Mengunggah file...</p></div>';
        
        try {
            // Upload
            const formData = new FormData();
            formData.append('cv_file', selectedFile);
            
            const uploadRes = await fetch(window.BASE_URL + 'app/upload.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (!uploadRes.ok) {
                throw new Error('Upload gagal: ' + uploadRes.statusText);
            }
            
            const uploadData = await uploadRes.json();
            console.log('Upload response:', uploadData);
            
            if (uploadData.error) {
                throw new Error(uploadData.message);
            }
            
            messages.innerHTML = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4"><p class="text-blue-700"><i class="fas fa-robot mr-2"></i>Menganalisis CV dengan AI...</p></div>';
            
            // Analyze
            const analyzeRes = await fetch(window.BASE_URL + 'app/analyze.php', {
                method: 'POST',
                credentials: 'same-origin'
            });
            
            if (!analyzeRes.ok) {
                throw new Error('Analisis gagal: ' + analyzeRes.statusText);
            }
            
            const analyzeData = await analyzeRes.json();
            if (analyzeData.error) {
                throw new Error(analyzeData.message);
            }
            
            messages.innerHTML = '<div class="bg-green-50 border border-green-200 rounded-lg p-4"><p class="text-green-700"><i class="fas fa-check-circle mr-2"></i>Selesai! Mengalihkan ke hasil...</p></div>';
            
            // Redirect
            setTimeout(() => {
                window.location.href = window.BASE_URL + 'results.php?id=' + analyzeData.data.cv_id;
            }, 500);
            
        } catch (error) {
            console.error('Error:', error);
            messages.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-700"><i class="fas fa-exclamation-circle mr-2"></i>Error: ${error.message}</p></div>`;
            analyzeBtn.disabled = false;
            analyzeBtn.innerHTML = '<i class="fas fa-robot mr-2"></i>Analisis dengan AI Sekarang';
        }
    });
});
