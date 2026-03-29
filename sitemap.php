<?php

require_once "config/database.php";
require_once "config/app.php";

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<!-- Homepage -->
<url>
    <loc><?= BASE_URL ?></loc>
    <priority>1.0</priority>
</url>

<!-- Halaman artikel -->
<url>
    <loc><?= BASE_URL ?>artikel</loc>
    <priority>0.9</priority>
</url>

<?php
// ambil semua artikel published
$query = $conn->query("
SELECT slug, created_at 
FROM articles 
WHERE status='published'
");

while ($row = $query->fetch_assoc()):


$result = $conn->query("SELECT slug FROM logos");

while($row = $result->fetch_assoc()){

echo "<url>";
echo "<loc>http://localhost:8000/logo/".$row['slug']."</loc>";
echo "</url>";

}
?>

<url>
    <loc><?= BASE_URL ?>artikel/<?= $row['slug'] ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($row['created_at'])) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
</url>

<?php endwhile; ?>

</urlset>