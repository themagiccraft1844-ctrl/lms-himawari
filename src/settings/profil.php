<?php
// File: src/settings/profil.php (DIPERBARUI DENGAN MODAL YANG BENAR)

if (session_status() == PHP_SESSION_NONE) {
    header("location: ../settings.php");
    exit;
}

$user_id = $_SESSION['id'];
$stmt = $mysqli->prepare("SELECT username, full_name, nim, email, profile_picture_url FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User tidak ditemukan.");
}

function parseNIM($nim) {
    $info = [ 'fakultas' => 'Tidak Diketahui', 'kampus' => 'Tidak Diketahui', 'jenjang' => 'Tidak Diketahui', 'prodi' => 'Tidak Diketahui', 'angkatan' => 'Tidak Diketahui', 'semester_masuk' => 'Tidak Diketahui', 'nomor_urut' => 'Tidak Diketahui' ];
    if (strlen($nim) < 10) return $info;
    $kodeFakultas = strtoupper(substr($nim, 0, 1));
    $fakultasMap = [ 'A' => 'Fakultas Pertanian (Faperta)', 'B' => 'Sekolah Kedokteran Hewan dan Biomedis (SKHB)', 'C' => 'Fakultas Perikanan dan Ilmu Kelautan (FPIK)', 'D' => 'Fakultas Peternakan (Fapet)', 'J' => 'Sekolah Vokasi', 'E' => 'Fakultas Kehutanan dan Lingkungan (Fahutan)', 'F' => 'Sekolah Teknik', 'G' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam (FMIPA)', 'H' => 'Fakultas Ekonomi dan Manajemen (FEM)', 'I' => 'Fakultas Ekologi Manusia (Fema)', 'K' => 'Sekolah Bisnis (SB)', 'L' => 'Fakultas Kedokteran (FK)', 'M' => 'Sekolah Sains Data, Matematika dan Informatika (SMI)' ];
    $info['fakultas'] = $fakultasMap[$kodeFakultas] ?? 'Tidak Diketahui';
    $info['kampus'] = substr($nim, 1, 1) == '0' ? 'Kampus Bogor' : 'Kampus Sukabumi';
    $info['jenjang'] = substr($nim, 2, 1) == '4' ? 'Sarjana Terapan (D4)' : 'Tidak Diketahui';
    $kodeProdi = substr($nim, 3, 2);
    if ($kodeFakultas == 'J') {
        $prodiMap = [ '01' => 'Komunikasi Digital dan Media', '03' => 'Teknologi Rekayasa Perangkat Lunak', '04' => 'Teknologi Rekayasa Komputer' ];
        $info['prodi'] = $prodiMap[$kodeProdi] ?? 'Tidak Diketahui';
    }
    $kodeTahun = substr($nim, 5, 2);
    $info['angkatan'] = '20' . $kodeTahun . '(' . (int)$kodeTahun + 2000 - 1963 . ')';
    $info['semester_masuk'] = substr($nim, 7, 1) == '1' ? 'Gasal' : 'Genap';
    $info['nomor_urut'] = substr($nim, -3);
    return $info;
}

$nim_info = parseNIM($user['nim']);
?>
<style>
/* Gaya untuk Foto Profil dan Modal (diadaptasi dari akun.php) */
.profile-picture-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background-color: var(--hover-color);
    border-radius: 12px;
    margin-bottom: 2rem;
}
.profile-picture {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--content-bg);
    box-shadow: 0 4px 8px var(--shadow-color);
}
.profile-picture-container .btn {
    width: auto;
}

/* === GAYA MODAL (diambil dari akun.php) === */
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);
    display: none; align-items: center; justify-content: center;
    z-index: 1000; opacity: 0; transition: opacity 0.3s ease;
}
.modal-overlay.show { display: flex; opacity: 1; }
.modal-content {
    background: var(--content-bg); padding: 0; border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    width: 90%; max-width: 500px;
    transform: scale(0.95); transition: transform 0.3s ease;
}
.modal-overlay.show .modal-content { transform: scale(1); }
.modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color);
}
.modal-header h2 { margin: 0; font-size: 1.25rem; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-grey); }
.modal-body { padding: 1.5rem; }

/* === GAYA TOMBOL UPLOAD (diambil dari style.css) === */
.file-upload-label {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; padding: 20px; background-color: var(--bg-color);
    border: 2px dashed var(--border-color); border-radius: 8px;
    cursor: pointer; transition: all 0.3s ease;
    color: var(--text-grey); font-weight: 500;
}
.file-upload-label:hover, .file-upload-label.drag-over {
    background-color: var(--primary-light); border-color: var(--primary-color); color: var(--primary-color);
}
.file-upload-label i { font-size: 1.5rem; }
.file-name-display {
    display: block; margin-top: 10px; font-size: 0.9rem;
    color: var(--text-grey); text-align: center; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
</style>

<!-- KONTEN SPESIFIK HALAMAN PROFIL -->
<div class="management-grid" style="grid-template-columns: 2fr 1fr; align-items: start;">
    <div class="form-container">
        <h3><i class="fas fa-user" style="margin-right: 10px;"></i>Profil Mahasiswa</h3>
        <div class="form-group"><label>Nama Lengkap</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly></div>
        <div class="form-group"><label>NIM</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nim']); ?>" readonly></div>
        <div class="form-group"><label>Fakultas</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($nim_info['fakultas']); ?>" readonly></div>
        <div class="form-group"><label>Program Studi</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($nim_info['prodi']); ?>" readonly></div>
        <div class="form-group"><label>Jenjang</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($nim_info['jenjang']); ?>" readonly></div>
        <div class="form-group"><label>Angkatan</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($nim_info['angkatan']); ?>" readonly></div>
    </div>

    <div class="form-container">
        <h3><i class="fas fa-user-circle" style="margin-right: 10px;"></i>Akun</h3>
        <div class="profile-picture-container">
            <img src="<?php echo !empty($user['profile_picture_url']) ? htmlspecialchars($user['profile_picture_url']) : 'https://placehold.co/120x120/EFEFEF/AAAAAA&text=Profil'; ?>" alt="Foto Profil" class="profile-picture">
            <button id="openUploadModalBtn" class="btn">Ubah Foto Profil</button>
        </div>
        <div class="form-group"><label>Username</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly></div>
        <div class="form-group"><label>Email</label><input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly></div>
    </div>
</div>

<!-- ===== KODE POP-UP (MODAL) UPLOAD FOTO PROFIL (Struktur Diperbaiki) ===== -->
<div id="uploadModal" class="modal-overlay">
    <div class="modal-content">
        <header class="modal-header">
            <h2>Ubah Foto Profil</h2>
            <button id="closeUploadModalBtn" class="modal-close-btn"><i class="fas fa-times"></i></button>
        </header>
        <main class="modal-body">
            <form action="settings/actions/update_profile_picture_action.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profile_picture" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Pilih File Gambar</span>
                    </label>
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg, image/png, image/gif" style="display:none;" required>
                    <span id="file-name-display-modal" class="file-name-display">Belum ada file yang dipilih.</span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn" style="width:100%;" value="Simpan Foto">
                </div>
            </form>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Logika Modal Upload Foto ---
    const modal = document.getElementById('uploadModal');
    const openBtn = document.getElementById('openUploadModalBtn');
    const closeBtn = document.getElementById('closeUploadModalBtn');
    const fileInput = document.getElementById('profile_picture');
    const fileNameDisplay = document.getElementById('file-name-display-modal');

    if (modal && openBtn && closeBtn && fileInput) {
        openBtn.addEventListener('click', () => modal.classList.add('show'));
        closeBtn.addEventListener('click', () => modal.classList.remove('show'));
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        });
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Belum ada file yang dipilih.';
            }
        });
    }
});
</script>

