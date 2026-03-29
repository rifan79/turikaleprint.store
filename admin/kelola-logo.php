<?php
session_start();
require '../config/database.php';



if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// ----------------
// Config
// ----------------
$per_page = 25; // jumlah item per page

// ----------------
// Handle delete
// ----------------
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $q = mysqli_query($conn, "SELECT file_name FROM logos WHERE id = $id LIMIT 1");
    if ($r = mysqli_fetch_assoc($q)) {
        $file = $r['file_name'];
        $path = __DIR__ . '/../uploads/logos/' . $file;
        if (file_exists($path)) {
            @unlink($path);
        }
    }
    mysqli_query($conn, "DELETE FROM logos WHERE id = $id");
    $_SESSION['flash_msg'] = 'Logo berhasil dihapus!';
    $_SESSION['flash_type'] = 'success';
    $_SESSION['flash_title'] = 'Berhasil';
    header('Location: kelola-logo.php');
    exit();
}

// ----------------
// Handle upload (sama dengan dashboard)
// ----------------
if (isset($_POST['upload_logo'])) {



    $title = isset($_POST['title']) ? mysqli_real_escape_string($conn, $_POST['title']) : '';
    $category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';

    $province_id = isset($_POST['province']) && $_POST['province'] !== '' ? (int)$_POST['province'] : null;
    $city_id = isset($_POST['city']) && $_POST['city'] !== '' ? (int)$_POST['city'] : null;

    $province_name = isset($_POST['province_name']) ? mysqli_real_escape_string($conn, $_POST['province_name']) : null;
    $city_name = isset($_POST['city_name']) ? mysqli_real_escape_string($conn, $_POST['city_name']) : null;

    $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
    $file = uniqid('logo_') . "." . $ext;
    $tmp = isset($_FILES['logo']['tmp_name']) ? $_FILES['logo']['tmp_name'] : '';

    if ($file != '' && is_uploaded_file($tmp)) {

        $file_hash = hash_file('sha256', $tmp);

        $check_hash = mysqli_query($conn, "SELECT id FROM logos WHERE file_hash='" . mysqli_real_escape_string($conn, $file_hash) . "' LIMIT 1");
        if (mysqli_num_rows($check_hash) > 0) {
            $_SESSION['flash_msg'] = 'Logo ini sudah pernah diupload!';
            $_SESSION['flash_type'] = 'warning';
            $_SESSION['flash_title'] = 'Peringatan';
            header("Location: kelola-logo.php");
            exit();
        }

        // cek duplikat title + wilayah
        $p = $province_id !== null ? $province_id : 'NULL';
        $c = $city_id !== null ? $city_id : 'NULL';
        $sql_check = "SELECT id FROM logos WHERE title='" . mysqli_real_escape_string($conn, $title) . "' AND province_id=" . ($province_id !== null ? $province_id : "IS NULL") . " ";
        // safer simple check (works for typical cases)
        $check_title_q = mysqli_query($conn,
            "SELECT id FROM logos 
            WHERE title='" . mysqli_real_escape_string($conn, $title) . "' 
            AND (province_id " . ($province_id === null ? "IS NULL" : "= " . (int)$province_id) . ")
            AND (city_id " . ($city_id === null ? "IS NULL" : "= " . (int)$city_id) . ")
            LIMIT 1"
        );

        if (mysqli_num_rows($check_title_q) > 0) {
            $_SESSION['flash_msg'] = 'Logo dengan nama dan wilayah ini sudah ada!';
            $_SESSION['flash_type'] = 'warning';
            $_SESSION['flash_title'] = 'Peringatan';
            header("Location: kelola-logo.php");
            exit();
        }

        // move file
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
                $sql = "INSERT INTO logos (title, file_name, category, province_id, city_id, file_hash) VALUES ('" . mysqli_real_escape_string($conn, $title) . "','" . mysqli_real_escape_string($conn, $file) . "','" . mysqli_real_escape_string($conn, $category) . "',$p,$c,'" . mysqli_real_escape_string($conn, $file_hash) . "')";
            }
            else {
                $prov = $province_name !== null ? "'" . mysqli_real_escape_string($conn, $province_name) . "'" : 'NULL';
                $cty = $city_name !== null ? "'" . mysqli_real_escape_string($conn, $city_name) . "'" : 'NULL';
                $sql = "INSERT INTO logos (title, file_name, category, province, city) VALUES ('" . mysqli_real_escape_string($conn, $title) . "','" . mysqli_real_escape_string($conn, $file) . "','" . mysqli_real_escape_string($conn, $category) . "',$prov,$cty)";
            }

            mysqli_query($conn, $sql);
            $_SESSION['flash_msg'] = 'Logo berhasil diunggah!';
            $_SESSION['flash_type'] = 'success';
            $_SESSION['flash_title'] = 'Berhasil';
        }
    }

    header('Location: kelola-logo.php');
    exit();
}

// ----------------
// Handle edit/update (modal)
if (isset($_POST['update_logo'])) {
    $id = (int)$_POST['edit_id'];
    $title = mysqli_real_escape_string($conn, $_POST['edit_title']);
    $category = mysqli_real_escape_string($conn, $_POST['edit_category']);

    $q = mysqli_query($conn, "SELECT file_name FROM logos WHERE id=$id");
    $data = mysqli_fetch_assoc($q);
    $file = $data['file_name'];

    if (!empty($_FILES['edit_logo']['name'])) {
        $ext = pathinfo($_FILES['edit_logo']['name'], PATHINFO_EXTENSION);
        $new_file = uniqid('logo_') . "." . $ext;
        $tmp = $_FILES['edit_logo']['tmp_name'];
        $target = __DIR__ . '/../uploads/logos/' . $new_file;
        if (move_uploaded_file($tmp, $target)) {
            @unlink(__DIR__ . '/../uploads/logos/' . $file);
            $file = $new_file;
        }
    }
    mysqli_query($conn, "UPDATE logos SET title='" . mysqli_real_escape_string($conn, $title) . "', category='" . mysqli_real_escape_string($conn, $category) . "', file_name='" . mysqli_real_escape_string($conn, $file) . "' WHERE id=$id");
    $_SESSION['flash_msg'] = 'Logo berhasil diperbarui!';
    $_SESSION['flash_type'] = 'success';
    $_SESSION['flash_title'] = 'Berhasil';
    header("Location: kelola-logo.php");
    exit();
}

// ----------------
// Filters & Pagination: get params
// ----------------
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';
$filter_category = isset($_GET['category']) ? mysqli_real_escape_string($conn, trim($_GET['category'])) : '';

// detect created_at column presence
$has_created_at = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM logos LIKE 'created_at'")) > 0;

// build WHERE
$where = [];
$filter_province = $_GET['province'] ?? '';
$filter_city = $_GET['city'] ?? '';
if ($filter_province != '') {    $where[] = "province_id='" . intval($filter_province) . "'";
}

if ($filter_city != '') {    $where[] = "city_id='" . intval($filter_city) . "'";
}
if ($search !== '') {
    $where[] = "title LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}
if ($filter_category !== '') {
    $where[] = "category = '" . mysqli_real_escape_string($conn, $filter_category) . "'";
}
$where_sql = count($where) ? "WHERE " . implode(' AND ', $where) : "";

// count total matching
$count_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM logos $where_sql");
$rowc = mysqli_fetch_assoc($count_q);
$total_items = (int)$rowc['total'];

$stat_total = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) total FROM logos
"))['total'];

$stat_pemerintahan = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) total FROM logos WHERE category='Pemerintahan'
"))['total'];

$stat_sekolah = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) total FROM logos WHERE category='Sekolah'
"))['total'];

$stat_pt = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) total FROM logos WHERE category='Perguruan Tinggi'
"))['total'];

$stat_org = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT COUNT(*) total FROM logos WHERE category='Organisasi'
"))['total'];

// pagination calc
$total_pages = max(1, (int)ceil($total_items / $per_page));
if ($page < 1)
    $page = 1;
if ($page > $total_pages)
    $page = $total_pages;
$offset = ($page - 1) * $per_page;

// query list
$order_by = $has_created_at ? "created_at DESC" : "id DESC";
$res_q = mysqli_query($conn, "SELECT * FROM logos $where_sql ORDER BY $order_by LIMIT $offset, $per_page");

// get categories distinct for filter UI
$cats_res = mysqli_query($conn, "SELECT DISTINCT category FROM logos ORDER BY category ASC");

// get provinces for upload form
$prov_res = mysqli_query($conn, "SELECT * FROM provinces ORDER BY name ASC");

?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Logo</title>
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
    <aside class="w-48 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col fixed h-full transition-colors duration-300">
        <div class="p-6 flex items-center gap-3">
                <img src="https://turikaleprint.store/public/assets/images/Logo Biru-TurikalePrint.png" alt="Turikale Print" class="h-10 w-auto" />
            </div>
        <nav class="flex-1 px-4 space-y-1 mt-4">
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group" href="dashboard.php">
                    <span class="material-symbols-outlined text-[22px]">dashboard</span>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary group"
                    href="#">
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
    <main class="flex-1 p-5 ml-48">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-bold">
                        Kelola Logo
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400">
                        Pusat pengelolaan aset logo.
                    </p>
                </div>
            </div>

       

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">

<div class="bg-white p-4 rounded-xl border">
<p class="text-xs text-slate-500">Total Logo</p>
<p class="text-2xl font-bold"><?php echo $stat_total; ?></p>
</div>

<div class="bg-white p-4 rounded-xl border">
<p class="text-xs text-slate-500">Pemerintahan</p>
<p class="text-2xl font-bold text-red-500"><?php echo $stat_pemerintahan; ?></p>
</div>

<div class="bg-white p-4 rounded-xl border">
<p class="text-xs text-slate-500">Sekolah</p>
<p class="text-2xl font-bold text-orange-500"><?php echo $stat_sekolah; ?></p>
</div>

<div class="bg-white p-4 rounded-xl border">
<p class="text-xs text-slate-500">Perguruan Tinggi</p>
<p class="text-2xl font-bold text-green-600"><?php echo $stat_pt; ?></p>
</div>

<div class="bg-white p-4 rounded-xl border">
<p class="text-xs text-slate-500">Organisasi</p>
<p class="text-2xl font-bold text-blue-500"><?php echo $stat_org; ?></p>
</div>

</div>

        <section class="mb-4">
            <form method="GET" class="flex gap-3 items-center flex-wrap">
                <input name="q"
value="<?php echo htmlspecialchars($search); ?>"
placeholder="Cari nama logo..."
class="px-3 py-2 rounded-lg border w-64"/>

<select name="category" class="px-3 py-2 rounded-lg border">
<option value="">Semua Kategori</option>
<?php while ($c = mysqli_fetch_assoc($cats_res)) { ?>
<option value="<?php echo htmlspecialchars($c['category']); ?>"
<?php if ($filter_category == $c['category'])
        echo 'selected'; ?>>
<?php echo htmlspecialchars($c['category']); ?>
</option>
<?php
}?>
</select>

<select name="province" id="filter_province"
class="px-3 py-2 rounded-lg border">

<option value="">Semua Provinsi</option>

<?php
$provs = mysqli_query($conn, "SELECT * FROM provinces ORDER BY name ASC");

while ($p = mysqli_fetch_assoc($provs)) {    $selected = ($filter_province == $p['id']) ? "selected" : "";
?>

<option value="<?php echo $p['id']; ?>" <?php echo $selected; ?>>
<?php echo $p['name']; ?>
</option>

<?php
}?>

</select>

<select name="city" id="filter_city"
class="px-3 py-2 rounded-lg border">

<option value="">Semua Kabupaten</option>

</select>

<button class="px-4 py-2 bg-primary text-white rounded">
Cari
</button>
            </form>
        </section>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2">
                <div class="bg-white rounded-xl overflow-hidden border">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/50">
                                    <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase">
                                        Preview
                                    </th>
                                    <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase">
                                        Nama Logo
                                    </th>
                                    <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase">
                                        Kategori
                                    </th>
                                    <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase">
                                        Waktu Upload
                                    </th>

                                    <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase ">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($res_q)) {
    $img = 'https://turikaleprint.store/uploads/logos/' . $row['file_name'];
?>
                                <tr class="border-t">
                                    <td class="px-3 py-2">
                                        <div class="w-10 h-10 bg-slate-100 flex items-center justify-center rounded overflow-hidden">
                                            <img src="<?php echo htmlspecialchars($img); ?>" class="object-contain max-w-full max-h-full" onclick="openPreview('<?php echo $img; ?>')" alt="">
                                        </div>
                                    </td>
                                     <!-- Nama logo-->
                                    <td class="px-3 py-2 font-medium text-xs"><?php echo htmlspecialchars($row['title']); ?>
                                    </td>
                                    <!-- Kategori-->
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-1 text-xs rounded-full bg-slate-100"><?php echo htmlspecialchars($row['category']); ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-slate-500 ">
                                        <?php
    if ($has_created_at && !empty($row['created_at'])) {
        echo date('d M Y H:i', strtotime($row['created_at']));
    }
    else {
        echo '-';
    }
?>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="inline-flex gap-2">
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
                                            <a href="#" onclick="event.preventDefault(); showModal('Konfirmasi Hapus', 'Hapus logo ini?', 'warning', () => { window.location='kelola-logo.php?delete=<?php echo $row['id']; ?>'; }, true)" class="px-2 py-1 text-red-600 rounded hover:bg-red-50"><button class="p-1.5 text-red-500 hover:bg-red-50 rounded">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
}?>
                                <?php if (mysqli_num_rows($res_q) == 0) { ?>
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-slate-500">Tidak ada data</td>
                                </tr>
                                <?php
}?>
                            </tbody>
                        </table>
                    </div>

                    <!-- pagination -->
                    <div class="px-6 py-4 bg-slate-50 flex items-center justify-between">
                        <p class="text-xs text-slate-500">Menampilkan <?php echo min($offset + 1, $total_items); ?> - <?php echo min($offset + $per_page, $total_items); ?> dari <?php echo $total_items; ?> logo</p>
                        <div class="flex gap-2 items-center">
                            <?php
$base_url = 'kelola-logo.php?';
if ($search !== '')
    $base_url .= 'q=' . urlencode($search) . '&';
if ($filter_category !== '')
    $base_url .= 'category=' . urlencode($filter_category) . '&';
if ($filter_province !== '')
    $base_url .= 'province=' . urlencode($filter_province) . '&';
if ($filter_city !== '')
    $base_url .= 'city=' . urlencode($filter_city) . '&';
// previous
if ($page > 1) {
    echo '<a href="' . $base_url . 'page=' . ($page - 1) . '" class="px-3 py-1 border rounded">Prev</a>';
}
else {
    echo '<span class="px-3 py-1 border rounded text-slate-400">Prev</span>';
}
// show pages (simple)
$start = max(1, $page - 3);
$end = min($total_pages, $page + 3);
for ($i = $start; $i <= $end; $i++) {
    if ($i == $page) {
        echo '<span class="px-3 py-1 bg-primary text-white rounded">' . $i . '</span>';
    }
    else {
        echo '<a href="' . $base_url . 'page=' . $i . '" class="px-3 py-1 border rounded">' . $i . '</a>';
    }
}
// next
if ($page < $total_pages) {
    echo '<a href="' . $base_url . 'page=' . ($page + 1) . '" class="px-3 py-1 border rounded">Next</a>';
}
else {
    echo '<span class="px-3 py-1 border rounded text-slate-400">Next</span>';
}
?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Upload form (kanan) -->
            <div
                    class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <h3 class="font-bold text-xl mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">cloud_upload</span>
                        Tambah Logo Baru
                    </h3>
                    <form id="uploadForm" method="POST" enctype="multipart/form-data" class="space-y-4">
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
    echo '<option value="' . (int)$p['id'] . '">' . htmlspecialchars($p['name']) . '</option>';
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

        </div>
    </main>
</div>

<!-- Edit modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-lg">
        <h3 class="text-lg font-bold mb-4">Edit Logo</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="mb-3">
                <label class="text-sm font-semibold">Nama Logo</label>
                <input type="text" name="edit_title" id="edit_title" class="w-full border rounded p-2">
            </div>
            <div class="mb-3">
                <label class="text-sm font-semibold">Kategori</label>
                <select name="edit_category" id="edit_category" class="w-full border rounded p-2">
                    <option>Sekolah</option>
                    <option>Pemerintahan</option>
                    <option>Perguruan Tinggi</option>
                    <option>Organisasi</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="text-sm font-semibold">Ganti Logo (optional)</label>
                <input type="file" name="edit_logo" accept="image/png">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 rounded">Batal</button>
                <button name="update_logo" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="logoPreviewModal"
onclick="closePreview()"
class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">

<div class="relative">

<button
onclick="closePreview()"
class="absolute -top-10 right-0 text-white text-3xl">
✕
</button>

<img id="previewLogoImage"
class="max-h-[80vh] max-w-[80vw] rounded-lg shadow-xl">

</div>

</div>

 <script>
        function previewLogo(event) {

            const file = event.target.files[0];

            if (!file) return;

            if (file.size > 1024 * 1024) {
                showModal("Gagal", "Ukuran file maksimal 1MB", "error");
                event.target.value = "";
                return;
            }

            if (file.type !== "image/png") {
                showModal("Gagal", "Hanya file PNG yang diperbolehkan", "error");
                event.target.value = "";
                return;
            }

        }
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function(){

const dropArea = document.getElementById("drop-area")
const input = document.getElementById("logoInput")
const preview = document.getElementById("previewImage")

dropArea.addEventListener("click",()=>input.click())

input.addEventListener("change",function(){
previewFile(this.files[0])
})

dropArea.addEventListener("dragover",function(e){
e.preventDefault()
dropArea.classList.add("border-primary")
})

dropArea.addEventListener("dragleave",function(){
dropArea.classList.remove("border-primary")
})

dropArea.addEventListener("drop",function(e){

e.preventDefault()
dropArea.classList.remove("border-primary")

const file=e.dataTransfer.files[0]
input.files=e.dataTransfer.files

previewFile(file)

})

function previewFile(file){

if(!file) return

if(file.size>1024*1024){
showModal("Gagal", "Ukuran maksimal 1MB", "error");
return;
}

if(file.type!=="image/png"){
showModal("Gagal", "File harus PNG", "error");
return;
}

const reader=new FileReader()

reader.onload=function(e){

preview.src=e.target.result
preview.classList.remove("hidden")

}

reader.readAsDataURL(file)

}

document.getElementById("uploadForm").addEventListener("submit",function(e){

let category=document.querySelector("select[name='category']").value
let city=document.getElementById("city_select").value

if(category!=="Pemerintahan" && city===""){
showModal("Peringatan", "Kabupaten/Kota wajib dipilih", "warning");
e.preventDefault()
}

})

})
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
document.getElementById("filter_province").addEventListener("change",function(){

let province_id=this.value

fetch('api/get-cities.php?province_id='+province_id)

.then(res=>res.json())

.then(data=>{
    let html = '<option value="">Semua Kabupaten</option>';
    data.forEach(city => {
        html += `<option value="${city.id}">${city.name}</option>`;
    });
    document.getElementById("filter_city").innerHTML=html;
})

})

</script>
<script>
    const selectedProvince = "<?php echo $filter_province; ?>"
const selectedCity = "<?php echo $filter_city; ?>"

if(selectedProvince){

fetch('api/get-cities.php?province_id='+selectedProvince)

.then(res=>res.json())

.then(data=>{

    let html = '<option value="">Semua Kabupaten</option>';
    data.forEach(city => {
        html += `<option value="${city.id}">${city.name}</option>`;
    });

document.getElementById("filter_city").innerHTML=html;

if(selectedCity){

document.getElementById("filter_city").value=selectedCity;

}

})

}
</script>

<script>
    function openPreview(src){

const modal = document.getElementById("logoPreviewModal")
const img = document.getElementById("previewLogoImage")

img.src = src

modal.classList.remove("hidden")
modal.classList.add("flex")

}

function closePreview(){

const modal = document.getElementById("logoPreviewModal")

modal.classList.remove("flex")
modal.classList.add("hidden")

}
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