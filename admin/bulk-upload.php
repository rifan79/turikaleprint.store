<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$stat_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM logos"))['total'];

// Fetch provinces for fallback edits
$provincesData = [];
$prov_res = mysqli_query($conn, "SELECT id, name FROM provinces ORDER BY name ASC");
while ($p = mysqli_fetch_assoc($prov_res)) {
    $provincesData[] = $p;
}
$provincesJson = json_encode($provincesData);
?>
<!doctype html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Upload Massal AI - Turikale Print</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="https://turikaleprint.store/public/Logo-Icon-TurikalePrint.jpg" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#136dec",
                        "accent-success": "#07883b",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101822",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px",
                    },
                },
            },
        };
    </script>
    <style>
        /* Loading spinner animation */
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.2s ease-out forwards; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 antialiased">
<div class="flex min-h-screen">
    <aside class="w-48 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col fixed h-full transition-colors duration-300">
        <div class="p-6 flex items-center gap-3">
            <img src="https://turikaleprint.store/public/assets/images/Logo Biru-TurikalePrint.png" alt="Turikale Print" class="h-10 w-auto" />
        </div>
        <nav class="flex-1 px-4 space-y-1 mt-4">
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group" href="dashboard.php">
                <span class="material-symbols-outlined text-[22px]">dashboard</span>
                <span class="text-sm font-medium">Dashboard</span>
            </a>
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group" href="kelola-logo.php">
                <span class="material-symbols-outlined text-[22px]">image</span>
                <span class="text-sm font-medium">Kelola Logo</span>
            </a>
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary group" href="bulk-upload.php">
                <span class="material-symbols-outlined text-[22px]">auto_awesome</span>
                <span class="text-sm font-medium">Upload AI</span>
            </a>
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group" href="kelola-artikel.php">
                <span class="material-symbols-outlined text-[22px]">description</span>
                <span class="text-sm font-medium">Kelola Artikel</span>
            </a>
            <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800">
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-red-500 hover:bg-red-50 transition-colors" href="logout.php">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="text-sm font-medium">Keluar</span>
                </a>
            </div>
        </nav>
    </aside>

    <main class="flex-1 p-5 ml-48 max-w-7xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">auto_awesome</span>
                    Upload Massal & AI Auto-Tagger
                </h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                    Unggah banyak logo sekaligus. AI akan memilah nama, kategori, provinsi, dan kabupaten otomatis.
                </p>
            </div>
            <div>
                <a href="kelola-logo.php" class="px-4 py-2 border rounded-lg bg-white hover:bg-slate-50 text-sm font-semibold transition-colors">Batal</a>
            </div>
        </div>

        <!-- Dropzone Area -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 mb-8">
            <div id="drop-area" class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-12 flex flex-col items-center justify-center text-slate-500 bg-slate-50 dark:bg-slate-800/30 hover:border-primary/50 transition-colors cursor-pointer relative overflow-hidden group">
                <input type="file" id="fileElem" multiple accept="image/png, image/jpeg, image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                
                <div class="bg-primary/10 text-primary p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-4xl">cloud_upload</span>
                </div>
                <h3 class="text-lg font-bold text-slate-700 dark:text-slate-200 mb-2">Klik atau Seret Logo Kesini</h3>
                <p class="text-sm">Bisa upload banyak gambar sekaligus (PNG, JPG, WEBP Maks 2MB/file)</p>
            </div>

            <div class="mt-5 flex items-center justify-center gap-3">
                <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Isi data manual</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="ai-toggle" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-primary"></div>
                </label>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Gunakan AI Auto-Tagger</span>
            </div>
        </div>

        <!-- Results Table (Hidden initially) -->
        <div id="results-container" class="hidden">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-bold text-lg">Hasil Pemindaian AI</h3>
                    <div class="text-sm font-semibold text-slate-500">
                        Total: <span id="total-count" class="text-primary">0</span> logo
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-xs text-slate-500 uppercase">
                                <th class="px-4 py-3 font-semibold border-b">Preview</th>
                                <th class="px-4 py-3 font-semibold border-b">Nama Logo</th>
                                <th class="px-4 py-3 font-semibold border-b">Kategori</th>
                                <th class="px-4 py-3 font-semibold border-b">Provinsi</th>
                                <th class="px-4 py-3 font-semibold border-b">Kab/Kota</th>
                                <th class="px-4 py-3 font-semibold border-b text-center">Status</th>
                                <th class="px-4 py-3 font-semibold border-b text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="logo-list">
                            <!-- Rows injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Action bar -->
            <div class="flex justify-end gap-3 mb-10">
                <button id="clear-btn" class="px-5 py-2.5 rounded-lg border bg-white hover:bg-slate-50 text-slate-700 font-semibold transition-colors">
                    Hapus Semua
                </button>
                <button id="save-all-btn" class="px-5 py-2.5 rounded-lg shadow-lg shadow-primary/30 bg-primary hover:bg-primary/90 text-white font-bold transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Simpan Semua ke Database
                </button>
            </div>
        </div>

        <!-- Image Preview Panel (Fixed floating) -->
        <div id="image-preview-panel" class="hidden fixed bottom-1/4 left-56 z-[60] bg-white p-3 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.3)] border border-slate-200 animate-fade-in">
            <div class="flex justify-between items-center mb-2 gap-4">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">visibility</span> Preview Detail
                </span>
                <button onclick="closePreviewPanel()" class="text-slate-400 hover:text-red-500 transition-colors rounded hover:bg-red-50 p-1">
                    <span class="material-symbols-outlined text-sm block">close</span>
                </button>
            </div>
            <div class="w-72 h-72 flex items-center justify-center bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPjxyZWN0IHdpZHRoPSI0IiBoZWlnaHQ9IjQiIGZpbGw9IiNmOGZhZmMiLz48cmVjdCB4PSI0IiB5PSI0IiB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZjhmYWZjIi8+PC9zdmc+')] bg-slate-100 rounded-xl border border-slate-200 p-2 overflow-hidden relative">
                <img id="preview-panel-img" src="" class="max-w-full max-h-full object-contain drop-shadow-lg transition-transform hover:scale-150 cursor-zoom-in" title="Arahkan kursor untuk zoom">
            </div>
        </div>

    </main>
</div>

<!-- Custom Popup Modal -->
<div id="custom-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity opacity-0">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full mx-4 overflow-hidden transform scale-95 transition-transform duration-300">
        <div class="p-6 text-center">
            <div id="modal-icon" class="mb-4 inline-flex items-center justify-center size-14 rounded-full bg-slate-100 text-slate-500">
                <span class="material-symbols-outlined text-3xl">info</span>
            </div>
            <h3 id="modal-title" class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">Pemberitahuan</h3>
            <p id="modal-message" class="text-sm text-slate-600 dark:text-slate-400 mb-6 font-medium">Message goes here</p>
            <div class="flex justify-center gap-3" id="modal-actions">
                <!-- Buttons injected by JS -->
            </div>
        </div>
    </div>
</div>

<!-- Raw Provinces JSON -->
<script>
    const provincesData = <?php echo $provincesJson; ?>;
    const categoriesList = ['Sekolah', 'Pemerintahan', 'Perguruan Tinggi', 'Organisasi'];
    let logosCache = []; // Temporary cache of AI responses
    let uploaderQueue = [];
    let isUploading = false;
</script>

<script>
const dropArea = document.getElementById('drop-area');
const input = document.getElementById('fileElem');
const resultsContainer = document.getElementById('results-container');
const logoList = document.getElementById('logo-list');
const saveBtn = document.getElementById('save-all-btn');
const clearBtn = document.getElementById('clear-btn');
const totalCountEl = document.getElementById('total-count');

// Custom Modal Functions
function showModal(title, message, type = 'info', onConfirm = null, isConfirm = false) {
    const modal = document.getElementById('custom-modal');
    const modalIcon = document.getElementById('modal-icon');
    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const modalActions = document.getElementById('modal-actions');
    
    modalTitle.innerText = title;
    modalMessage.innerText = message;
    
    if (type === 'success') {
        modalIcon.className = "mb-4 inline-flex items-center justify-center size-14 rounded-full bg-green-100 text-green-500";
        modalIcon.innerHTML = '<span class="material-symbols-outlined text-3xl">check_circle</span>';
    } else if (type === 'error') {
        modalIcon.className = "mb-4 inline-flex items-center justify-center size-14 rounded-full bg-red-100 text-red-500";
        modalIcon.innerHTML = '<span class="material-symbols-outlined text-3xl">error</span>';
    } else if (type === 'warning') {
        modalIcon.className = "mb-4 inline-flex items-center justify-center size-14 rounded-full bg-orange-100 text-orange-500";
        modalIcon.innerHTML = '<span class="material-symbols-outlined text-3xl">warning</span>';
    } else {
        modalIcon.className = "mb-4 inline-flex items-center justify-center size-14 rounded-full bg-blue-100 text-blue-500";
        modalIcon.innerHTML = '<span class="material-symbols-outlined text-3xl">info</span>';
    }

    if (isConfirm) {
        modalActions.innerHTML = `
            <button id="modal-cancel-btn" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-colors">Batal</button>
            <button id="modal-confirm-btn" class="flex-1 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl transition-colors">Hapus</button>
        `;
        document.getElementById('modal-cancel-btn').onclick = closeModal;
        document.getElementById('modal-confirm-btn').onclick = () => {
            closeModal();
            if (onConfirm) onConfirm();
        };
    } else {
        modalActions.innerHTML = `
            <button id="modal-ok-btn" class="w-full px-4 py-2.5 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-colors">Oke</button>
        `;
        document.getElementById('modal-ok-btn').onclick = () => {
            closeModal();
            if (onConfirm) onConfirm();
        };
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Animate
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('div').classList.remove('scale-95');
    });
}

function closeModal() {
    const modal = document.getElementById('custom-modal');
    modal.classList.add('opacity-0');
    modal.querySelector('div').classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

// Drag 'n drop UI feedback
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});
function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }

['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, () => dropArea.classList.add('border-primary', 'bg-primary/5'), false);
});
['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, () => dropArea.classList.remove('border-primary', 'bg-primary/5'), false);
});

dropArea.addEventListener('drop', handleDrop, false);
input.addEventListener('change', (e) => handleFiles(e.target.files), false);

function handleDrop(e) { handleFiles(e.dataTransfer.files); }

function handleFiles(files) {
    if (!files || files.length === 0) return;
    
    // Convert FileList to array and push to queue
    [...files].forEach(file => {
        if (file.size > 2 * 1024 * 1024) {
            showModal('File Terlalu Besar', `File ${file.name} terlalu besar (Maks 2MB). Diskip.`, 'warning');
            return;
        }
        uploaderQueue.push(file);
    });

    if (!isUploading) processQueue();
}

async function processQueue() {
    if (uploaderQueue.length === 0) {
        isUploading = false;
        return;
    }
    
    isUploading = true;
    resultsContainer.classList.remove('hidden');
    
    // Take one file from queue
    const file = uploaderQueue.shift();
    const objUrl = URL.createObjectURL(file);
    
    // Create UI row in loading state
    const rowId = 'logo_row_' + Date.now() + Math.floor(Math.random() * 100);
    createLoadingRow(rowId, file, objUrl);
    
    // Upload and get AI prediction
    try {
        const useAi = document.getElementById('ai-toggle').checked;
        const formData = new FormData();
        formData.append('logo', file);
        formData.append('use_ai', useAi ? '1' : '0');

        const response = await fetch('api/bulk-upload-logo', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            const index = logosCache.length;
            logosCache.push(result.data);
            updateRowToSuccess(rowId, result.data, index, objUrl);
            totalCountEl.innerText = logosCache.length;
        } else {
            updateRowToError(rowId, file.name, result.message || 'Gagal memproses AI');
        }
    } catch (err) {
        updateRowToError(rowId, file.name, 'Koneksi error');
    }

    // Process next file sequentially to not overflow API rate limits
    setTimeout(processQueue, 500); 
}

// -------------------------------------------------------------
// UI Render Helpers
// -------------------------------------------------------------
function createLoadingRow(rowId, file, objUrl) {
    const tr = document.createElement('tr');
    tr.id = rowId;
    tr.className = 'border-b hover:bg-slate-50 transition-colors animate-pulse';
    tr.innerHTML = `
        <td class="px-4 py-3">
            <div onclick="showPreviewPanel('${objUrl}')" class="size-12 bg-slate-100 rounded overflow-hidden flex items-center justify-center p-1 border cursor-zoom-in hover:border-primary hover:shadow-md transition-all group relative" title="Klik untuk perbesar">
                <img src="${objUrl}" class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform">
                <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-[16px]">zoom_in</span>
                </div>
            </div>
        </td>
        <td class="px-4 py-3 text-sm font-medium text-slate-500">${file.name}</td>
        <td class="px-4 py-3"><div class="h-6 bg-slate-200 rounded w-24"></div></td>
        <td class="px-4 py-3"><div class="h-6 bg-slate-200 rounded w-32"></div></td>
        <td class="px-4 py-3"><div class="h-6 bg-slate-200 rounded w-32"></div></td>
        <td class="px-4 py-3 text-center">
            <span class="material-symbols-outlined text-slate-400 animate-spin">sync</span>
        </td>
        <td class="px-4 py-3 text-center">-</td>
    `;
    logoList.appendChild(tr);
}

function updateRowToSuccess(rowId, data, index, objUrl) {
    const tr = document.getElementById(rowId);
    if (!tr) return;
    tr.classList.remove('animate-pulse');
    
    // Prov options
    let provOptions = '<option value="">- Pilih Provinsi -</option>';
    provincesData.forEach(p => {
        const selected = (p.id == data.province_id) || (p.name.toLowerCase() === (data.province_name || '').toLowerCase()) ? 'selected' : '';
        provOptions += `<option value="${p.id}" ${selected}>${p.name}</option>`;
    });

    // Cat options
    let catOptions = '';
    categoriesList.forEach(c => {
        const selected = (c === data.category) ? 'selected' : '';
        catOptions += `<option value="${c}" ${selected}>${c}</option>`;
    });

    tr.innerHTML = `
        <td class="px-4 py-3">
            <div onclick="showPreviewPanel('${objUrl}')" class="size-12 bg-slate-100 rounded overflow-hidden flex items-center justify-center p-1 border cursor-zoom-in hover:border-primary hover:shadow-md transition-all group relative" title="Klik untuk perbesar">
                <img src="${objUrl}" class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform">
                <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-[16px]">zoom_in</span>
                </div>
            </div>
        </td>
        <td class="px-4 py-3">
            <input type="text" value="${data.title}" 
                   onchange="updateCache(${index}, 'title', this.value)"
                   class="w-full text-sm font-medium border-slate-300 rounded px-2 py-1.5 focus:ring-primary focus:border-primary">
            <div class="text-[10px] text-slate-400 mt-1" title="${data.original_name}">${data.original_name.substring(0,15)}...</div>
        </td>
        <td class="px-4 py-3">
            <select onchange="updateCache(${index}, 'category', this.value)"
                    class="w-full text-sm border-slate-300 rounded px-2 py-1.5 focus:ring-primary focus:border-primary bg-slate-50">
                ${catOptions}
            </select>
        </td>
        <td class="px-4 py-3">
            <select id="prov_select_${index}" onchange="provChange(${index}, this.value)"
                    class="w-full text-sm border-slate-300 rounded px-2 py-1.5 focus:ring-primary focus:border-primary bg-slate-50">
                ${provOptions}
            </select>
            ${(!data.province_id && data.province_name) ? `<div class="text-[10px] text-orange-500 mt-1">AI Deteksi: ${data.province_name}</div>` : ''}
        </td>
        <td class="px-4 py-3">
            <select id="city_select_${index}" onchange="updateCache(${index}, 'city_id', this.value)"
                    class="w-full text-sm border-slate-300 rounded px-2 py-1.5 focus:ring-primary focus:border-primary bg-slate-50">
                <option value="">${data.city_id ? '- Memuat -' : '- Pilih Kab/Kota -'}</option>
            </select>
            ${(!data.city_id && data.city_name) ? `<div class="text-[10px] text-orange-500 mt-1">AI Deteksi: ${data.city_name}</div>` : ''}
        </td>
        <td class="px-4 py-3 text-center">
            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded uppercase">Siap</span>
        </td>
        <td class="px-4 py-3 text-center">
            <button onclick="removeRow('${rowId}', ${index})" class="text-red-500 hover:bg-red-50 p-1.5 rounded" title="Hapus dari daftar">
                <span class="material-symbols-outlined text-lg">delete</span>
            </button>
        </td>
    `;
    
    // Auto load cities if a province was matched
    if (data.province_id || document.getElementById(`prov_select_${index}`).value) {
        const pId = data.province_id || document.getElementById(`prov_select_${index}`).value;
        loadCitiesForIdx(index, pId, data.city_id);
    }
}

function updateRowToError(rowId, fallbackName, errorMsg) {
    const tr = document.getElementById(rowId);
    if (!tr) return;
    tr.classList.remove('animate-pulse');
    tr.classList.add('bg-red-50/50');
    tr.innerHTML = `
        <td class="px-4 py-3">
            <div class="size-12 bg-slate-100 rounded overflow-hidden flex items-center justify-center p-1 border border-red-200">
                <span class="material-symbols-outlined text-red-300">broken_image</span>
            </div>
        </td>
        <td class="px-4 py-3 text-sm font-medium text-slate-600">${fallbackName}</td>
        <td colspan="3" class="px-4 py-3 text-xs text-red-500 font-medium">Gagal: ${errorMsg}</td>
        <td class="px-4 py-3 text-center">
            <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-1 rounded uppercase">Error</span>
        </td>
         <td class="px-4 py-3 text-center">
            <button onclick="document.getElementById('${rowId}').remove()" class="text-red-500 hover:bg-red-50 p-1.5 rounded" title="Hapus">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </td>
    `;
}

// -------------------------------------------------------------
// Interactive Functions 
// -------------------------------------------------------------
function showPreviewPanel(imgUrl) {
    const panel = document.getElementById('image-preview-panel');
    const img = document.getElementById('preview-panel-img');
    img.src = imgUrl;
    panel.classList.remove('hidden');
}

function closePreviewPanel() {
    document.getElementById('image-preview-panel').classList.add('hidden');
}

// Close preview panel when clicking outside
document.addEventListener('click', function(e) {
    const panel = document.getElementById('image-preview-panel');
    if (!panel.classList.contains('hidden')) {
        // If click is not inside the panel, and not on a thumbnail image
        if (!panel.contains(e.target) && !e.target.closest('.cursor-zoom-in')) {
            closePreviewPanel();
        }
    }
});

// Hover zoom handler for preview panel
const previewPanelImg = document.getElementById('preview-panel-img');
previewPanelImg.addEventListener('mousemove', function(e) {
    const { left, top, width, height } = this.getBoundingClientRect();
    const x = (e.clientX - left) / width * 100;
    const y = (e.clientY - top) / height * 100;
    this.style.transformOrigin = `${x}% ${y}%`;
});
previewPanelImg.addEventListener('mouseleave', function() {
    this.style.transformOrigin = 'center center';
});

function updateCache(index, key, val) {
    if (logosCache[index]) {
        logosCache[index][key] = val;
    }
}

function provChange(index, provId) {
    updateCache(index, 'province_id', provId);
    updateCache(index, 'city_id', null); // reset city
    loadCitiesForIdx(index, provId, null);
}

function loadCitiesForIdx(index, provinceId, selectedCityId) {
    const citySelect = document.getElementById(`city_select_${index}`);
    if (!citySelect) return;
    
    if (!provinceId) {
        citySelect.innerHTML = '<option value="">- Pilih Kab/Kota -</option>';
        return;
    }
    
    fetch(`api/get-cities.php?province_id=${provinceId}`)
        .then(r => r.json())
        .then(data => {
            let options = '<option value="">- Pilih Kab/Kota -</option>';
            if (data && data.length > 0) {
                data.forEach(city => {
                    options += `<option value="${city.id}">${city.name}</option>`;
                });
            }
            citySelect.innerHTML = options;
            
            if (selectedCityId) {
                citySelect.value = selectedCityId;
            } else {
                citySelect.value = "";
            }
        })
        .catch(err => {
            console.error("Gagal memuat kota:", err);
            citySelect.innerHTML = '<option value="">- Gagal memuat -</option>';
        });
}

function removeRow(rowId, index) {
    const tr = document.getElementById(rowId);
    if (tr) tr.remove();
    // Nullify in cache instead of splice to preserve indexes of other rows
    logosCache[index] = null; 
    
    const activeCount = logosCache.filter(l => l !== null).length;
    totalCountEl.innerText = activeCount;
    
    if (activeCount === 0 && !isUploading) {
        resultsContainer.classList.add('hidden');
    }
}

clearBtn.addEventListener('click', () => {
    showModal('Konfirmasi Hapus', 'Ingin menghapus semua daftar preview?', 'warning', () => {
        logosCache = [];
        logoList.innerHTML = '';
        totalCountEl.innerText = 0;
        resultsContainer.classList.add('hidden');
    }, true);
});

saveBtn.addEventListener('click', async () => {
    // Filter out nulls
    const validLogos = logosCache.filter(l => l !== null);
    
    if (validLogos.length === 0) {
        showModal("Peringatan", "Tidak ada logo yang valid untuk disimpan.", "warning");
        return;
    }
    
    // Quick validation
    for (const logo of validLogos) {
        if (!logo.title || logo.title.trim() === '') {
            showModal("Data Tidak Lengkap", "Beberapa logo tidak memiliki nama. Mohon cek kembali tabel preview.", "warning");
            return;
        }
        if (logo.category !== 'Pemerintahan' && !logo.city_id && logo.category !== 'Kementerian') {
            // Optional warning, sometimes we force them, sometimes we don't.
            console.log(`Warning: Logo ${logo.title} has no city_id selected.`);
        }
    }
    
    saveBtn.innerHTML = `<span class="material-symbols-outlined text-lg animate-spin">sync</span> Menyimpan...`;
    saveBtn.disabled = true;
    
    try {
        const response = await fetch('api/save-bulk-logos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ logos: validLogos })
        });
        
        let result;
        const textResponse = await response.text();
        try {
            result = JSON.parse(textResponse);
        } catch (jsonErr) {
            console.error("Raw response:", textResponse);
            showModal("Error Server", "Terjadi error dari server. Lihat console untuk detail.", "error");
            saveBtn.innerHTML = `<span class="material-symbols-outlined text-lg">save</span> Simpan Semua ke Database`;
            saveBtn.disabled = false;
            return;
        }
        
        if (result.status === 'success' || result.status === 'partial_success') {
            showModal("Berhasil Menyimpan", result.message, "success", () => {
                window.location = 'kelola-logo.php'; // Redirect to normal admin page
            });
        } else {
            showModal("Gagal Menyimpan", "Error: " + result.message, "error");
            saveBtn.innerHTML = `<span class="material-symbols-outlined text-lg">save</span> Simpan Semua ke Database`;
            saveBtn.disabled = false;
        }
    } catch (err) {
        showModal("Error Koneksi", "Terjadi kesalahan koneksi saat menyimpan.", "error");
        saveBtn.innerHTML = `<span class="material-symbols-outlined text-lg">save</span> Simpan Semua ke Database`;
        saveBtn.disabled = false;
    }
});
</script>
</body>
</html>
