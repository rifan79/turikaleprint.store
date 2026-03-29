<?php
session_start();
require_once '../config/database.php';

/* ======================================
   AUTH CHECK
====================================== */
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

/* ======================================
   DELETE ARTICLE
====================================== */
if (isset($_GET['delete_article']) && is_numeric($_GET['delete_article'])) {

    $id = (int)$_GET['delete_article'];

    $q = mysqli_query($conn,"SELECT thumbnail FROM articles WHERE id=$id LIMIT 1");
    $row = mysqli_fetch_assoc($q);

    if($row){
        $thumb = $row['thumbnail'];
        $path = __DIR__ . '/../uploads/articles/thumbnails/' . $thumb;

        if($thumb && file_exists($path)){
            unlink($path);
        }
    }

    mysqli_query($conn,"DELETE FROM articles WHERE id=$id");

    $_SESSION['flash_msg'] = 'Artikel berhasil dihapus!';
    $_SESSION['flash_type'] = 'success';
    $_SESSION['flash_title'] = 'Berhasil';

    header("Location: kelola-artikel.php");
    exit();
}

/* ======================================
   CREATE ARTICLE
====================================== */
if (isset($_POST['upload_article'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
    // content is HTML from TinyMCE - store raw HTML (but still escape when printing)
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = (int) $_POST['category_id'];
    $status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'draft';
    $author = isset($_SESSION['admin']) ? mysqli_real_escape_string($conn,$_POST['author']) : 'Admin';

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
            header("Location: kelola-artikel.php");
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
            header("Location: kelola-artikel.php");
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

    header('Location: kelola-artikel.php');
    exit();
}

/* ======================================
   STATISTICS
====================================== */
$total_articles = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) as total FROM articles")
)['total'];

$total_published = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) as total FROM articles WHERE status='published'")
)['total'];

$total_draft = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) as total FROM articles WHERE status='draft'")
)['total'];

/* ======================================
   CATEGORY LIST
====================================== */
$categories = mysqli_query($conn,"SELECT * FROM article_categories ORDER BY name ASC");

/* ======================================
   SEARCH
====================================== */
$search = '';
if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn,$_GET['search']);
}

$where = '';
if($search!=''){
    $where = "WHERE articles.title LIKE '%$search%' OR articles.author LIKE '%$search%'";
}

/* ======================================
   PAGINATION
====================================== */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){ $page = 1; }

$start = ($page-1) * $limit;

/* ======================================
   ARTICLE LIST
====================================== */
$articles = mysqli_query($conn,"
SELECT articles.*, article_categories.name AS category_name
FROM articles
LEFT JOIN article_categories
ON articles.category_id = article_categories.id
$where
ORDER BY articles.id DESC
LIMIT $start,$limit
");

$showing = mysqli_num_rows($articles);

/* ======================================
   TOTAL FILTERED
====================================== */
$total_filtered = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) as total FROM articles $where")
)['total'];

$total_pages = ceil($total_filtered / $limit);
?>



<!doctype html>

<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
      rel="stylesheet"
    />
    <link
      rel="icon"
      type="image/x-icon"
      href="https://turikaleprint.store/public/Logo-Icon-TurikalePrint.jpg"
    />
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              primary: "#136dec",
              "background-light": "#f6f7f8",
              "background-dark": "#101822",
            },
            fontFamily: {
              display: ["Inter"],
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
    <title>Kelola Artikel</title>
  </head>
  <body
    class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100"
  >
    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <aside class="w-48 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col fixed h-full transition-colors duration-300">
        <div class="p-6 flex items-center gap-3">
                <img src="https://turikaleprint.store/public/assets/images/Logo Biru-TurikalePrint.png" alt="Turikale Print" class="h-10 w-auto" />
            </div>
        <nav class="flex-1 px-4 space-y-1 mt-4">
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group" href="dashboard.php">
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
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary group"
                    href="#">
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

              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-bold">
                        Kelola Artikel
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400">
                        Manajemen konten dan publikasi artikel blog untuk optimasi SEO.
                    </p>
                </div>
            </div>

          
          <!-- Stats/Filter Row (Optional enhancement) -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div
              class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 flex items-center gap-4"
            >
              <div
                class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600"
              >
                <span class="material-symbols-outlined">description</span>
              </div>
              <div>
                <p class="text-slate-500 text-sm">Total Artikel</p>
                <p class="text-2xl font-bold">
              <?php echo $total_articles; ?>
                    </p>
              </div>
            </div>
            <div
              class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 flex items-center gap-4"
            >
              <div
                class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600"
              >
                <span class="material-symbols-outlined">check_circle</span>
              </div>
              <div>
                <p class="text-slate-500 text-sm">Diterbitkan</p>
                <p class="text-2xl font-bold">
                  <?php echo $total_published; ?>
                  </p>
              </div>
            </div>
            <div
              class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 flex items-center gap-4"
            >
              <div
                class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600"
              >
                <span class="material-symbols-outlined">edit_document</span>
              </div>
              <div>
                <p class="text-slate-500 text-sm">Draft</p>
                <p class="text-2xl font-bold">
                        <?php echo $total_draft; ?>
                      </p>
              </div>
            </div>
          </div>
          <!-- Table Container -->
          <div
            class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
          >
            <div
              class="p-6 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-4 justify-between items-center"
            >
              <form method="GET" class="relative w-full sm:w-80">

                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input name="search" value="<?php echo htmlspecialchars($search); ?>" class="w-full pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg dark:bg-slate-800" placeholder="Cari judul atau penulis..." type="text" /> 
              </form>
              <div class="flex gap-2 w-full sm:w-auto">
                <button
                  class="flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                >
                  <span class="material-symbols-outlined text-sm"
                    >filter_list</span
                  >
                  Filter
                </button>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead>
                  <tr
                    class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs font-bold uppercase tracking-wider"
                  >
                    <th class="px-6 py-4">Gambar</th>
                    <th class="px-6 py-4">Judul Artikel</th>
                    <th class="px-6 py-4">Penulis</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                  <?php while($art = mysqli_fetch_assoc($articles)) { ?>
                  <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                      <div class="w-16 h-10 rounded-md bg-slate-100 overflow-hidden">
                        <?php if(!empty($art['thumbnail'])) { ?>
                        <img src="https://turikaleprint.store/uploads/articles/thumbnails/<?php echo $art['thumbnail']; ?>" class="w-full h-full object-cover">
                        <?php } ?>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <p class="font-semibold text-sm line-clamp-1">
                        <?php echo htmlspecialchars($art['title']); ?>
                      </p>
                      <p class="text-xs text-slate-400"> <?php echo htmlspecialchars($art['category_name']); ?>
                    </p>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <?php echo htmlspecialchars($art['author']); ?>
                  </td>
                  <td class="px-6 py-4">
                    <?php if($art['status'] == 'published') { ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Published</span>
                    <?php } else { ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400">Draft</span>
                    <?php } ?>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-500">
                    <?php echo date("d M Y", strtotime($art['created_at'])); ?>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        
                      <a href="https://turikaleprint.store/artikel/<?= $art['slug'] ?>" target="_blank"
class="p-2 text-slate-400 hover:text-green-600 transition-colors">
                        <span class="material-symbols-outlined text-xl">visibility</span>
                      </a>

<a href="edit-artikel.php?id=<?php echo $art['id']; ?>"
class="p-2 text-slate-400 hover:text-primary transition-colors">

<span class="material-symbols-outlined text-xl">edit</span>

</a>

<a href="#" onclick="event.preventDefault(); showModal('Konfirmasi Hapus', 'Hapus artikel ini?', 'warning', () => { window.location='kelola-artikel.php?delete_article=<?php echo $art['id']; ?>'; }, true)">

<button class="p-2 text-slate-400 hover:text-red-500 transition-colors">

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
            <div
              class="p-6 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between"
            >
              <p class="text-sm text-slate-500">
Menampilkan <?php echo $showing; ?> dari <?php echo $total_filtered; ?> artikel
</p>
              <div class="flex gap-2">

<?php if($page > 1){ ?>

<a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>"

class="px-3 py-1 border rounded-md text-sm">

Sebelumnya

</a>

<?php } ?>

<?php for($i=1;$i<=$total_pages;$i++){ ?>

<a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"

class="px-3 py-1 text-sm rounded-md

<?php echo $i==$page ? 'bg-primary text-white' : 'border'; ?>">

<?php echo $i; ?>

</a>

<?php } ?>

<?php if($page < $total_pages){ ?>

<a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>"

class="px-3 py-1 border rounded-md text-sm">

Selanjutnya

</a>

<?php } ?>

</div>
            </div>
          </div>
          <!-- Editor Section (Form Tulis Artikel Baru) -->
          <div
            class="mt-12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden"
          >
            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
              <h3 class="text-xl font-bold">Tulis Artikel Baru</h3>
              <p class="text-sm text-slate-500">
                Buat konten menarik untuk audiens Anda.
              </p>
            </div>
            <div class="p-8">
              <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                  <!-- Left Column: Fields -->
                  <div class="lg:col-span-2 space-y-6">
                    <div class="space-y-2">
                      <label class="text-sm font-semibold">Judul Artikel</label>
                      <input
                        name="title"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 dark:bg-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-base transition-all"
                        placeholder="Masukkan judul artikel yang menarik..."
                        type="text"
                        required
                      />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold">Ringkasan (Excerpt)</label>

                          <textarea name="excerpt" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800" placeholder="Ringkasan artikel..." rows="3" required></textarea>
                      </div>
                    <div class="space-y-2">
                      <label class="text-sm font-semibold">Isi Konten</label>
                      <!-- Simple Rich Text Editor Mockup -->
                      <div
                        class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden"
                      >
                        
                        <textarea id="tinymce_content"
                          name="content"
                          class="w-full px-4 py-4 dark:bg-slate-900 border-none focus:ring-0 outline-none resize-none min-h-[300px]"
                          placeholder="Tuliskan isi artikel Anda di sini..."
                          rows="12" required
                        ></textarea>
                      </div>
                    </div>
                  </div>
                  <!-- Right Column: Meta & Upload -->
                  <div class="space-y-6">
                    <div class="space-y-2">
                      <label class="text-sm font-semibold"
                        >Upload Gambar Utama</label
                      >
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
            </div>

            </div>
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
        showModal("Gagal", "Ukuran maksimal 2MB", "error");
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

  setup: function(editor) {

      editor.on('change', function () {
          tinymce.triggerSave();
      });

  }
});
</script>
<?php require_once 'modal.php'; ?>
  </body>
</html>
