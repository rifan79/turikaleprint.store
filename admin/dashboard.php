<?php
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Handle delete (optional)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    // get file
    $q = mysqli_query($conn, "SELECT file_name FROM logos WHERE id = $id LIMIT 1");
    if ($r = mysqli_fetch_assoc($q)) {
        $file = $r['file_name'];
        $path = __DIR__ . 'https://turikaleprint.store/uploads/logos/' . $file;
        if (file_exists($path)) {
            @unlink($path);
        }
    }
    mysqli_query($conn, "DELETE FROM logos WHERE id = $id");
    $_SESSION['flash_msg'] = 'Logo berhasil dihapus!';
    $_SESSION['flash_type'] = 'success';
    $_SESSION['flash_title'] = 'Berhasil';
    header('Location: dashboard.php');
    exit();
}

// Handle upload
if (isset($_POST['upload_logo'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    $province_id = $_POST['province'] !== '' ? (int)$_POST['province'] : null;
    $city_id = $_POST['city'] !== '' ? (int)$_POST['city'] : null;

    $province_name = isset($_POST['province_name']) ? mysqli_real_escape_string($conn, $_POST['province_name']) : null;
    $city_name = isset($_POST['city_name']) ? mysqli_real_escape_string($conn, $_POST['city_name']) : null;

    $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
    $file = uniqid('logo_') . "." . $ext;
    $tmp = $_FILES['logo']['tmp_name'];

    if ($file != '' && is_uploaded_file($tmp)) {

        $file_hash = hash_file('sha256', $tmp);

        // =============================
        // CEK DUPLIKAT FILE
        // =============================
        $check_hash = mysqli_query($conn, "SELECT id FROM logos WHERE file_hash='$file_hash' LIMIT 1");

        if(mysqli_num_rows($check_hash) > 0){
            $_SESSION['flash_msg'] = 'Logo ini sudah pernah diupload!';
            $_SESSION['flash_type'] = 'warning';
            $_SESSION['flash_title'] = 'Peringatan';
            header("Location: dashboard.php");
            exit();
        }

        // =============================
        // CEK DUPLIKAT TITLE + WILAYAH
        // =============================
        $check_title = mysqli_query($conn,
            "SELECT id FROM logos 
            WHERE title='$title' 
            AND province_id='$province_id' 
            AND city_id='$city_id'
            LIMIT 1"
        );

        if(mysqli_num_rows($check_title) > 0){
            $_SESSION['flash_msg'] = 'Logo dengan nama dan wilayah ini sudah ada!';
            $_SESSION['flash_type'] = 'warning';
            $_SESSION['flash_title'] = 'Peringatan';
            header("Location: dashboard.php");
            exit();
        }

        // =============================
        // UPLOAD FILE
        // =============================
        $uploads_dir = __DIR__ . '/../uploads/logos/';

        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }

        $target = $uploads_dir . $file;

        if (move_uploaded_file($tmp, $target)) {

            $has_prov_id = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM logos LIKE 'province_id'")) > 0;
            $has_city_id = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM logos LIKE 'city_id'")) > 0;

            if ($has_prov_id || $has_city_id) {

                $p = $province_id !== null ? $province_id : 'NULL';
                $c = $city_id !== null ? $city_id : 'NULL';

                $sql = "INSERT INTO logos 
                (title, file_name, category, province_id, city_id, file_hash)
                VALUES
                ('$title','$file','$category',$p,$c,'$file_hash')";

            } else {

                $prov = $province_name !== null ? "'$province_name'" : 'NULL';
                $cty = $city_name !== null ? "'$city_name'" : 'NULL';

                $sql = "INSERT INTO logos 
                (title, file_name, category, province, city)
                VALUES
                ('$title','$file','$category',$prov,$cty)";
            }

            mysqli_query($conn, $sql);
            $_SESSION['flash_msg'] = 'Logo berhasil diunggah!';
            $_SESSION['flash_type'] = 'success';
            $_SESSION['flash_title'] = 'Berhasil';
        }
    }

    header('Location: dashboard.php');
    exit();
}

// ======================
// HANDLE EDIT
// ======================

if(isset($_POST['update_logo'])){

$id = (int)$_POST['edit_id'];

$title = mysqli_real_escape_string($conn,$_POST['edit_title']);
$category = mysqli_real_escape_string($conn,$_POST['edit_category']);

$q = mysqli_query($conn,"SELECT file_name FROM logos WHERE id=$id");
$data = mysqli_fetch_assoc($q);

$file = $data['file_name'];

if(!empty($_FILES['edit_logo']['name'])){

$ext = pathinfo($_FILES['edit_logo']['name'],PATHINFO_EXTENSION);
$new_file = uniqid('logo_').".".$ext;

$tmp = $_FILES['edit_logo']['tmp_name'];

$target = __DIR__.'/../uploads/logos/'.$new_file;

if(move_uploaded_file($tmp,$target)){

unlink(__DIR__.'/../uploads/logos/'.$file);

$file=$new_file;

}

}

mysqli_query($conn,"
UPDATE logos
SET title='$title',
category='$category',
file_name='$file'
WHERE id=$id
");

$_SESSION['flash_msg'] = 'Logo berhasil diperbarui!';
$_SESSION['flash_type'] = 'success';
$_SESSION['flash_title'] = 'Berhasil';

header("Location: dashboard.php");
exit();

}

// ===============================
// Ambil total logo
// ===============================

$total_logo = 0;

$count_query = mysqli_query($conn, 'SELECT COUNT(*) as total FROM logos');

if ($count_query) {
    $row_count = mysqli_fetch_assoc($count_query);
    $total_logo = $row_count['total'];
}

// ===============================
// Ambil 5 logo terbaru
// ===============================

$result = mysqli_query(
    $conn,
    "
SELECT *
FROM logos
ORDER BY id DESC
LIMIT 5
",
);

/* ============================
   HANDLE DELETE ARTICLE (GET)
   ============================ */
if (isset($_GET['delete_article']) && is_numeric($_GET['delete_article'])) {
    $aid = (int) $_GET['delete_article'];

    // ambil thumbnail
    $qart = mysqli_query($conn, "SELECT thumbnail FROM articles WHERE id = $aid LIMIT 1");
    if ($rart = mysqli_fetch_assoc($qart)) {
        $thumb = $rart['thumbnail'];
        $thumbPath = __DIR__ . 'https://turikaleprint.store/uploads/articles/thumbnails/' . $thumb;
        if ($thumb && file_exists($thumbPath)) {
            @unlink($thumbPath);
        }
    }

    mysqli_query($conn, "DELETE FROM articles WHERE id = $aid");
    $_SESSION['flash_msg'] = 'Artikel berhasil dihapus!';
    $_SESSION['flash_type'] = 'success';
    $_SESSION['flash_title'] = 'Berhasil';
    header('Location: dashboard.php');
    exit();
}

/* ============================
   HANDLE UPLOAD / CREATE ARTICLE (POST)
   nama form field: upload_article
   ============================ */
if (isset($_POST['upload_article'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
    // content is HTML from TinyMCE - store raw HTML (but still escape when printing)
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = (int) $_POST['category_id'];
    $status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'draft';
    $author = isset($_SESSION['admin']) ? mysqli_real_escape_string($conn, $_SESSION['admin']) : 'Admin';

    // generate slug, ensure unique
    $base_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
    $slug = trim($base_slug, '-');
    $orig = $slug;
    $i = 1;
    while (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM articles WHERE slug = '$slug' LIMIT 1")) > 0) {
        $slug = $orig . '-' . $i;
        $i++;
    }

    // handle thumbnail upload (optional)
    $thumbnail = null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png','jpg','jpeg','webp'])) {
            $_SESSION['flash_msg'] = 'Thumbnail harus berupa png/jpg/webp';
            $_SESSION['flash_type'] = 'warning';
            $_SESSION['flash_title'] = 'Gagal';
            header("Location: dashboard.php");
            exit();
        }
        // create folder if not exist
        $uploads_dir = __DIR__ . '/../uploads/articles/thumbnails/';
        if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

        $thumb_name = uniqid('art_') . '.' . $ext;
        $tmp = $_FILES['thumbnail']['tmp_name'];
        if (move_uploaded_file($tmp, $uploads_dir . $thumb_name)) {
            $thumbnail = $thumb_name;
        }
        if ($_FILES['thumbnail']['size'] > 1 * 1024 * 1024) {
            $_SESSION['flash_msg'] = 'Thumbnail maksimal 2MB';
            $_SESSION['flash_type'] = 'warning';
            $_SESSION['flash_title'] = 'Gagal';
            header("Location: dashboard.php");
            exit();
        }
    }

    $thumbnail_sql = $thumbnail ? "'$thumbnail'" : "NULL";

    $sql = "INSERT INTO articles (title, slug, excerpt, content, category_id, thumbnail, author, status)
VALUES ('$title','$slug','$excerpt','$content','$category_id','$thumbnail','$author','$status')";

    mysqli_query($conn, $sql);

    $_SESSION['flash_msg'] = 'Artikel berhasil ditambahkan!';
    $_SESSION['flash_type'] = 'success';
    $_SESSION['flash_title'] = 'Berhasil';

    header('Location: dashboard.php');
    exit();
}

/* TOTAL ARTIKEL */
$total_articles = $conn->query("SELECT COUNT(*) as total FROM articles")->fetch_assoc()['total'];

/* ARTIKEL PUBLISHED */
$total_published = $conn->query("SELECT COUNT(*) as total FROM articles WHERE status='published'")
->fetch_assoc()['total'];

/* ARTIKEL DRAFT */
$total_draft = $conn->query("SELECT COUNT(*) as total FROM articles WHERE status='draft'")
->fetch_assoc()['total'];

$categories = mysqli_query($conn, "SELECT * FROM article_categories ORDER BY name ASC");
/* ARTIKEL TERBARU */
$r_articles = mysqli_query($conn, "
SELECT articles.*, article_categories.name AS category_name
FROM articles
LEFT JOIN article_categories 
ON articles.category_id = article_categories.id
ORDER BY articles.id DESC
LIMIT 5
");


?>

<!doctype html>

<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
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
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar Navigation -->
        <aside
            class="w-48 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col fixed h-full transition-colors duration-300">
            <div class="p-6 flex items-center gap-3">
                <img src="https://turikaleprint.store/public/assets/images/Logo Biru-TurikalePrint.png" alt="Turikale Print" class="h-10 w-auto" />
            </div>
            <nav class="flex-1 px-4 space-y-1 mt-4">
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary group" href="#">
                    <span class="material-symbols-outlined text-[22px]">dashboard</span>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group"
                    href="kelola-logo.php">
                    <span class="material-symbols-outlined text-[22px]">image</span>
                    <span class="text-sm font-medium">Kelola Logo</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group"
                    href="bulk-upload.php">
                    <span class="material-symbols-outlined text-[22px]">auto_awesome</span>
                    <span class="text-sm font-medium">Upload AI</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group"
                    href="kelola-artikel.php">
                    <span class="material-symbols-outlined text-[22px]">description</span>
                    <span class="text-sm font-medium">Kelola Artikel</span>
                </a>
                <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800">
                    <a class="hidden items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-primary/10 hover:text-primary transition-colors"
                        href="#">
                        <span class="material-symbols-outlined">settings</span>
                        <span class="text-sm font-medium">Pengaturan</span>
                    </a>
                    <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-red-500 hover:bg-red-50 transition-colors"
                        href="logout.php">
                        <span class="material-symbols-outlined">logout</span>
                        <span class="text-sm font-medium">Keluar</span>
                    </a>
                </div>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 p-5 ml-48">
            <!-- Header Section -->
             
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-bold">
                        Halo, Admin!
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400">
                        Selamat datang di dashboard pengelolaan konten Turikale Print.
                    </p>
                </div>
            </div>
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div
                    class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">
                            Total Logo
                        </p>
                        <span class="material-symbols-outlined text-primary">image</span>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl font-bold text-slate-900 dark:text-white">
                            <?php echo $total_logo; ?>
                        </p>
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">
                            Total Artikel
                        </p>
                        <span class="material-symbols-outlined text-primary">article</span>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl font-bold text-slate-900 dark:text-white">
                            <?= $total_articles ?>
                        </p>
                    </div>
                </div>
                
            </div>
            <!-- Content Management Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Logo Terbaru Table -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Logo Terbaru</h3>
                        
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/50">
                                    <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase">
                                        Preview
                                    </th>
                                    <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase">
                                        Nama Logo
                                    </th>
                                    <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase">
                                        Kategori
                                    </th>
                                    <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase text-right">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                                <?php while($row = mysqli_fetch_assoc($result)) { ?>

                                <tr>

                                    <td class="px-5 py-4">
                                        <div class="size-10 rounded overflow-hidden border">
                                            <img src="https://turikaleprint.store/uploads/logos/<?php echo $row['file_name']; ?>"
                                                class="w-full h-full object-cover">
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 text-sm font-medium">
                                        <?php echo $row['title']; ?>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-slate-500">
                                        <?php echo $row['category']; ?>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2" >

                                            <button 
onclick="openEditModal(
'<?php echo $row['id']; ?>',
'<?php echo htmlspecialchars($row['title']); ?>',
'<?php echo $row['category']; ?>',
'<?php echo $row['file_name']; ?>'
)"
class="p-1.5 text-blue-500 hover:bg-blue-50 rounded">

<span class="material-symbols-outlined text-xl">edit</span>

</button>

                                            <a href="#" onclick="event.preventDefault(); showModal('Konfirmasi Hapus', 'Hapus logo ini?', 'warning', () => { window.location='dashboard.php?delete=<?php echo $row['id']; ?>&file=<?php echo $row['file_name']; ?>'; }, true)">

                                            <button class="p-1.5 text-red-500 hover:bg-red-50 rounded">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>

                                        </a>

                                        </div>
                                    </td>

                                </tr>

                                <?php } ?>

                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Artikel Terbaru Table -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Artikel Terbaru</h3>
                        
                    </div>
                    <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-800/50">
                <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase">Preview</th>
                <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase">Judul</th>
                <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase">Kategori</th>
                <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php
            // ambil 5 artikel terbaru (include thumbnail & category)
            
            while ($art = mysqli_fetch_assoc($r_articles)) { ?>
                <tr>
                    <td class="px-5 py-4">
                        <div class="size-10 rounded overflow-hidden border">
                            <?php if (!empty($art['thumbnail']) && file_exists(__DIR__ . '/../uploads/articles/thumbnails/' . $art['thumbnail'])): ?>
                                <img src="https://turikaleprint.store/uploads/articles/thumbnails/<?php echo $art['thumbnail']; ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                    <span class="material-symbols-outlined">image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm font-medium"><?php echo htmlspecialchars($art['title']); ?></td>
                    <td class="px-5 py-4 text-sm text-slate-500"><?php echo htmlspecialchars($art['category_name']); ?></td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="edit-artikel.php?id=<?php echo $art['id']; ?>" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded">
                                <span class="material-symbols-outlined text-xl">edit</span>
                            </a>
                            <a href="#" onclick="event.preventDefault(); showModal('Konfirmasi Hapus', 'Hapus artikel ini?', 'warning', () => { window.location='dashboard.php?delete_article=<?php echo $art['id']; ?>'; }, true)">
                                <button class="p-1.5 text-red-500 hover:bg-red-50 rounded">
                                    <span class="material-symbols-outlined text-xl">delete</span>
                                </button>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
                </div>
            </div>
            <!-- Form Sections -->
            <div class="mt-12 grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Upload Logo Form -->
                <div
                    class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <h3 class="font-bold text-xl mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">cloud_upload</span>
                        Tambah Logo Baru
                    </h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div id="drop-area"
                            class="border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-lg p-8 flex flex-col items-center justify-center text-slate-400 bg-slate-50 dark:bg-slate-800/30 hover:border-primary/50 transition-colors cursor-pointer">

                            <input type="file" name="logo" id="logoInput" accept="image/png" class="hidden"
                                required>

                            <img id="previewImage" class="hidden w-24 mb-3 rounded">

                            <span class="material-symbols-outlined text-4xl mb-2">
                                upload_file
                            </span>

                            <p class="text-sm">Klik atau seret file ke sini</p>
                            <p class="text-xs mt-1">PNG (Max 1MB)</p>

                        </div>
                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label class="block text-sm font-semibold mb-1">Nama</label>
                                <input
                                    class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-800 focus:ring-primary focus:border-primary text-sm"
                                    placeholder="e.g. Logo Kabupaten Maros, Logo SMPN 1 Maros" name="title"
                                    type="text" required />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Kategori</label>
                                <select name="category"
                                    class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-800 focus:ring-primary focus:border-primary text-sm" required>
                                    <option value="">Pilih Kategori</option>
                                    <option>Sekolah</option>
                                    <option>Pemerintahan</option>
                                    <option>Perguruan Tinggi</option>
                                    <option>Organisasi</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Provinsi</label>
                                <select id="province_select" name="province"
                                    class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-800 focus:ring-primary focus:border-primary text-sm"
                                    required>
                                    <option value="">Pilih Provinsi</option>
                                    <?php
                                    $provs = mysqli_query($conn, 'SELECT * FROM provinces ORDER BY name ASC');
                                    while ($p = mysqli_fetch_assoc($provs)) {
                                        echo '<option value="' . (int) $p['id'] . '">' . htmlspecialchars($p['name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Kabupaten/Kota</label>
                                <select id="city_select" name="city"
                                    class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-800 focus:ring-primary focus:border-primary text-sm">
                                    <option value="">Pilih Kabupaten</option>

                                </select>
                                <!-- Keep hidden fallback text fields if needed -->
                                <input type="hidden" name="province_name" id="province_name">
                                <input type="hidden" name="city_name" id="city_name">
                            </div>
                        </div>
                        <button
                            class="w-full bg-primary text-white py-2.5 rounded-lg font-bold text-sm hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all"
                            name="upload_logo" type="submit">
                            Unggah Logo
                        </button>
                    </form>
                </div>
                <!-- Add Article Form -->
                <div
                    class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <h3 class="font-bold text-xl mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">edit_note</span>
                        Tambah Artikel Baru
                    </h3>
                    <form class="space-y-4" method="POST" enctype="multipart/form-data">
    <div>
        <label class="block text-sm font-semibold mb-1">Judul Artikel</label>
        <input name="title" required
            class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-800 focus:ring-primary focus:border-primary text-sm"
            placeholder="Masukkan judul menarik..." type="text" />
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Gambar Utama</label>

        <div id="article-drop-area"
            class="border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-lg p-6 flex flex-col items-center justify-center text-slate-400 bg-slate-50 dark:bg-slate-800/30 hover:border-primary/50 transition-colors cursor-pointer">

            <input type="file" name="thumbnail" id="articleThumbnailInput"
            accept="image/png,image/jpeg,image/webp"
                class="hidden">

            <img id="articlePreviewImage" class="hidden w-24 mb-3 rounded">

            <span class="material-symbols-outlined text-3xl mb-2">
                image
            </span>

            <p class="text-sm">Klik atau seret gambar ke sini</p>
            <p class="text-xs mt-1">PNG / JPG / WEBP</p>
        </div>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Ringkasan (excerpt)</label>
        <textarea name="excerpt" class="w-full rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-800 text-sm" rows="2"></textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Konten</label>
        <textarea id="tinymce_content" name="content" class="w-full border-none focus:ring-0 dark:bg-slate-900 text-sm" rows="6" placeholder="Tulis artikel Anda di sini..."></textarea>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <select name="category_id" required 
        class="rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-800 p-2">

            <option value="">Pilih Kategori</option>

            <?php while($cat = mysqli_fetch_assoc($categories)): ?>

            <option value="<?= $cat['id'] ?>">
        <?= htmlspecialchars($cat['name']) ?>
        </option>

        <?php endwhile; ?>

            </select>
        <select name="status" class="rounded-lg border-slate-200 dark:border-slate-800 dark:bg-slate-800 p-2">
            <option value="published">Publikasikan</option>
            <option value="draft">Simpan Draft</option>
        </select>
    </div>
    <div class="space-y-2">
<label class="text-sm font-semibold">Penulis</label>

<input
name="author"
type="text"
placeholder="Nama penulis artikel"
class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 dark:bg-slate-900"
required
/>

</div>

    <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.querySelector('select[name=status]').value='draft'"
            class="flex-1 border border-slate-200 dark:border-slate-700 py-2.5 rounded-lg font-bold text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800">
            Simpan Draft
        </button>
        <button name="upload_article" type="submit"
            class="flex-1 bg-primary text-white py-2.5 rounded-lg font-bold text-sm hover:bg-primary/90 shadow-lg shadow-primary/20">
            Publikasikan
        </button>
    </div>
</form>
                </div>
            </div>
        </main>
    </div>
    <!-- EDIT LOGO MODAL -->

<div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">

<div class="bg-white p-6 rounded-lg w-full max-w-lg">

<h3 class="text-lg font-bold mb-4">Edit Logo</h3>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="edit_id" id="edit_id">

<div class="mb-3">
<label class="text-sm font-semibold">Nama Logo</label>
<input type="text" name="edit_title" id="edit_title"
class="w-full border rounded p-2">
</div>

<div class="mb-3">
<label class="text-sm font-semibold">Kategori</label>

<select name="edit_category" id="edit_category"
class="w-full border rounded p-2">

<option>Sekolah</option>
<option>Pemerintahan</option>
<option>Universitas</option>
<option>Organisasi</option>

</select>
</div>

<div class="mb-3">

<label class="text-sm font-semibold">Ganti Logo (optional)</label>

<input type="file" name="edit_logo" accept="image/png">

</div>

<div class="flex justify-end gap-2">

<button type="button"
onclick="closeEditModal()"
class="px-4 py-2 bg-gray-300 rounded">

Batal

</button>

<button
name="update_logo"
class="px-4 py-2 bg-blue-600 text-white rounded">

Update

</button>

</div>

</form>

</div>

</div>
    <script>
        function previewLogo(event) {

            const file = event.target.files[0];

            if (!file) return;

            if (file.size > 1024 * 1024) {
                alert("Ukuran file maksimal 1MB");
                event.target.value = "";
                return;
            }

            if (file.type !== "image/png") {
                alert("Hanya file PNG yang diperbolehkan");
                event.target.value = "";
                return;
            }

        }
    </script>

    <script>
        const dropArea = document.getElementById("drop-area");
        const input = document.getElementById("logoInput");
        const preview = document.getElementById("previewImage");

        dropArea.addEventListener("click", () => input.click());

        input.addEventListener("change", function() {
            previewFile(this.files[0]);
        });

        dropArea.addEventListener("dragover", function(e) {
            e.preventDefault();
            dropArea.classList.add("border-primary");
        });

        dropArea.addEventListener("dragleave", function() {
            dropArea.classList.remove("border-primary");
        });

        dropArea.addEventListener("drop", function(e) {
            e.preventDefault();
            dropArea.classList.remove("border-primary");

            const file = e.dataTransfer.files[0];
            input.files = e.dataTransfer.files;

            previewFile(file);
        });

        function previewFile(file) {

            if (!file) return;

            if (file.size > 1024 * 1024) {
                alert("Ukuran maksimal 1MB");
                return;
            }

            if (file.type !== "image/png") {
                alert("File harus PNG");
                return;
            }

            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove("hidden");
            }

            reader.readAsDataURL(file);

        }

        // populate hidden text fallback so older code can use names if needed
        document.getElementById('province_select').addEventListener('change', function() {

            let province_id = this.value;

            fetch('api/get-cities.php?province_id=' + province_id)

                .then(response => response.text())

                .then(data => {

                    document.getElementById('city_select').innerHTML = data;

                });

        });

        document.getElementById('city_select').addEventListener('change', function() {

            document.getElementById('city_name').value =
                this.options[this.selectedIndex].text;

        });

        document.querySelector("form").addEventListener("submit", function(e) {

            let category = document.querySelector("select[name='category']").value;
            let province = document.getElementById("province_select").value;
            let city = document.getElementById("city_select").value;

            if (category !== "Pemerintahan" && city === "") {
                alert("Kabupaten/Kota wajib dipilih untuk kategori ini.");
                e.preventDefault();
            }

        });
    </script>
    <script>

function openEditModal(id,title,category,file){

document.getElementById("editModal").classList.remove("hidden")

document.getElementById("edit_id").value=id
document.getElementById("edit_title").value=title
document.getElementById("edit_category").value=category

}

function closeEditModal(){

document.getElementById("editModal").classList.add("hidden")

}

</script>

<script>
    const articleDropArea = document.getElementById("article-drop-area");
const articleInput = document.getElementById("articleThumbnailInput");
const articlePreview = document.getElementById("articlePreviewImage");

articleDropArea.addEventListener("click", () => articleInput.click());

articleInput.addEventListener("change", function(){
    previewArticleImage(this.files[0]);
});

articleDropArea.addEventListener("dragover", function(e){
    e.preventDefault();
    articleDropArea.classList.add("border-primary");
});

articleDropArea.addEventListener("dragleave", function(){
    articleDropArea.classList.remove("border-primary");
});

articleDropArea.addEventListener("drop", function(e){
    e.preventDefault();
    articleDropArea.classList.remove("border-primary");

    const file = e.dataTransfer.files[0];
    articleInput.files = e.dataTransfer.files;

    previewArticleImage(file);
});

function previewArticleImage(file){

    if(!file) return;

    if(file.size > 2 * 1024 * 1024){
        alert("Ukuran maksimal 2MB");
        return;
    }

    const reader = new FileReader();

    reader.onload = function(e){
        articlePreview.src = e.target.result;
        articlePreview.classList.remove("hidden");
    }

    reader.readAsDataURL(file);
}
</script>

<!-- TinyMCE CDN -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
tinymce.init({
  selector: '#tinymce_content',
  height: 350,
  menubar: false,
  plugins: 'link image lists code table',
  toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
  content_css: false
});
</script>
<script>
document.getElementById("province_select").addEventListener("change", function(){

let province_id = this.value

fetch('api/get-cities.php?province_id=' + province_id)

.then(res => res.json())

.then(data => {

let html = '<option value="">Pilih Kabupaten</option>'

data.forEach(city => {

html += `<option value="${city.id}">${city.name}</option>`

})

document.getElementById("city_select").innerHTML = html

})

})
</script>

<?php require_once 'modal.php'; ?>
</body>
</html>


