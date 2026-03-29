<?php

require_once "../../config/database.php";

$slug = $_GET['slug'] ?? null;

if(!$slug){
    header("HTTP/1.0 404 Not Found");
    exit("Artikel tidak ditemukan");
}

$stmt = $conn->prepare("
SELECT a.*, c.name as category_name
FROM articles a
LEFT JOIN article_categories c 
ON a.category_id = c.id
WHERE a.slug = ?
LIMIT 1
");

$stmt->bind_param("s", $slug);
$stmt->execute();

$result = $stmt->get_result();
$article = $result->fetch_assoc();

if(!$article){
    header("HTTP/1.0 404 Not Found");
    exit("Artikel tidak ditemukan");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<title><?= htmlspecialchars($article['title']) ?> | Turikale Print</title>
<meta name="description" content="<?= htmlspecialchars($article['excerpt']) ?>">

<meta property="og:title" content="<?= htmlspecialchars($article['title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($article['excerpt']) ?>">
<meta property="og:image" content="https://turikaleprint.store/uploads/articles/thumbnails/<?= $article['thumbnail'] ?>">
<meta property="og:type" content="article">
<link rel="canonical" href="https://turikaleprint.store/artikel/<?= $article['slug'] ?>">

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
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
      href="../../public/Logo-Icon-TurikalePrint.jpg"
    />
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              primary: "#136dec",
              accent: "#10b981",
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

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100" >

<div
      class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden"
    >
    
    <div class="layout-container flex h-full grow flex-col"> 
    <header
        class="sticky top-0 z-50 w-full border-b border-slate-200 dark:border-slate-800 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md"
      >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 items-center justify-between">
            <a href="/" class="flex items-center">
              <img
                src="/public/assets/images/Logo Biru-TurikalePrint.png"
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
              class="absolute right-0 mt-3 w-64 origin-top transform scale-y-0 opacity-0 transition-all duration-300 ease-in-out bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col gap-5">
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
      
      <main class='flex-1'>
          <div class="max-w-[1000px] mx-auto px-6 py-6">
            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-sm mb-8">
                
                <a class="hover:text-primary" href="../../index.html">Home</a>
              <span class="material-symbols-outlined text-xs"
                >chevron_right</span>
              <a class="hover:text-primary" href="/public/pages/artikel.php">Artikel</a>
              <span class="material-symbols-outlined text-xs"
                >chevron_right</span>
                
                <span class="text-slate-900 dark:text-white font-medium truncate"><?= htmlspecialchars($article['title']) ?>
                </span>
                
                </div>
                
                <article>
                    <header class='mb-10 text-center md:text-left' >
                       <span class="inline-block px-3 py-1 bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider rounded-full mb-4"><?= htmlspecialchars($article['category_name']) ?>
                       </span> 
                        <h1 class="text-3xl md:text-5xl font-bold"><?= htmlspecialchars($article['title']) ?>
                        </h1>
                        
                        <div class="flex flex-wrap items-center gap-4 border-b border-slate-200 dark:border-slate-800 pb-8 pl-4">
                        <div>
                    <p class="text-slate-900 dark:text-white font-semibold">
                    <?= htmlspecialchars($article['author']) ?>
                    </p>
                  
                      <span class="text-slate-500 dark:text-slate-400 text-sm">
                      <?= date('d F Y', strtotime($article['created_at'])) ?>
                      </span>
                  </div>
                  </div>
                  
                  <div class="rounded-2xl overflow-hidden mb-12 shadow-xl aspect-[21/9]">
                      
                      <img class='w-full h-full object-cover' src="/uploads/articles/thumbnails/<?= htmlspecialchars($article['thumbnail']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                      
                    </div>
                    
                    <div class="max-w-[720px] mx-auto prose prose-slate dark:prose-invert lg:prose-lg" ><?= $article['content'] ?>
                    </div>
                        
                    </header>
                </article>
                
                <hr class="my-16 border-slate-200 dark:border-slate-800" />
                
                
           </div>
      </main>
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