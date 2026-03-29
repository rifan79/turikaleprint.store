<?php

require_once __DIR__ . '/../config/database.php';

if(!isset($_GET['slug'])){
    die("Logo tidak ditemukan");
}

$slug = $_GET['slug'];

$stmt = $conn->prepare("
SELECT logos.*, 
provinces.name AS province_name,
cities.name AS city_name
FROM logos
LEFT JOIN provinces ON logos.province_id = provinces.id
LEFT JOIN cities ON logos.city_id = cities.id
WHERE logos.slug = ?
");

$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Logo tidak ditemukan");
}

$logo = $result->fetch_assoc();

$related = $conn->prepare("
SELECT slug, title, file_name
FROM logos
WHERE category = ?
AND id != ?
LIMIT 6
");

$related->bind_param("si", $logo['category'], $logo['id']);
$related->execute();
$related_result = $related->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>

<title><?= $logo['title'] ?> - Download Logo</title>

<meta name="description" content="Download logo <?= $logo['title'] ?> format PNG kualitas tinggi.">

<link rel="canonical" href="/logo/<?= $logo['slug'] ?>">

</head>

<body>

<h1><?= $logo['title'] ?></h1>

<p>
Kategori: <?= $logo['category'] ?>
</p>

<p>
Lokasi: <?= $logo['city_name'] ?>, <?= $logo['province_name'] ?>
</p>

<img src="/uploads/logos/<?= $logo['file_name'] ?>" width="300">

<br><br>

<a href="/uploads/logos/<?= $logo['file_name'] ?>" download>
Download Logo
</a>

<h2>Logo Terkait</h2>

<div>

<?php while($r = $related_result->fetch_assoc()): ?>

<a href="/logo/<?= $r['slug'] ?>">

<img src="/uploads/logos/<?= $r['file_name'] ?>" width="100">

<p><?= $r['title'] ?></p>

</a>

<?php endwhile; ?>

</div>

</body>

</html>