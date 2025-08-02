function openAcceptModal() {
    const nik = document.getElementById("nik_qc_awal").value.trim();
    const photoDepan = document.getElementById("input_tampilan-depan").value.trim() !== "";
    const photoBelakang = document.getElementById("input_tampilan-belakang").value.trim() !== "";

    const causeEffect = document.getElementById('causeEffect').value.trim();
    const correction = document.getElementById('correction').value.trim();

    const aqlFields = ['criticalDefect','criticalAllowed','majorDefect','majorAllowed','minorDefect','minorAllowed'];
    const allAqlFilled = aqlFields.every(id => document.getElementById(id).value.trim() !== "");

    const anyChecked = document.querySelectorAll('.form-check-input:checked').length > 0;

    // Validasi berurutan
    if (nik === "") {
        Swal.fire({
            icon: 'warning',
            title: 'NIK QC Belum Diisi!',
            text: 'Silakan masukkan NIK QC terlebih dahulu.',
        });
        return;
    }

    // if (!photoDepan || !photoBelakang) {
    //     Swal.fire({
    //         icon: 'warning',
    //         title: 'Dokumentasi Gambar Belum Lengkap!',
    //         text: 'Harap capture gambar Tampilan Depan & Belakang terlebih dahulu.',
    //     });
    //     return;
    // }

    if (!anyChecked) {
        Swal.fire({
            icon: 'warning',
            title: 'Checklist Belum Diisi!',
            text: 'Silakan centang minimal 1 item pemeriksaan.',
        });
        return;
    }

    if (causeEffect === "" || correction === "") {
        Swal.fire({
            icon: 'warning',
            title: 'Detail Pemeriksaan Belum Lengkap!',
            text: 'Isi kolom Cause Effect dan Correction terlebih dahulu.',
        });
        return;
    }

    if (!allAqlFilled) {
        Swal.fire({
            icon: 'warning',
            title: 'Data AQL Belum Lengkap!',
            text: 'Silakan isi semua nilai pada Acceptance Quality Level (AQL).',
        });
        return;
    }

    // Semua validasi lolos
    document.getElementById("nik_qc").value = nik;

    const acceptModal = new bootstrap.Modal(document.getElementById('acceptModal'));
    acceptModal.show();
}

function OpenRejectModal() {
    const rejectBtn = document.getElementById('rejectButton');

    if (rejectBtn.dataset.disabled === "true") {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Bisa Reject',
            text: 'Anda belum melakukan Usage Decision (Accept) terlebih dahulu.'
        });
        return;
    }

    // Jika sudah boleh, buka modal Reject
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    rejectModal.show();
}

// document.getElementById('SubmitPopupButton').addEventListener('click', function () {
//     // Submit form manual
//     document.querySelector('form').submit();

// });