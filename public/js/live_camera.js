function openCamera(videoId, canvasId, previewId, inputId, captureBtnId) {
    const video = document.getElementById(videoId);
    const captureBtn = document.getElementById(captureBtnId);
    const cancelBtn = document.getElementById('cancelBtn_' + getSlug(videoId));

    // Akses kamera
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
            video.classList.remove('hidden');
            captureBtn.classList.remove('hidden');
            if (cancelBtn) cancelBtn.classList.remove('hidden');
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mengakses Kamera',
                text: 'Periksa izin kamera di browser Anda.\n\n' + err.message,
            });
        });
}

function captureImage(videoId, canvasId, previewId, inputId, captureBtnId, cancelBtnId) {
    const video = document.getElementById(videoId);
    const canvas = document.getElementById(canvasId);
    const preview = document.getElementById(previewId);
    const input = document.getElementById(inputId);
    const captureBtn = document.getElementById(captureBtnId);
    const cancelBtn = document.getElementById(cancelBtnId);
    const retakeBtn = document.getElementById('retakeBtn_' + getSlug(videoId));

    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = canvas.toDataURL('image/png');
    preview.src = imageData;
    preview.classList.remove('hidden');
    input.value = imageData;

    stopCamera(video);

    video.classList.add('hidden');
    captureBtn.classList.add('hidden');
    if (cancelBtn) cancelBtn.classList.add('hidden');
    if (retakeBtn) retakeBtn.classList.remove('hidden');
}

function retakeCapture(videoId, canvasId, previewId, inputId, captureBtnId, retakeBtnId) {
    const preview = document.getElementById(previewId);
    const input = document.getElementById(inputId);
    const retakeBtn = document.getElementById(retakeBtnId);

    // Kosongkan preview & input
    preview.classList.add('hidden');
    preview.src = '';
    input.value = '';

    // Buka ulang kamera
    openCamera(videoId, canvasId, previewId, inputId, captureBtnId);
    if (retakeBtn) retakeBtn.classList.add('hidden');
}

function cancelCamera(videoId, captureBtnId, cancelBtnId) {
    const video = document.getElementById(videoId);
    const captureBtn = document.getElementById(captureBtnId);
    const cancelBtn = document.getElementById(cancelBtnId);

    stopCamera(video);

    if (video) video.classList.add('hidden');
    if (captureBtn) captureBtn.classList.add('hidden');
    if (cancelBtn) cancelBtn.classList.add('hidden');
}

// Utility: Stop stream kamera
function stopCamera(video) {
    if (video && video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
}

//  Utility: Ambil slug dari ID kamera
function getSlug(videoId) {
    // Dari 'camera_tampilan-atas' -> 'tampilan-atas'
    return videoId.replace('camera_', '');
}