<?php
// File: register.php
// Halaman registrasi baru dengan validasi real-time dan preview gambar

require_once "db.php";

$status = $_GET['status'] ?? '';
$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Mahasiswa via KTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <?php require_once 'theme_loader.php'; // Tambahkan ini untuk dark mode ?>
    <!-- Memuat Tesseract.js & jsQR dari CDN -->
    <script src='https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1/dist/jsQR.min.js"></script>
    <style>
        /* Gaya tambahan untuk validasi real-time */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper .form-control {
            padding-right: 45px;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: #999;
        }
        .invalid-feedback, .valid-feedback {
            font-size: 0.875em;
            margin-top: 5px;
            display: none;
            width: 100%;
        }
        .requirement {
            transition: color 0.3s;
        }
        .requirement.valid {
            color: var(--success-color);
        }
        .requirement.invalid {
            color: var(--danger-color);
        }
        .invalid-feedback { color: var(--danger-color); }
        .valid-feedback { color: var(--success-color); }
        .form-control.is-invalid { border-color: var(--danger-color); }
        .form-control.is-valid { border-color: var(--success-color); }
    </style>
</head>
<body>
    <div class="auth-container" style="max-width: 600px;">
        <div class="auth-header">
            <h1>Registrasi dengan KTM</h1>
            <p>Unggah foto KTM Anda untuk mengisi data secara otomatis.</p>
        </div>
        <div class="auth-body">
            <?php if ($status == 'success'): ?>
                <div class="alert alert-success">Pendaftaran berhasil! Silakan periksa email Anda untuk link aktivasi.</div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="actions/register_action.php" method="post" id="register-form">
                <!-- Step 1: Data Akun -->
                <fieldset id="step-1">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                        <span class="invalid-feedback" id="username-error"></span>
                        <span class="valid-feedback" id="username-success"></span>
                    </div>
                     <div class="form-group">
                        <label>Email Universitas</label>
                        <input type="email" name="email" id="email" class="form-control" required placeholder="wajib diakhiri dengan @apps.ipb.ac.id">
                        <span class="invalid-feedback" id="email-error"></span>
                        <span class="valid-feedback" id="email-success"></span>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control" required minlength="6">
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                        <div id="password-requirements" style="font-size: 0.8em; color: #666; margin-top: 8px; display: none;">
                            <div id="length" class="requirement">Minimal 6 karakter</div>
                            <div id="capital" class="requirement">Satu huruf kapital (A-Z)</div>
                            <div id="letter" class="requirement">Satu huruf kecil (a-z)</div>
                            <div id="number" class="requirement">Satu angka (0-9)</div>
                            <div id="symbol" class="requirement">Satu simbol (!@#$%^&*)</div>
                        </div>
                    </div>
                     <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                        <span class="invalid-feedback" id="password-error">Password tidak cocok.</span>
                        <span class="valid-feedback" id="password-success">Password cocok.</span>
                    </div>
                </fieldset>

                <!-- Step 2: Upload KTM & OCR -->
                <fieldset id="step-2">
                    <div class="form-group">
                        <label>Upload Foto KTM</label>
                        <p class="form-text">Gunakan gambar yang jelas dan terang untuk hasil terbaik.</p>
                        <label for="ktm-upload" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Pilih File atau Jatuhkan di Sini</span>
                        </label>
                        <input type="file" id="ktm-upload" accept="image/*">
                        <span id="file-name-display" class="file-name-display">Belum ada file yang dipilih.</span>
                    </div>

                    <div id="ocr-status">
                        <div class="ocr-overlay"></div>
                        <div class="loader"></div>
                        <p id="ocr-progress-text">Menganalisis gambar...</p>
                    </div>

                    <div id="ocr-preview" style="display: none;">
                        <h4>Hasil Ekstraksi Data:</h4>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" id="preview-nama" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>NIM</label>
                            <input type="text" id="preview-nim" class="form-control" readonly>
                             <span class="invalid-feedback" id="nim-error"></span>
                             <span class="valid-feedback" id="nim-success"></span>
                        </div>
                        <p class="form-text">Jika data tidak sesuai, silakan unggah ulang gambar KTM yang lebih jelas.</p>
                        <div class="form-group-row">
                            <button type="button" class="btn btn-secondary" id="retry-ocr-btn">Unggah Ulang</button>
                            <button type="button" class="btn" id="confirm-ocr-btn">Konfirmasi & Lanjutkan</button>
                        </div>
                    </div>
                </fieldset>
                
                <input type="hidden" name="full_name" id="hidden-full-name">
                <input type="hidden" name="nim" id="hidden-nim">

                <div class="form-group">
                    <input type="submit" id="submit-btn" class="btn btn-primary" value="Daftar Akun" disabled>
                </div>
                <p>Sudah punya akun? <a href="index.php">Login di sini</a>.</p>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Form Elements & State ---
    const form = document.getElementById('register-form');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submit-btn');
    const togglePasswordIcons = document.querySelectorAll('.toggle-password');
    
    let isUsernameValid = false;
    let isEmailValid = false;
    let isPasswordValid = false;
    let isNimValid = false;

    // --- Debounce Function ---
    const debounce = (func, delay) => {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    };

    // --- Generic Availability Checker ---
    const checkAvailability = async (field, value) => {
        const feedbackError = document.getElementById(`${field}-error`);
        const feedbackSuccess = document.getElementById(`${field}-success`);
        const inputElement = document.getElementById(field === 'nim' ? 'preview-nim' : field);
        
        feedbackError.style.display = 'none';
        feedbackSuccess.style.display = 'none';
        inputElement.classList.remove('is-invalid', 'is-valid');

        if (value.trim() === '') return false;

        try {
            const response = await fetch('actions/check_availability.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `field=${field}&value=${encodeURIComponent(value)}`
            });
            const data = await response.json();
            
            if (data.available) {
                inputElement.classList.add('is-valid');
                feedbackSuccess.textContent = `${field.charAt(0).toUpperCase() + field.slice(1)} tersedia.`;
                feedbackSuccess.style.display = 'block';
                return true;
            } else {
                inputElement.classList.add('is-invalid');
                feedbackError.textContent = data.message;
                feedbackError.style.display = 'block';
                return false;
            }
        } catch (error) {
            console.error('Validation check failed:', error);
            inputElement.classList.add('is-invalid');
            feedbackError.textContent = 'Gagal memvalidasi. Periksa koneksi Anda.';
            feedbackError.style.display = 'block';
            return false;
        }
    };
    
    // --- Update Submit Button State ---
    const updateSubmitButtonState = () => {
        if (isUsernameValid && isEmailValid && isPasswordValid && isNimValid) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    };

    // --- Event Listeners ---
usernameInput.addEventListener('keyup', debounce(async () => {
        const usernameValue = usernameInput.value;
        const feedbackError = document.getElementById('username-error');
        const feedbackSuccess = document.getElementById('username-success');

        // Selalu bersihkan feedback sebelumnya
        usernameInput.classList.remove('is-invalid', 'is-valid');
        feedbackError.style.display = 'none';
        feedbackSuccess.style.display = 'none';
        isUsernameValid = false;

        if (usernameValue.trim() === '') {
            updateSubmitButtonState();
            return;
        }

        // Validasi format username (tanpa spasi, tanpa huruf kapital, tanpa simbol)
        let validationError = '';
        if (/\s/.test(usernameValue)) {
            validationError = 'Username tidak boleh mengandung spasi.';
        } else if (/[A-Z]/.test(usernameValue)) {
            validationError = 'Username harus menggunakan huruf kecil.';
        } else if (/[^a-z0-9_.]/.test(usernameValue)) {
            validationError = 'Username hanya boleh mengandung huruf kecil, angka, titik, dan garis bawah (_).';
        }

        if (validationError) {
            usernameInput.classList.add('is-invalid');
            feedbackError.textContent = validationError;
            feedbackError.style.display = 'block';
            isUsernameValid = false;
        } else {
            // Jika format valid, baru periksa ketersediaan di database
            isUsernameValid = await checkAvailability('username', usernameValue);
        }
        
        updateSubmitButtonState();
    }, 500));

    emailInput.addEventListener('keyup', debounce(async () => {
        const emailValue = emailInput.value;
        const requiredDomain = "@apps.ipb.ac.id";
        const feedbackError = document.getElementById('email-error');
        const feedbackSuccess = document.getElementById('email-success');

        emailInput.classList.remove('is-invalid', 'is-valid');
        feedbackError.style.display = 'none';
        feedbackSuccess.style.display = 'none';
        isEmailValid = false;

        if (emailValue.trim() === '') {
            updateSubmitButtonState();
            return;
        }

        if (!emailValue.endsWith(requiredDomain)) {
            emailInput.classList.add('is-invalid');
            feedbackError.textContent = `Email harus diakhiri dengan ${requiredDomain}.`;
            feedbackError.style.display = 'block';
            isEmailValid = false;
        } else {
            isEmailValid = await checkAvailability('email', emailValue);
        }
        updateSubmitButtonState();
    }, 500));

const validatePassword = () => {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const errorEl = document.getElementById('password-error');
        const successEl = document.getElementById('password-success');
        const requirementsEl = document.getElementById('password-requirements');

        // Tampilkan/sembunyikan daftar persyaratan
        if (password.length > 0) {
            requirementsEl.style.display = 'block';
        } else {
            requirementsEl.style.display = 'none';
        }

        // Reset status
        errorEl.style.display = 'none';
        successEl.style.display = 'none';
        confirmPasswordInput.classList.remove('is-invalid', 'is-valid');
        isPasswordValid = false;

        // Cek persyaratan individu
        const checks = {
            length: password.length >= 6,
            capital: /[A-Z]/.test(password),
            letter: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            symbol: /[^A-Za-z0-9]/.test(password)
        };

        // Update UI untuk persyaratan
        for (const key in checks) {
            const el = document.getElementById(key);
            if (checks[key]) {
                el.classList.add('valid');
                el.classList.remove('invalid');
            } else {
                el.classList.add('invalid');
                el.classList.remove('valid');
            }
        }
        
        // Cek apakah semua persyaratan password terpenuhi
        const allReqsMet = Object.values(checks).every(Boolean);

        if (allReqsMet) {
            passwordInput.classList.add('is-valid');
            passwordInput.classList.remove('is-invalid');
        } else if (password.length > 0) {
            passwordInput.classList.add('is-invalid');
            passwordInput.classList.remove('is-valid');
        } else {
            passwordInput.classList.remove('is-invalid', 'is-valid');
        }

        // Cek konfirmasi password
        if (confirmPassword.length > 0) {
            if (allReqsMet && password === confirmPassword) {
                confirmPasswordInput.classList.add('is-valid');
                successEl.style.display = 'block';
                isPasswordValid = true;
            } else {
                confirmPasswordInput.classList.add('is-invalid');
                errorEl.style.display = 'block';
                isPasswordValid = false;
            }
        } else {
            isPasswordValid = false;
        }
        
        updateSubmitButtonState();
    };

    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validatePassword);

    togglePasswordIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    });

    // --- OCR and File Upload Logic ---
    const ktmUpload = document.getElementById('ktm-upload');
    const fileUploadLabel = document.querySelector('.file-upload-label');
    const fileNameDisplay = document.getElementById('file-name-display');
    const ocrStatus = document.getElementById('ocr-status');
    const ocrProgressText = document.getElementById('ocr-progress-text');
    const ocrPreview = document.getElementById('ocr-preview');
    const previewNama = document.getElementById('preview-nama');
    const previewNim = document.getElementById('preview-nim');
    const retryOcrBtn = document.getElementById('retry-ocr-btn');
    const confirmOcrBtn = document.getElementById('confirm-ocr-btn');
    const hiddenFullName = document.getElementById('hidden-full-name');
    const hiddenNim = document.getElementById('hidden-nim');
    
    // Event listener for file selection via click
    ktmUpload.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            fileNameDisplay.textContent = this.files[0].name;
            handleFileSelect(this.files[0]);
        } else {
            fileNameDisplay.textContent = 'Belum ada file yang dipilih.';
        }
    });

    // Event listeners for drag and drop
    fileUploadLabel.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileUploadLabel.classList.add('drag-over');
    });

    fileUploadLabel.addEventListener('dragleave', (e) => {
        e.preventDefault();
        fileUploadLabel.classList.remove('drag-over');
    });

    fileUploadLabel.addEventListener('drop', (e) => {
        e.preventDefault();
        fileUploadLabel.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            ktmUpload.files = files;
            const changeEvent = new Event('change');
            ktmUpload.dispatchEvent(changeEvent);
        }
    });

    async function handleFileSelect(file) {
        if (!file) return;

        const imageURL = URL.createObjectURL(file);
        ocrStatus.style.backgroundImage = `url(${imageURL})`;
        ocrPreview.style.display = 'none';
        ocrStatus.style.display = 'flex';
        ocrProgressText.textContent = 'Menganalisis gambar...';

        let finalResult = { name: null, nim: null };

        ocrProgressText.textContent = 'Mencari QR Code...';
        const nimFromQr = await new Promise(resolve => scanQrCode(file, resolve));
        if (nimFromQr && /(\w{1}\d{8,11}|\b\d{8,12}\b)/.test(nimFromQr)) {
            finalResult.nim = nimFromQr;
            ocrProgressText.textContent = 'QR Code ditemukan! Mengekstrak nama...';
        }

        try {
            const worker = await Tesseract.createWorker('eng+ind', 1, {
                logger: m => {
                     if (m.status === 'recognizing text') {
                        const progress = Math.round(m.progress * 100);
                        ocrProgressText.textContent = `Mengenali Teks... (${progress}%)`;
                    }
                }
            });

            for (const angle of [0, 90, 180, 270]) {
                 if (finalResult.name && finalResult.nim) break;
                ocrProgressText.textContent = `Mencoba rotasi ${angle}°...`;
                const imageToProcess = await new Promise(resolve => {
                    if (angle === 0) return resolve(file);
                    getRotatedImage(file, angle, rotatedImage => resolve(rotatedImage));
                });
                
                const { data } = await worker.recognize(imageToProcess);
                const ocrResult = parseTextFromOcr(data);
                finalResult.name = ocrResult.name || finalResult.name;
                finalResult.nim = ocrResult.nim || finalResult.nim;
            }
            
            await worker.terminate();
            previewNama.value = finalResult.name || 'Tidak terdeteksi';
            previewNim.value = finalResult.nim || 'Tidak terdeteksi';
            ocrStatus.style.display = 'none';
            ocrPreview.style.display = 'block';
        } catch (error) {
            console.error(error);
            ocrProgressText.textContent = 'Gagal memproses gambar. Coba lagi.';
        }
    }
    
    function scanQrCode(file, callback) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, img.width, img.height);
                const imageData = ctx.getImageData(0, 0, img.width, img.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                callback(code ? code.data : null);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    
function parseTextFromOcr(data) {
        const words = data.words;
        const nimRegex = /(\w{1}\d{8,11}|\b\d{8,12}\b)/;
        const exclusionKeywords = ["MAHASISWA", "UNIVERSITY", "INSTITUT", "KARTU", "REPUBLIK", "INDONESIA", "NIM", "IPB", "BOGOR", "STUDENT", "IDENTITY", "CARD"];

        // 1. Temukan blok NIM sebagai referensi utama
        let nimBlock = words.find(w => nimRegex.test(w.text));
        if (!nimBlock) {
            const nimAnchor = words.find(w => w.text.toUpperCase().includes('NIM'));
            if (!nimAnchor) return { name: null, nim: null };
            nimBlock = words
                .filter(w => nimRegex.test(w.text))
                .sort((a, b) => Math.hypot(a.bbox.x0 - nimAnchor.bbox.x0, a.bbox.y0 - nimAnchor.bbox.y0) - Math.hypot(b.bbox.x0 - nimAnchor.bbox.x0, b.bbox.y0 - nimAnchor.bbox.y0))[0];
        }
        if (!nimBlock) return { name: null, nim: null };

        // 2. Kumpulkan semua kandidat kata untuk nama yang berada di atas NIM
        const nameCandidates = words.filter(w => {
            const text = w.text.toUpperCase();
            return w.text.length > 1 &&
                /^[A-Z',.]+$/.test(text) &&
                !exclusionKeywords.some(keyword => text.includes(keyword)) &&
                nimBlock.bbox.y0 - w.bbox.y1 > 5; // Harus ada jarak vertikal minimal
        });

        const nimMatch = nimBlock.text.match(nimRegex);
        if (nameCandidates.length === 0) {
            return { name: null, nim: nimMatch ? nimMatch[0] : null };
        }

        // 3. Kelompokkan kandidat menjadi beberapa baris berdasarkan posisi vertikal (y0)
        const lines = {};
        const Y_TOLERANCE = 10; // Toleransi perbedaan y0 untuk dianggap satu baris

        nameCandidates.forEach(word => {
            let foundLine = false;
            for (const y in lines) {
                if (Math.abs(word.bbox.y0 - y) < Y_TOLERANCE) {
                    lines[y].push(word);
                    foundLine = true;
                    break;
                }
            }
            if (!foundLine) {
                lines[word.bbox.y0] = [word];
            }
        });

        // 4. Urutkan baris dari atas ke bawah
        const sortedLines = Object.values(lines).sort((lineA, lineB) => {
            return lineA[0].bbox.y0 - lineB[0].bbox.y0;
        });

        // 5. Urutkan kata di dalam setiap baris, lalu gabungkan semua baris
        const fullName = sortedLines.map(line => {
            line.sort((a, b) => a.bbox.x0 - b.bbox.x0);
            return line.map(w => w.text).join(' ');
        }).join(' ');

        return {
            name: fullName ? fullName.trim() : null,
            nim: nimMatch ? nimMatch[0] : null
        };
    }
    
    function getRotatedImage(file, degrees, callback) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const isVerticalRotation = degrees === 90 || degrees === 270;
                canvas.width = isVerticalRotation ? img.height : img.width;
                canvas.height = isVerticalRotation ? img.width : img.height;
                const ctx = canvas.getContext('2d');
                ctx.translate(canvas.width / 2, canvas.height / 2);
                ctx.rotate(degrees * Math.PI / 180);
                ctx.drawImage(img, -img.width / 2, -img.height / 2);
                callback(canvas.toDataURL());
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    retryOcrBtn.addEventListener('click', () => {
        ktmUpload.value = '';
        fileNameDisplay.textContent = 'Belum ada file yang dipilih.';
        ocrPreview.style.display = 'none';
        ocrStatus.style.backgroundImage = 'none';
        confirmOcrBtn.disabled = false;
        retryOcrBtn.textContent = 'Unggah Ulang';
        retryOcrBtn.disabled = false;
        ktmUpload.disabled = false;
        isNimValid = false;
        updateSubmitButtonState();
    });

    confirmOcrBtn.addEventListener('click', async () => {
        const nimValue = previewNim.value;
        if (previewNama.value !== 'Tidak terdeteksi' && nimValue !== 'Tidak terdeteksi') {
            isNimValid = await checkAvailability('nim', nimValue);
            
            if (isNimValid) {
                hiddenFullName.value = previewNama.value;
                hiddenNim.value = nimValue;
                ktmUpload.disabled = true;
                confirmOcrBtn.disabled = true;
                retryOcrBtn.textContent = 'Data Terkonfirmasi';
                retryOcrBtn.disabled = true;
                alert('Data Nama dan NIM berhasil dikonfirmasi. Silakan lengkapi pendaftaran.');
            } else {
                 alert('NIM ini sudah terdaftar. Silakan gunakan NIM lain atau hubungi admin.');
            }
        } else {
            alert('Data tidak lengkap. Silakan unggah ulang gambar KTM yang lebih jelas.');
            isNimValid = false;
        }
        updateSubmitButtonState();
    });
});
</script>

</body>
</html>
