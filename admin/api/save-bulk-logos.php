<?php
session_start();
header('Content-Type: application/json');
require '../../config/database.php';

// Check permissions
if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$rawData = file_get_contents("php://input");
$postData = json_decode($rawData, true);

if (!isset($postData['logos']) || !is_array($postData['logos'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data format. Expected an array of logos.']);
    exit();
}

$logos = $postData['logos'];
$savedCount = 0;
$errors = [];

$uploadsDir = __DIR__ . '/../../uploads/logos/';
$tempDir = $uploadsDir . 'temp/';

foreach ($logos as $index => $logo) {
    if (empty($logo['temp_file'])) {
        $errors[] = "Logo at index $index missing temp file reference.";
        continue;
    }

    $title = mysqli_real_escape_string($conn, $logo['title'] ?? '');
    $category = mysqli_real_escape_string($conn, $logo['category'] ?? 'Organisasi');

    $province_id = !empty($logo['province_id']) ? (int)$logo['province_id'] : 'NULL';
    $city_id = !empty($logo['city_id']) ? (int)$logo['city_id'] : 'NULL';

    $tempFile = $logo['temp_file'];
    $tempPath = $tempDir . basename($tempFile); // basename for security

    if (!file_exists($tempPath)) {
        $errors[] = "Temporary file for '$title' not found on server.";
        continue;
    }

    // Compute hash to prevent exact duplicates before moving the file
    $file_hash = hash_file('sha256', $tempPath);

    // =============================
    // CHECK DUPLICATE FILE HASH
    // =============================
    $check_hash = mysqli_query($conn, "SELECT id FROM logos WHERE file_hash='$file_hash' LIMIT 1");
    if (mysqli_num_rows($check_hash) > 0) {
        $errors[] = "Logo '$title' dilewati karena file gambar sama persis sudah ada di database.";
        @unlink($tempPath); // Delete temp file
        continue;
    }

    // =============================
    // CHECK DUPLICATE TITLE + WILAYAH
    // =============================
    $p_check = $province_id !== 'NULL' ? $province_id : 'NULL';
    $c_check = $city_id !== 'NULL' ? $city_id : 'NULL';

    // Build check query depending on if null or numeric
    $where_prov = $p_check === 'NULL' ? "province_id IS NULL" : "province_id = $p_check";
    $where_city = $c_check === 'NULL' ? "city_id IS NULL" : "city_id = $c_check";

    $check_title = mysqli_query($conn,
        "SELECT id FROM logos WHERE title='$title' AND $where_prov AND $where_city LIMIT 1"
    );

    if (mysqli_num_rows($check_title) > 0) {
        $errors[] = "Logo '$title' dilewati karena nama dan wilayah yang sama sudah ada.";
        @unlink($tempPath); // Delete temp file
        continue;
    }

    // Move file to final destination
    $ext = pathinfo($tempFile, PATHINFO_EXTENSION);
    $finalFileName = uniqid('logo_') . '.' . $ext;
    $finalPath = $uploadsDir . $finalFileName;

    if (rename($tempPath, $finalPath)) {
        // Check if DB schema uses province_id/city_id or just strings province/city
        // Based on kelola-logo.php it uses province_id, city_id
        $check_prov_col = mysqli_query($conn, "SHOW COLUMNS FROM logos LIKE 'province_id'");

        if (mysqli_num_rows($check_prov_col) > 0) {
            $sql = "INSERT INTO logos (title, file_name, category, province_id, city_id, file_hash) 
                    VALUES ('$title', '$finalFileName', '$category', $province_id, $city_id, '$file_hash')";
        }
        else {
            // Fallback if schema doesn't match expectations
            $prov_name = !empty($logo['province_name']) ? "'" . mysqli_real_escape_string($conn, $logo['province_name']) . "'" : 'NULL';
            $city_name = !empty($logo['city_name']) ? "'" . mysqli_real_escape_string($conn, $logo['city_name']) . "'" : 'NULL';

            $sql = "INSERT INTO logos (title, file_name, category, province, city) 
                    VALUES ('$title', '$finalFileName', '$category', $prov_name, $city_name)";
        }

        if (mysqli_query($conn, $sql)) {
            $savedCount++;
        }
        else {
            $errors[] = "Database error for '$title': " . mysqli_error($conn);
            // Optionally rollback image move
            @rename($finalPath, $tempPath);
        }
    }
    else {
        $errors[] = "Failed to move file for '$title'.";
    }
}

if ($savedCount > 0 && count($errors) === 0) {
    echo json_encode(['status' => 'success', 'message' => "Successfully saved $savedCount logos."]);
}
else if ($savedCount > 0 && count($errors) > 0) {
    echo json_encode(['status' => 'partial_success', 'message' => "Saved $savedCount logos, but encountered " . count($errors) . " errors.", 'errors' => $errors]);
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save any logos.', 'errors' => $errors]);
}
