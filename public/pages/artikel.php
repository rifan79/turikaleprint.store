<?php

require_once "../../config/database.php";

$limit = 9;
$page = $_GET['page'] ?? 1;
$category_slug = $_GET['category'] ?? '';
$search = $_GET['q'] ?? '';

$start = ($page - 1) * $limit;

$where = "WHERE a.status = 'published'";

if ($category_slug) {
    $where .= " AND c.slug = '$category_slug'";
}

if ($search) {
    $where .= " AND (
        a.title LIKE '%$search%' 
        OR a.excerpt LIKE '%$search%'
        OR a.content LIKE '%$search%'
    )";
}

$query = "
SELECT 
    a.id,
    a.title,
    a.slug,
    a.excerpt,
    a.thumbnail,
    a.created_at,
    c.name AS category_name,
    c.slug AS category_slug
FROM articles a
LEFT JOIN article_categories c 
ON a.category_id = c.id
$where
ORDER BY a.created_at DESC
LIMIT $start, $limit
";

$result = $conn->query($query);
$articles = $result->fetch_all(MYSQLI_ASSOC);
$found = count($articles);

$total_query = $conn->query("
SELECT COUNT(*) as total
FROM articles a
LEFT JOIN article_categories c 
ON a.category_id = c.id
$where
");

$total_articles = $total_query->fetch_assoc()['total'];
$total_pages = ceil($total_articles / $limit);

?>

<!doctype html>

<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries,line-clamp"></script>
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
      rel="stylesheet"
    />
    <link
      rel="icon"
      type="image/x-icon"
      href="../Logo-Icon-TurikalePrint.jpg"
    />
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              primary: "#136dec",
              "accent-green": "#10b981",
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
    <title>Turikale Print - Artikel</title>
  </head>
  <body
    class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100"
  >
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
      <header
        class="sticky top-0 z-50 w-full border-b border-slate-200 dark:border-slate-800 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md"
      >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 items-center justify-between">
            <a href="/" class="flex items-center">
              <img
                src="../assets/images/Logo Biru-TurikalePrint.png"
                alt="Turikale Print"
                class="h-11 w-auto"
              />
            </a>
            <nav class="hidden md:flex items-center gap-8">
              <a
                class="text-sm font-medium hover:text-primary transition-colors"
                href="../../index.html"
                >Beranda</a
              >
              <a
                class="text-sm font-medium hover:text-primary transition-colors"
                href="/public/pages/profil.html"
                >Profil</a
              >
              <a
                class="text-sm font-medium hover:text-primary transition-colors"
                href="/public/pages/aset-logo.php"
                >Aset Logo</a
              >
              <a
                class="text-sm font-bold text-primary border-b-2 border-primary"
                href="#"
                >Artikel</a
              >
            </nav>

            <button
              id="menu-toggle"
              class="md:hidden text-slate-800 dark:text-white focus:outline-none transition-transform duration-300"
            >
              <span id="menu-icon" class="material-symbols-outlined text-3xl">
                menu
              </span>
            </button>
          </div>

          <div class="relative md:hidden">
            <div
              id="mobile-menu"
              class="absolute right-0 mt-3 w-64 origin-top transform scale-y-0 opacity-0 transition-all duration-300 ease-in-out bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col gap-5"
            >
              <a
                class="text-base font-medium hover:text-primary transition-colors"
                href="../../index.html"
                >Beranda</a
              >
              <a
                class="text-base font-medium hover:text-primary transition-colors"
                href="/public/pages/profil.html"
                >Profil</a
              >
              <a
                class="text-base font-medium hover:text-primary transition-colors"
                href="/public/pages/aset-logo.php"
                >Aset Logo</a
              >
              <a
                class="text-base font-semibold hover:text-primary transition-colors"
                href="#"
                >Artikel</a
              >
            </div>
          </div>
        </div>
      </header>
      <main class="flex-grow">
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 lg:py-12">
          <!-- Hero Header -->
          <div
            class="flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6 mb-6 sm:mb-8"
          >
            <div class="max-w-2xl">
              <span
                class="inline-block px-2.5 py-1 bg-accent-green/10 text-accent-green text-[10px] sm:text-xs font-bold rounded-full mb-2 sm:mb-3 uppercase tracking-wider"
                >Pusat Informasi</span
              >
              <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black tracking-tight mb-2 sm:mb-4">
                Wawasan &amp; Kabar Percetakan
              </h1>
              <p class="text-sm sm:text-base lg:text-lg text-slate-600 dark:text-slate-400">
                Tips profesional dan pembaruan terbaru dari tim ahli Turikale
                Print untuk hasil cetak maksimal.
              </p>
            </div>
          </div>

          <!-- Search & Filters -->
          <form method="GET" class="mb-6 sm:mb-8">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category_slug) ?>">
            <div class="flex gap-2 w-full sm:max-w-md">
              <input
                type="text"
                name="q"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari artikel..."
                class="flex-1 border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white rounded-lg px-3 sm:px-4 py-2 text-sm sm:text-base focus:ring-primary focus:border-primary"
              />
              <button
                type="submit"
                class="bg-primary text-white px-4 sm:px-5 py-2 rounded-lg text-sm sm:text-base font-semibold hover:bg-primary/90 transition-colors"
              >
                Cari
              </button>
            </div>
          </form>

          <?php

$categories = $conn->query("
SELECT name, slug 
FROM article_categories
");

?>

          <div class="flex gap-2 sm:gap-3 mb-6 sm:mb-10 flex-wrap">
            <a href="artikel.php"
              class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full border text-xs sm:text-sm font-medium transition-colors
              <?= $category_slug == '' ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-slate-800 dark:text-slate-300 hover:border-primary hover:text-primary' ?>">
              Semua
            </a>

            <?php while($cat = $categories->fetch_assoc()): ?>
            <a href="artikel.php?category=<?= $cat['slug'] ?>"
              class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full border text-xs sm:text-sm font-medium transition-colors
              <?= $category_slug == $cat['slug'] ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-slate-800 dark:text-slate-300 hover:border-primary hover:text-primary' ?>">
              <?= htmlspecialchars($cat['name']) ?>
            </a>
            <?php endwhile; ?>
          </div>

          <!-- Section Title -->
          <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-6 sm:mb-8 lg:mb-10">
            <?php
            if ($category_slug) {
                echo "Artikel Kategori: " . htmlspecialchars($category_slug);
            } else {
                echo "Artikel & Tips Percetakan";
            }
            ?>
          </h2>

          <!-- Article Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
            <?php foreach($articles as $article): ?>
            <a href="/artikel/<?= $article['slug'] ?>"
              class="group bg-white dark:bg-slate-900 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all">

              <div class="aspect-[16/10] overflow-hidden">
                <img
                  src="/uploads/articles/thumbnails/<?= htmlspecialchars($article['thumbnail']) ?>"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  alt="<?= htmlspecialchars($article['title']) ?>"
                >
              </div>

              <div class="p-3 sm:p-4 lg:p-5">
                <span class="text-[10px] sm:text-xs font-bold text-primary uppercase">
                  <?= htmlspecialchars($article['category_name']) ?>
                </span>

                <h3 class="text-sm sm:text-base lg:text-lg font-bold mt-1.5 sm:mt-2 mb-1.5 sm:mb-2 line-clamp-2 text-slate-900 dark:text-white">
                  <?= htmlspecialchars($article['title']) ?>
                </h3>

                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 line-clamp-2 sm:line-clamp-3">
                  <?= htmlspecialchars($article['excerpt']) ?>
                </p>

                <div class="text-[10px] sm:text-xs text-slate-400 mt-3 sm:mt-4">
                  <?= date('d M Y', strtotime($article['created_at'])) ?>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <div class="flex justify-center mt-8 sm:mt-12 gap-1.5 sm:gap-2">
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a 
              href="?page=<?= $i ?>&category=<?= $category_slug ?>&q=<?= urlencode($search) ?>"
              class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg border text-sm font-medium transition-colors
              <?= ($i == $page) ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-slate-800 dark:text-slate-300 hover:border-primary hover:text-primary' ?>">
              <?= $i ?>
            </a>
            <?php endfor; ?>
          </div>
          
        </section>
      </main>

      <footer
        class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-12 px-4 lg:px-20"
      >
        <div class="max-w-7xl mx-auto">
          <div class="grid grid-cols-3 md:grid-cols-4 gap-4 md:gap-12 mb-12">
            <div class="col-span-3 md:col-span-2">
              <img
                src="../assets/images/Logo Biru-TurikalePrint.png"
                alt="Turikale Print"
                class="h-10 w-auto"
              />
              <p class="text-slate-500 max-w-sm mb-6">
                <br />Partner terpercaya untuk segala kebutuhan bisnis dan
                personal anda. Cepat, berkualitas, dan terjangkau.
              </p>
              <div class="flex items-center gap-4">
                <a
                  class="size-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-primary hover:text-white transition-colors"
                  href="https://www.tiktok.com/@turikale_digitalprinting?is_from_webapp=1&sender_device=pc"
                  target="_blank"
                  rel="noopener noreferrer "
                >
                  <svg
                    fill="#000000"
                    viewBox="0 0 14 14"
                    role="img"
                    focusable="false"
                    aria-hidden="true"
                    xmlns="https://www.w3.org/2000/svg"
                  >
                    <path
                      d="m 4.9520184,12.936803 c -1.12784,-0.2039 -2.19411,-0.9875 -2.66789,-1.9606 -0.32895,-0.6757 -0.45541,-1.3901003 -0.37418,-2.1137003 0.15054,-1.3412 0.84482,-2.4395 1.92406,-3.0439 0.56899,-0.3186 1.38421,-0.4769 1.991,-0.3867 l 0.35091,0.052 9e-5,1.0725 9e-5,1.0725 -0.332,-0.014 c -0.79998,-0.033 -1.39595,0.3096 -1.70379,0.9784 -0.1473,0.32 -0.18461,0.8887 -0.081,1.2351 0.12773,0.4273003 0.50542,0.8132003 0.96145,0.9825003 0.15535,0.058 0.32344,0.08 0.61152,0.079 0.35862,-4e-4 0.42448,-0.013 0.67663,-0.1323 0.36505,-0.1726 0.63141,-0.4231 0.78797,-0.7411 0.10147,-0.2061003 0.13414,-0.3430003 0.16587,-0.6951003 0.0217,-0.2412 0.0401,-2.2122 0.0409,-4.38 l 10e-4,-3.94149998 0.68371,0 c 0.37605,0 0.8277,0.012 1.00368,0.027 l 0.31995,0.027 0,0.1584 c 0,0.3813 0.22299,1.1127 0.45156,1.4812 0.0571,0.092 0.2564996,0.3178 0.4431796,0.5018 0.36068,0.3555 0.66494,0.5352 1.13352,0.6692 0.138,0.04 0.28359,0.089 0.32353,0.109 0.0399,0.02 0.11522,0.038 0.16728,0.038 0.0521,4e-4 0.13701,0.012 0.18876,0.026 l 0.0941,0.025 0,0.9948 0,0.9948 -0.17773,-0.019 c -0.9611,-0.1037 -1.72925,-0.3601 -2.3423096,-0.782 -0.30468,-0.2096 -0.33102,-0.222 -0.30218,-0.1422 0.0104,0.029 0.003,1.1249 -0.0164,2.436 -0.0336,2.2728 -0.0396,2.3992 -0.12781,2.7173003 -0.33904,1.2222 -1.09994,2.1297 -2.10183,2.5068 -0.6126,0.2306 -1.39679,0.2932 -2.09405,0.1671 z"
                    />
                  </svg>
                </a>
                <a
                  class="size-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-primary hover:text-white transition-colors"
                  href="https://www.instagram.com/turikale.print?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <svg
                    fill="#000000"
                    viewBox="0 0 24 24"
                    xmlns="https://www.w3.org/2000/svg"
                    data-name="Layer 1"
                  >
                    <path
                      d="M17.34,5.46h0a1.2,1.2,0,1,0,1.2,1.2A1.2,1.2,0,0,0,17.34,5.46Zm4.6,2.42a7.59,7.59,0,0,0-.46-2.43,4.94,4.94,0,0,0-1.16-1.77,4.7,4.7,0,0,0-1.77-1.15,7.3,7.3,0,0,0-2.43-.47C15.06,2,14.72,2,12,2s-3.06,0-4.12.06a7.3,7.3,0,0,0-2.43.47A4.78,4.78,0,0,0,3.68,3.68,4.7,4.7,0,0,0,2.53,5.45a7.3,7.3,0,0,0-.47,2.43C2,8.94,2,9.28,2,12s0,3.06.06,4.12a7.3,7.3,0,0,0,.47,2.43,4.7,4.7,0,0,0,1.15,1.77,4.78,4.78,0,0,0,1.77,1.15,7.3,7.3,0,0,0,2.43.47C8.94,22,9.28,22,12,22s3.06,0,4.12-.06a7.3,7.3,0,0,0,2.43-.47,4.7,4.7,0,0,0,1.77-1.15,4.85,4.85,0,0,0,1.16-1.77,7.59,7.59,0,0,0,.46-2.43c0-1.06.06-1.4.06-4.12S22,8.94,21.94,7.88ZM20.14,16a5.61,5.61,0,0,1-.34,1.86,3.06,3.06,0,0,1-.75,1.15,3.19,3.19,0,0,1-1.15.75,5.61,5.61,0,0,1-1.86.34c-1,.05-1.37.06-4,.06s-3,0-4-.06A5.73,5.73,0,0,1,6.1,19.8,3.27,3.27,0,0,1,5,19.05a3,3,0,0,1-.74-1.15A5.54,5.54,0,0,1,3.86,16c0-1-.06-1.37-.06-4s0-3,.06-4A5.54,5.54,0,0,1,4.21,6.1,3,3,0,0,1,5,5,3.14,3.14,0,0,1,6.1,4.2,5.73,5.73,0,0,1,8,3.86c1,0,1.37-.06,4-.06s3,0,4,.06a5.61,5.61,0,0,1,1.86.34A3.06,3.06,0,0,1,19.05,5,3.06,3.06,0,0,1,19.8,6.1,5.61,5.61,0,0,1,20.14,8c.05,1,.06,1.37.06,4S20.19,15,20.14,16ZM12,6.87A5.13,5.13,0,1,0,17.14,12,5.12,5.12,0,0,0,12,6.87Zm0,8.46A3.33,3.33,0,1,1,15.33,12,3.33,3.33,0,0,1,12,15.33Z"
                    />
                  </svg>
                </a>
              </div>
            </div>
            <div>
              <h4 class="font-bold text-xs sm:text-sm md:text-base mb-3 md:mb-6">Layanan</h4>
              <ul class="space-y-2 md:space-y-4 text-xs sm:text-sm text-slate-500 transition-colors">
                <li>Spanduk</li>
                <li>Stiker</li>
                <li>Kartu Nama</li>
                <li>Stempel</li>
                <li>Banner</li>
                <li>Outdoor Media</li>
              </ul>
            </div>
            <div>
              <h4 class="font-bold text-xs sm:text-sm md:text-base mb-3 md:mb-6">Bantuan</h4>
              <ul class="space-y-2 md:space-y-4 text-xs sm:text-sm text-slate-500">
                <li>
                  <a
                    class="hover:text-primary transition-colors"
                    href="https://wa.me/6282188579988?text=Halo%20saya%20ingin%20bertanya"
                    target="_blank"
                    rel="noopener noreferrer"
                    >Hubungi Kami</a
                  >
                </li>
              </ul>
            </div>
          </div>
          <div
            class="pt-5 border-t border-slate-500 text-center text-sm text-slate-500"
          >
            <p>© 2026 Turikale Print.</p>
          </div>
        </div>
      </footer>
    </div>
    <!-- Hidden Layout: Article Preview (For reference/implementation) -->
    <!-- This would normally be on its own page, but included here as per requested single page view components -->
    <div
      class="hidden fixed inset-0 z-[100] bg-white dark:bg-background-dark overflow-y-auto"
    >
      <div class="max-w-4xl mx-auto px-4 py-8">
        <a href="../pages/artikel.php"
          ><button
            class="flex items-center gap-2 text-primary font-bold mb-8 hover:translate-x-[-4px] transition-transform"
          >
            <span class="material-symbols-outlined">arrow_back</span> Kembali ke
            Artikel
          </button></a
        >

        <article>
          <div class="flex items-center gap-4 text-sm text-slate-500 mb-4">
            <span
              class="px-2 py-1 bg-primary/10 text-primary rounded font-bold text-[10px]"
              >TIPS CETAK</span
            >
            <span>15 Mei 2024</span>
            <span>•</span>
            <span>Oleh Tim Ahli Turikale</span>
          </div>
          <h1 class="text-3xl md:text-5xl font-black mb-8 leading-tight">
            5 Tips untuk Hasil Cetak Offset Berkualitas Tinggi
          </h1>
          <div class="aspect-[21/9] rounded-2xl overflow-hidden mb-12">
            <img
              class="w-full h-full object-cover"
              data-alt="High quality professional offset printing machinery"
              src="https://lh3.googleusercontent.com/aida-public/AB6AXuC9-R7iyH0tSmEka_-2wI4-GxOOfjs7AT6VGLioNAukldrcWsPav7OqU9UYDzj95cJrV4kcxlr46QjBD2t0h994yJIRcZusDQU9qGcbvmuABJsoelwoJ3dgl5Jn8CQIKB48Q70IEjfG3Klkg2P3R1R5rtKSktGmCcVqZWeiRx8P6YmuFLRSQtOlZlgSiKgFRYoS55Gqw8kiUllHtV1DBr1_1i_5nWG8AEJvBydo24swcMzq_NlI48G6k_zks1rouZhMqeHItG2cxUKg"
            />
          </div>
          <div class="prose prose-slate dark:prose-invert max-w-none">
            <p
              class="text-lg leading-relaxed mb-6 text-slate-700 dark:text-slate-300"
            >
              Cetak offset adalah standar emas untuk proyek pencetakan volume
              tinggi. Apakah Anda mencetak brosur, kartu nama, atau katalog
              tahunan perusahaan, mencapai kualitas tertinggi memerlukan
              persiapan yang matang sejak tahap desain.
            </p>
            <h2 class="text-2xl font-bold mb-4">1. Gunakan Ruang Warna CMYK</h2>
            <p class="mb-6 text-slate-700 dark:text-slate-300">
              Pastikan file Anda dalam mode CMYK, bukan RGB. Monitor menampilkan
              warna dalam RGB (Light), sedangkan mesin cetak menggunakan tinta
              CMYK (Pigment). Mengonversi file di akhir dapat mengubah saturasi
              warna secara drastis.
            </p>
            <h2 class="text-2xl font-bold mb-4">
              2. Perhatikan Bleed dan Margin
            </h2>
            <p class="mb-6 text-slate-700 dark:text-slate-300">
              Selalu sertakan setidaknya 3mm area bleed di sekeliling desain
              Anda untuk menghindari garis putih di tepi setelah pemotongan.
            </p>
          </div>
        </article>
      </div>
    </div>
    <script>
      const toggleBtn = document.getElementById("menu-toggle");
      const mobileMenu = document.getElementById("mobile-menu");
      const menuIcon = document.getElementById("menu-icon");

      let isOpen = false;

      toggleBtn.addEventListener("click", () => {
        isOpen = !isOpen;

        if (isOpen) {
          mobileMenu.classList.remove("scale-y-0", "opacity-0");
          mobileMenu.classList.add("scale-y-100", "opacity-100");
          menuIcon.textContent = "close";
        } else {
          mobileMenu.classList.remove("scale-y-100", "opacity-100");
          mobileMenu.classList.add("scale-y-0", "opacity-0");
          menuIcon.textContent = "menu";
        }
      });
    </script>
  </body>
</html>
