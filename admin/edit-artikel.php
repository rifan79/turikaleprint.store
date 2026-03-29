<?php

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: kelola-artikel.php");
    exit();
}

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"
SELECT articles.*, article_categories.name AS category_name
FROM articles
LEFT JOIN article_categories
ON articles.category_id = article_categories.id
WHERE articles.id=$id
LIMIT 1
");

/* ============================
   HANDLE UPDATE ARTICLE
============================ */



if(isset($_POST['update_article'])){

$id = (int)$_POST['id'];

$title = mysqli_real_escape_string($conn,$_POST['title']);
$excerpt = mysqli_real_escape_string($conn,$_POST['excerpt']);
$content = mysqli_real_escape_string($conn,$_POST['content']);
$category_id = (int)$_POST['category_id'];
$status = mysqli_real_escape_string($conn,$_POST['status']);
 $author = isset($_SESSION['admin']) ? mysqli_real_escape_string($conn,$_POST['author']) : 'Admin';

$q = mysqli_query($conn,"SELECT thumbnail FROM articles WHERE id=$id");
$data = mysqli_fetch_assoc($q);

$thumbnail = $data['thumbnail'];

/* ============================
   UPLOAD THUMBNAIL BARU
============================ */

if(!empty($_FILES['thumbnail']['name'])){

$ext = strtolower(pathinfo($_FILES['thumbnail']['name'],PATHINFO_EXTENSION));

if(!in_array($ext,['png','jpg','jpeg','webp'])){
die("Format thumbnail tidak valid");
}

$new_thumb = uniqid('art_').".".$ext;

$tmp = $_FILES['thumbnail']['tmp_name'];

$upload_dir = __DIR__.'/../uploads/articles/thumbnails/';

if(!is_dir($upload_dir)){
mkdir($upload_dir,0755,true);
}

$target = $upload_dir.$new_thumb;

if(move_uploaded_file($tmp,$target)){

if($thumbnail && file_exists($upload_dir.$thumbnail)){
unlink($upload_dir.$thumbnail);
}

$thumbnail = $new_thumb;

}

}

/* ============================
   UPDATE DATABASE
============================ */

mysqli_query($conn,"
UPDATE articles SET
title='$title',
excerpt='$excerpt',
content='$content',
category_id=$category_id,
status='$status',
author='$author',
thumbnail='$thumbnail'
WHERE id=$id
");

header("Location: kelola-artikel.php");
exit();

}

if(mysqli_num_rows($q)==0){
    header("Location: kelola-artikel.php");
    exit();
}

$article = mysqli_fetch_assoc($q);

$categories = mysqli_query($conn,"SELECT * FROM article_categories ORDER BY name ASC");

?>

<!doctype html>
<html lang="id">

<head>
<meta charset="utf-8">
<title>Edit Artikel</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>

</head>

<body class="bg-slate-100 font-[Inter]">

<div class="max-w-4xl mx-auto p-8">

<h1 class="text-2xl font-bold mb-6">
Edit Artikel
</h1>

<form method="POST" enctype="multipart/form-data" class="space-y-6">

<input type="hidden" name="id" value="<?= $article['id'] ?>">

<div>
<label class="text-sm font-semibold">Judul</label>

<input
type="text"
name="title"
value="<?= htmlspecialchars($article['title']) ?>"
class="w-full border rounded-lg p-3">
</div>

<div>
<label class="text-sm font-semibold">Thumbnail</label>

<div class="flex gap-4 items-center">

<?php if($article['thumbnail']){ ?>

<img
src="https://turikaleprint.store/uploads/articles/thumbnails/<?= htmlspecialchars($article['thumbnail']) ?>"
class="w-24 rounded">

<?php } ?>

<input type="file" name="thumbnail">

</div>
</div>

<div>
<label class="text-sm font-semibold">Excerpt</label>

<textarea
name="excerpt"
class="w-full border rounded-lg p-3"
rows="3"><?= htmlspecialchars($article['excerpt']) ?></textarea>

</div>

<div>
<label class="text-sm font-semibold">Konten</label>

<textarea
id="content"
name="content"><?= htmlspecialchars($article['content']) ?></textarea>

</div>

<div class="grid grid-cols-2 gap-4">

<select name="category_id" class="border rounded-lg p-3">

<?php while($cat = mysqli_fetch_assoc($categories)){ ?>

<option
value="<?= $cat['id'] ?>"
<?= $cat['id']==$article['category_id'] ? 'selected' : '' ?>>

<?= htmlspecialchars($cat['name']) ?>

</option>

<?php } ?>

</select>

<select name="status" class="border rounded-lg p-3">

<option value="published" <?= $article['status']=='published'?'selected':'' ?>>
Published
</option>

<option value="draft" <?= $article['status']=='draft'?'selected':'' ?>>
Draft
</option>

</select>

</div>
<div class="space-y-2">
<label class="text-sm font-semibold">Penulis</label>

<input
name="author"
type="text"
placeholder="Nama penulis artikel"
class="w-full px-4 py-3 rounded-xl border border-slate-200 "
required
/>

</div>

<button
name="update_article"
class="bg-blue-600 text-white px-6 py-3 rounded-lg">

Update Artikel

</button>

</form>

</div>

<script>

tinymce.init({
selector:'#content',
height:400,
plugins:'link image lists table code',
toolbar:'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
});

</script>

</body>
</html>