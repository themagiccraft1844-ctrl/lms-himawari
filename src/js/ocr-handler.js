// File: js/ocr-handler.js (BARU)
// Berisi logika OCR yang dapat digunakan kembali untuk registrasi dan verifikasi akun.

function initializeOcrHandler({
    // ID elemen HTML yang dibutuhkan oleh skrip
    fileInputId,
    fileNameDisplayId,
    ocrStatusId,
    ocrProgressTextId,
    ocrPreviewId,
    previewNamaId,
    previewNimId,
    retryBtnId,
    confirmBtnId,
    fileUploadLabelSelector,
    // Callback yang akan dijalankan saat tombol konfirmasi diklik
    onConfirm, 
}) {
    // Pengambilan elemen DOM
    const fileInput = document.getElementById(fileInputId);
    const fileNameDisplay = document.getElementById(fileNameDisplayId);
    const ocrStatus = document.getElementById(ocrStatusId);
    const ocrProgressText = document.getElementById(ocrProgressTextId);
    const ocrPreview = document.getElementById(ocrPreviewId);
    const previewNama = document.getElementById(previewNamaId);
    const previewNim = document.getElementById(previewNimId);
    const retryBtn = document.getElementById(retryBtnId);
    const confirmBtn = document.getElementById(confirmBtnId);
    const fileUploadLabel = document.querySelector(fileUploadLabelSelector);
    
    // Jika elemen penting tidak ditemukan, hentikan eksekusi
    if (!fileInput || !fileUploadLabel) return;

    // Event listener untuk input file (saat diklik)
    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            fileNameDisplay.textContent = this.files[0].name;
            handleFileSelect(this.files[0]);
        } else {
            fileNameDisplay.textContent = 'Belum ada file yang dipilih.';
        }
    });

    // Event listeners untuk fungsionalitas drag and drop
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
            fileInput.files = files;
            const changeEvent = new Event('change');
            fileInput.dispatchEvent(changeEvent);
        }
    });

    // Fungsi utama untuk memproses file gambar
    async function handleFileSelect(file) {
        if (!file) return;

        const imageURL = URL.createObjectURL(file);
        if (ocrStatus.style.backgroundImage !== undefined) {
            ocrStatus.style.backgroundImage = `url(${imageURL})`;
        }
        ocrPreview.style.display = 'none';
        ocrStatus.style.display = 'flex';
        ocrProgressText.textContent = 'Menganalisis gambar...';

        let finalResult = { name: null, nim: null };

        // Coba pindai QR code terlebih dahulu
        ocrProgressText.textContent = 'Mencari QR Code...';
        const nimFromQr = await new Promise(resolve => scanQrCode(file, resolve));
        if (nimFromQr && /(\w{1}\d{8,11}|\b\d{8,12}\b)/.test(nimFromQr)) {
            finalResult.nim = nimFromQr;
            ocrProgressText.textContent = 'QR Code ditemukan! Mengekstrak nama...';
        }

        // Jalankan Tesseract OCR
        try {
            const worker = await Tesseract.createWorker('eng+ind', 1, {
                logger: m => {
                     if (m.status === 'recognizing text') {
                        const progress = Math.round(m.progress * 100);
                        ocrProgressText.textContent = `Mengenali Teks... (${progress}%)`;
                    }
                }
            });

            // Coba rotasi gambar untuk hasil yang lebih baik
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

    // Fungsi untuk memindai QR code dari gambar
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
    
    // Fungsi untuk mem-parsing teks hasil OCR
    function parseTextFromOcr(data) {
        const words = data.words;
        const nimRegex = /(\w{1}\d{8,11}|\b\d{8,12}\b)/;
        const exclusionKeywords = ["MAHASISWA", "UNIVERSITY", "INSTITUT", "KARTU", "REPUBLIK", "INDONESIA", "NIM", "IPB", "BOGOR", "STUDENT", "IDENTITY", "CARD"];

        let nimBlock = words.find(w => nimRegex.test(w.text));
        if (!nimBlock) {
            const nimAnchor = words.find(w => w.text.toUpperCase().includes('NIM'));
            if (!nimAnchor) return { name: null, nim: null };
            nimBlock = words
                .filter(w => nimRegex.test(w.text))
                .sort((a, b) => Math.hypot(a.bbox.x0 - nimAnchor.bbox.x0, a.bbox.y0 - nimAnchor.bbox.y0) - Math.hypot(b.bbox.x0 - nimAnchor.bbox.x0, b.bbox.y0 - nimAnchor.bbox.y0))[0];
        }
        if (!nimBlock) return { name: null, nim: null };

        const nameCandidates = words.filter(w => {
            const text = w.text.toUpperCase();
            return w.text.length > 1 &&
                   /^[A-Z',.]+$/.test(text) &&
                   !exclusionKeywords.some(keyword => text.includes(keyword)) &&
                   nimBlock.bbox.y0 - w.bbox.y1 > 5;
        });

        const nimMatch = nimBlock.text.match(nimRegex);
        if (nameCandidates.length === 0) {
            return { name: null, nim: nimMatch ? nimMatch[0] : null };
        }

        const lines = {};
        const Y_TOLERANCE = 10;

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

        const sortedLines = Object.values(lines).sort((lineA, lineB) => lineA[0].bbox.y0 - lineB[0].bbox.y0);

        const fullName = sortedLines.map(line => {
            line.sort((a, b) => a.bbox.x0 - b.bbox.x0);
            return line.map(w => w.text).join(' ');
        }).join(' ');

        return {
            name: fullName ? fullName.trim() : null,
            nim: nimMatch ? nimMatch[0] : null
        };
    }
    
    // Fungsi untuk merotasi gambar jika diperlukan
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
    
    // Event listener untuk tombol coba lagi
    if (retryBtn) {
        retryBtn.addEventListener('click', () => {
            fileInput.value = '';
            fileNameDisplay.textContent = 'Belum ada file yang dipilih.';
            ocrPreview.style.display = 'none';
            if (ocrStatus.style.backgroundImage) ocrStatus.style.backgroundImage = 'none';
            confirmBtn.disabled = false;
            retryBtn.textContent = 'Unggah Ulang';
            retryBtn.disabled = false;
            fileInput.disabled = false;
            onConfirm(false); // Memberi sinyal bahwa konfirmasi di-reset
        });
    }

    // Event listener untuk tombol konfirmasi
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
             // Panggil callback dengan data yang diperlukan
             onConfirm(true, {
                 name: previewNama.value,
                 nim: previewNim.value,
                 confirmBtn: confirmBtn,
                 retryBtn: retryBtn,
                 fileInput: fileInput
             });
        });
    }
}
