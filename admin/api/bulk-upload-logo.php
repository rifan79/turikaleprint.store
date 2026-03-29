<?php
session_start();
header('Content-Type: application/json');
require '../../config/database.php';

// Check permissions
if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// -------------------------------------------------------------------------------------
// CONFIGURATION
// -------------------------------------------------------------------------------------
$GEMINI_API_KEY = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : 'AIzaSyDB7P8M13WVOpp47_b3o1SndEaazXqVfRQ';

// API Key empty check moved to AI execution block



if (!isset($_FILES['logo'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Field logo tidak ditemukan di $_FILES',
        'files' => $_FILES
    ]);
    exit();
}

if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    $error = $_FILES['logo']['error'];

    $errorMessages = [
        1 => 'File melebihi upload_max_filesize (php.ini)',
        2 => 'File melebihi MAX_FILE_SIZE (form)',
        3 => 'Upload hanya sebagian',
        4 => 'Tidak ada file dikirim',
        6 => 'Folder tmp tidak ada',
        7 => 'Gagal menulis ke disk',
        8 => 'Diblokir ekstensi PHP'
    ];

    echo json_encode([
        'status' => 'error',
        'message' => $errorMessages[$error] ?? 'Unknown upload error',
        'error_code' => $error,
        'debug' => $_FILES['logo']
    ]);
    exit();
}

$tmpPath = $_FILES['logo']['tmp_name'];
$originalName = $_FILES['logo']['name'];
$fileSize = $_FILES['logo']['size'];
$fileType = mime_content_type($tmpPath);

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, WEBP are allowed.']);
    exit();
}

if ($fileSize > 1 * 1024 * 1024) { //1MB limit for individual files during bulk upload
    echo json_encode(['status' => 'error', 'message' => 'File too large (Max 1MB).']);
    exit();
}

// -------------------------------------------------------------------------------------
// 1. SAVE TO TEMP FOLDER
// -------------------------------------------------------------------------------------
$uploadsDir = __DIR__ . '/../../uploads/logos/temp/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$ext = pathinfo($originalName, PATHINFO_EXTENSION);
$tempFileName = uniqid('temp_logo_') . "." . $ext;
$targetPath = $uploadsDir . $tempFileName;

if (!move_uploaded_file($tmpPath, $targetPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
    exit();
}


// -------------------------------------------------------------------------------------
// 2. PREPARE IMAGE FOR GEMINI API OR MANUAL
// -------------------------------------------------------------------------------------
$useAi = isset($_POST['use_ai']) && $_POST['use_ai'] === '1';

if ($useAi) {
    if (empty($GEMINI_API_KEY)) {
        echo json_encode(['status' => 'error', 'message' => 'Gemini API Key is missing.']);
        exit();
    }

    $imageData = file_get_contents($targetPath);
    $base64Image = base64_encode($imageData);
    // We need to fetch the list of provinces from DB to help AI pick the exact name
    $provinces = [];
    $provQ = mysqli_query($conn, "SELECT name FROM provinces");
    while ($p = mysqli_fetch_assoc($provQ)) {
    $provinces[] = $p['name'];
}
$provinceListStr = implode(", ", $provinces);
$prompt = "Tolong periksa gambar logo ini dengan seksama. JIKA ANDA TIDAK YAKIN, GUNAKAN HASIL PENCARIAN GOOGLE (GOOGLE SEARCH) untuk mencari tahu logo ini milik instansi/sekolah mana dan berlokasi di mana secara pasti.
Saya butuh Anda mengekstrak informasi berikut berdasarkan teks yang ada di logo ATAUPUN dari hasil penelusuran web:
1. Nama logo (Misal: Universitas Hasanuddin, SMAN 1 Maros, Kementerian Kesehatan).  
2. Kategori logo (Apakah Pemerintahan, Perguruan Tinggi, Sekolah, atau Organisasi).
3. Provinsi dari instansi tersebut (Sesuaikan dengan daftar provinsi).
4. Kabupaten/Kota dari instansi tersebut.

Keluarkan *hanya* dalam format JSON murni tanpa markdown block (tanpa ```json):

{
  \"name\": \"Nama instansi/sekolah/organisasi/pemerintahan dari logo ini\",
  \"category\": \"Kategori yang paling tepat (Pilih SATU dari: Sekolah, Pemerintahan, Perguruan Tinggi, Organisasi)\",
  \"province\": \"Nama Provinsi (harus persis salah satu dari daftar ini: $provinceListStr). Jika tidak diketahui, kosongkan atau null.\",
  \"city\": \"Nama Kota/Kabupaten (Misal: Kabupaten Maros, Kota Makassar). Jika tidak diketahui, kosongkan atau null.\"
}

ATURAN PENTING:
1. Jika ini Logo Kementerian atau lembaga pusat, province dan city biarkan kosong.
2. Jika ini Logo Provinsi (contoh: Logo Provinsi Sulawesi Selatan), city biarkan kosong.
3. Untuk sekolah, lengkapi nama sekolahnya sebaik mungkin berdasarkan teks di logo (contoh: SMP Negeri 1 Maros).
4. Hasil HARUS berupa JSON valid yang bisa di-parse.
";

// -------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------
// 3. CALL GEMINI API (REST API cURL)
// -------------------------------------------------------------------------------------
$geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . $GEMINI_API_KEY;
$requestData = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt],
                [
                    "inline_data" => [
                        "mime_type" => $fileType,
                        "data" => $base64Image
                    ]
                ]
            ]
        ]
    ],
    "tools" => [
        [
            "googleSearch" => new stdClass()
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.1 // Low temperature for factual extraction
    ]];
$ch = curl_init($geminiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData)); // Disable SSL verification for local dev if needed, but better to keep it enabled
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit();
}
curl_close($ch);
if ($httpCode !== 200) {
    $errorObj = json_decode($response, true);
    $errMsg = isset($errorObj['error']['message']) ? $errorObj['error']['message'] : ('HTTP Code: ' . $httpCode);
    echo json_encode(['status' => 'error', 'message' => 'Gemini API Error: ' . $errMsg]);
    exit();
}
$responseData = json_decode($response, true);
$aiText = '';
if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'];
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Unexpected API response structure.', 'raw' => $response]);
    exit();
}
// Clean up JSON (sometimes AI wraps in ```json ... ``` despite instructions)
$aiText = trim($aiText);
$aiText = preg_replace('/^```json\s*/i', '', $aiText);
$aiText = preg_replace('/```$/', '', $aiText);
$aiText = trim($aiText);

$logoData = json_decode($aiText, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to parse AI JSON response.', 'raw_ai_text' => $aiText]);
    exit();
}
} else {
    // Manual entry, provide basic extracted placeholders
    $logoData = [
        'name' => pathinfo($originalName, PATHINFO_FILENAME),
        'category' => '',
        'province' => '',
        'city' => ''
    ];
}

// -------------------------------------------------------------------------------------
// 4. MAP TO DATABASE IDs
// -------------------------------------------------------------------------------------
$finalProvinceId = null;
$finalCityId = null;
$finalCityName = $logoData['city'] ?? '';

// Try matching province
if (!empty($logoData['province'])) {
    $stmt = $conn->prepare("SELECT id FROM provinces WHERE name LIKE ? LIMIT 1");
    $provSearch = '%' . $logoData['province'] . '%';
    $stmt->bind_param("s", $provSearch);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $finalProvinceId = $row['id'];
    }
    $stmt->close();
}

// Try matching city if province is found and city exists
if ($finalProvinceId && !empty($finalCityName)) {
    // City names in DB might be "KABUPATEN MAROS" while AI says "Kabupaten Maros"
    $stmt = $conn->prepare("SELECT id FROM cities WHERE province_id = ? AND name LIKE ? LIMIT 1");
    // Try to remove "Kota " or "Kabupaten " prefixes for broader search if direct match fails
    $cityNameClean = str_ireplace(['Kabupaten ', 'Kota '], '', $finalCityName);
    $citySearch = '%' . $cityNameClean . '%';

    $stmt->bind_param("is", $finalProvinceId, $citySearch);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $finalCityId = $row['id'];
    }
    $stmt->close();
}

// Ensure category is one of the valid ones
$validCategories = ['Sekolah', 'Pemerintahan', 'Perguruan Tinggi', 'Organisasi'];
$finalCategory = in_array($logoData['category'] ?? '', $validCategories) ? $logoData['category'] : 'Organisasi'; // default fallback


// -------------------------------------------------------------------------------------
// 5. RETURN SUCCESS RESPONSE
// -------------------------------------------------------------------------------------
echo json_encode([
    'status' => 'success',
    'data' => [
        'original_name' => $originalName,
        'temp_file' => $tempFileName, // Important: UI needs this to submit later
        'temp_url' => '/uploads/logos/temp/' . $tempFileName,
        'title' => $logoData['name'] ?? '',
        'category' => $finalCategory,
        'province_id' => $finalProvinceId,
        'province_name' => $logoData['province'] ?? '',
        'city_id' => $finalCityId,
        'city_name' => $finalCityName,
    ]
]);
