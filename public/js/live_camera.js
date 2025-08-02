function openCamera(videoId, canvasId, previewId, inputId, captureBtnId) {
    const video = document.getElementById(videoId);
    const captureBtn = document.getElementById(captureBtnId);
    const cancelBtn = document.getElementById('cancelBtn_' + getSlug(videoId));

    // Akses kamera
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
            video.classList.remove('d-none');
            captureBtn.classList.remove('d-none');
            if (cancelBtn) cancelBtn.classList.remove('d-none');
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
    preview.classList.remove('d-none');
    input.value = imageData;

    stopCamera(video);

    video.classList.add('d-none');
    captureBtn.classList.add('d-none');
    if (cancelBtn) cancelBtn.classList.add('d-none');
    if (retakeBtn) retakeBtn.classList.remove('d-none');
}

function retakeCapture(videoId, canvasId, previewId, inputId, captureBtnId, retakeBtnId) {
    const preview = document.getElementById(previewId);
    const input = document.getElementById(inputId);
    const retakeBtn = document.getElementById(retakeBtnId);

    // Kosongkan preview & input
    preview.classList.add('d-none');
    preview.src = '';
    input.value = '';

    // Buka ulang kamera
    openCamera(videoId, canvasId, previewId, inputId, captureBtnId);
    if (retakeBtn) retakeBtn.classList.add('d-none');
}

function cancelCamera(videoId, captureBtnId, cancelBtnId) {
    const video = document.getElementById(videoId);
    const captureBtn = document.getElementById(captureBtnId);
    const cancelBtn = document.getElementById(cancelBtnId);

    stopCamera(video);

    if (video) video.classList.add('d-none');
    if (captureBtn) captureBtn.classList.add('d-none');
    if (cancelBtn) cancelBtn.classList.add('d-none');
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