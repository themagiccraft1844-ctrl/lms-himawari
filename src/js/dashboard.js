// File: js/dashboard.js (Versi dengan perbaikan)

document.addEventListener('DOMContentLoaded', function() {
    // --- Script untuk Toggle Sidebar ---
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }

    // --- Script untuk Dropdown Profil Pengguna ---
    const userMenuToggle = document.getElementById('user-menu-toggle');
    const userDropdown = document.getElementById('user-dropdown');

    if (userMenuToggle && userDropdown) {
        userMenuToggle.addEventListener('click', function(event) {
            event.stopPropagation(); // Mencegah event sampai ke window
            userDropdown.classList.toggle('show');
        });

        // Menutup dropdown jika klik di luar area
        window.addEventListener('click', function(event) {
            if (userDropdown.classList.contains('show')) {
                userDropdown.classList.remove('show');
            }
        });
    }


    // --- Script untuk Accordion di halaman lihat kursus ---
    const accordionHeaders = document.querySelectorAll('.accordion-header');

    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            this.classList.toggle('active');
            const content = this.nextElementSibling;
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                content.style.padding = "0 20px";
            } else {
                content.style.maxHeight = content.scrollHeight + 40 + "px";
                content.style.padding = "0 20px 20px 20px";
            }
        });
    });

    // --- Script untuk Editor Kuis Dinamis ---
    const questionTypeSelect = document.getElementById('question-type-select');
    if (questionTypeSelect) {
        const mcOptionsContainer = document.getElementById('multiple-choice-options');
        const tfOptionsContainer = document.getElementById('true-false-options');
        const addOptionBtn = document.getElementById('add-option-btn');
        const optionsWrapper = document.getElementById('options-wrapper');

        function setMultipleChoiceRequired(isRequired) {
            const inputs = mcOptionsContainer.querySelectorAll('input[type="text"], input[type="radio"]');
            inputs.forEach(input => {
                if (isRequired) {
                    input.setAttribute('required', 'required');
                } else {
                    input.removeAttribute('required');
                }
            });
        }

        function toggleAnswerFields() {
            const selectedType = questionTypeSelect.value;
            mcOptionsContainer.style.display = 'none';
            tfOptionsContainer.style.display = 'none';
            
            // Nonaktifkan semua validasi terlebih dahulu
            setMultipleChoiceRequired(false);

            if (selectedType === 'multiple_choice') {
                mcOptionsContainer.style.display = 'block';
                setMultipleChoiceRequired(true); // Aktifkan validasi untuk Pilihan Ganda
            } else if (selectedType === 'true_false') {
                tfOptionsContainer.style.display = 'block';
            }
            // Tipe Esai tidak memerlukan field tambahan
        }

        function addMcOption() {
            const optionCount = optionsWrapper.children.length;
            const newOption = document.createElement('div');
            newOption.className = 'option-input-group';
            newOption.innerHTML = `
                <input type="radio" name="is_correct" value="${optionCount}" required>
                <input type="text" name="options[]" class="form-control" placeholder="Teks Opsi ${optionCount + 1}" required>
                <button type="button" class="btn-action-sm delete remove-option">Hapus</button>
            `;
            optionsWrapper.appendChild(newOption);
            updateRemoveButtons();
        }
        
        function updateRemoveButtons() {
            const removeButtons = optionsWrapper.querySelectorAll('.remove-option');
            const canRemove = optionsWrapper.children.length > 2;
            removeButtons.forEach(btn => btn.style.display = canRemove ? 'inline-block' : 'none');
        }

        questionTypeSelect.addEventListener('change', toggleAnswerFields);
        addOptionBtn.addEventListener('click', addMcOption);

        optionsWrapper.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-option')) {
                e.target.parentElement.remove();
                optionsWrapper.querySelectorAll('input[type="radio"]').forEach((radio, index) => {
                    radio.value = index;
                });
                updateRemoveButtons();
            }
        });

        // Inisialisasi form saat halaman dimuat
        toggleAnswerFields();
        if (optionsWrapper.children.length === 0) {
            addMcOption();
            addMcOption();
        }
    }
});
