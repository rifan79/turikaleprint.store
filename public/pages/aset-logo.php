<?php
require '../../config/database.php';

$limit = 30;
$search = '';
$where = [];

if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = $_GET['search'];
    $where[] = "title LIKE '%$search%'";
}

if (isset($_GET['category'])) {
    $categories = $_GET['category'];
    $catList = "'" . implode("','", $categories) . "'";
    $where[] = "category IN ($catList)";
}

if (isset($_GET['province']) && $_GET['province'] != '') {
    $province = (int) $_GET['province'];
    $where[] = "province_id = $province";
}

if (isset($_GET['city']) && $_GET['city'] != '') {
    $city = (int) $_GET['city'];
    $where[] = "city_id = $city";
}

$whereSQL = '';

if (!empty($where)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$start = ($page - 1) * $limit;

$result = mysqli_query(
    $conn,
    "
SELECT * FROM logos
$whereSQL
ORDER BY id DESC
LIMIT $start, $limit
",
);
$shown_logo = mysqli_num_rows($result);

$total_query = mysqli_query(
    $conn,
    "
SELECT COUNT(*) as total
FROM logos
$whereSQL
",
);
$total_data = mysqli_fetch_assoc($total_query);
$total_logo = $total_data['total'];

$total_pages = ceil($total_logo / $limit);

$prov = mysqli_query($conn, 'SELECT * FROM provinces ORDER BY name ASC');

?>

<!doctype html>

<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Turikale Print - Aset Logo Terlengkap</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../Logo-Icon-TurikalePrint.jpg" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#136dec",
                        secondary: "#10b981",
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
    <style>
        body {
            font-family: "Inter", sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings:
                "FILL" 0,
                "wght" 400,
                "GRAD" 0,
                "opsz" 24;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100">
    <header
        class="sticky top-0 z-50 w-full border-b border-slate-200 dark:border-slate-800 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="/" class="flex items-center">
                    <img src="../assets/images/Logo Biru-TurikalePrint.png" alt="Turikale Print" class="h-11 w-auto" />
                </a>
                <nav class="hidden md:flex items-center gap-8">
                    <a class="text-sm font-medium hover:text-primary transition-colors" href="../../index.html">Beranda</a>
                    <a class="text-sm font-medium hover:text-primary transition-colors"
                        href="../pages/profil.html">Profil</a>
                    <a class="text-sm font-bold text-primary border-b-2 border-primary" href="#">Aset Logo</a>
                    <a class="text-sm font-medium hover:text-primary transition-colors"
                        href="../pages/artikel.php">Artikel</a>
                </nav>
                

                <button id="menu-toggle"
                    class="md:hidden text-slate-800 dark:text-white focus:outline-none transition-transform duration-300">
                    <span id="menu-icon" class="material-symbols-outlined text-3xl">
                        menu
                    </span>
                </button>
            </div>

            <div class="relative md:hidden">
                <div id="mobile-menu"
                    class="absolute right-0 mt-3 w-64 origin-top transform scale-y-0 opacity-0 transition-all duration-300 ease-in-out bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col gap-5">
                    <a class="text-base font-medium hover:text-primary transition-colors"
                        href="../index.html">Beranda</a>
                   <a class="text-base font-medium hover:text-primary transition-colors"
                        href="../pages/profil.html">Profil</a>
                    <a class="text-base font-semibold hover:text-primary transition-colors" href="#">Aset Logo</a>
                    <a class="text-base font-medium hover:text-primary transition-colors"
                        href="../pages/artikel.php">Artikel</a>
                </div>
            </div>
        </div>
    </header>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <section class="mb-12">
            <div class="relative overflow-hidden rounded-xl bg-slate-900 px-6 py-16 text-center shadow-xl sm:px-12 sm:py-20"
                data-alt="Abstract blue and green digital background pattern"
                style="
            background-image:
              linear-gradient(
                to bottom right,
                rgba(19, 109, 236, 0.8),
                rgba(16, 185, 129, 0.6)
              ),
              url(&quot;https://lh3.googleusercontent.com/aida-public/AB6AXuD-sialny-DA-T_gszQKGaL94SRU6hLEwYNttmV7KrkdWq2zgLALcrrzQseutxaTFhPfDucZWwBHGTovCANRDm-wx4pkD-IT2CxgO7zfsuOYhcE0YC4btaDkNZn6hzgEJepu62gWsZNDwVCf2IzYA5erVx1qs-9TZqKENnEqK-h0Ug0oPU83AxiBHMDyUROolcSMVEWSx7umZwR0yjXCAeJ2ekvScXs9V0RztFi7_5IbQWEqpAEkwDRJOLsXUjjew015UcyANkFJVzq&quot;);
          ">
                <div class="relative z-10 max-w-2xl mx-auto">
                    <h1 class="text-3xl font-black tracking-tight text-white sm:text-5xl mb-4">
                        Aset Logo Terlengkap
                    </h1>
                    <p class="text-lg text-slate-100 mb-8">
                        Temukan ribuan aset logo institusi, sekolah, dan organisasi untuk
                        kebutuhan desain profesional Anda.
                    </p>
                    <!-- Main Search Bar -->
                    <div class="relative max-w-xl mx-auto">

                        <form method="GET">
                            <div class="flex items-center bg-white dark:bg-slate-800 rounded-xl shadow-lg p-2">
                                <span class="material-symbols-outlined text-slate-400 px-3">search</span>
                                <input
                                    class="w-full border-none focus:ring-0 text-slate-900 dark:text-white bg-transparent text-base"
                                    placeholder="Cari logo institusi, sekolah, atau organisasi..." type="text"
                                    name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">

                                <button
                                    class="bg-primary text-white px-6 py-2 rounded-lg font-bold hover:bg-primary/90 transition-colors">
                                    Cari
                                </button>
                            </div>

                        </form>


                    </div>
                </div>
            </div>
        </section>
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Mobile Filter Toggle Button -->
            <div class="lg:hidden mb-2">
                <button id="filter-toggle"
                    class="flex items-center gap-2 w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <span class="material-symbols-outlined text-primary">filter_list</span>
                    <span>Filter Pencarian</span>
                    <span id="filter-arrow" class="material-symbols-outlined ml-auto transition-transform duration-300">expand_more</span>
                </button>
            </div>
            <!-- Sidebar Filters -->
            <aside class="w-full lg:w-64 flex-shrink-0">

                <div id="filter-panel"
                    class="hidden lg:block bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 lg:sticky lg:top-24">
                    <div class="hidden lg:flex items-center gap-2 mb-6 border-b border-slate-100 dark:border-slate-700 pb-2">
                        <span class="material-symbols-outlined text-primary">filter_list</span>
                        <h3 class="font-bold text-slate-900 dark:text-white">
                            Filter Pencarian
                        </h3>
                    </div>
                    <div class="space-y-6">


                        <form id="filterForm" method="GET">

                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Provinsi</label>
                                <select
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary"
                                    id="province" name="province">
                                    <option value="">Pilih Provinsi</option>

                                    <?php
$prov = mysqli_query($conn,'SELECT * FROM provinces ORDER BY name ASC');
while($p = mysqli_fetch_assoc($prov)){
?>

<option value="<?php echo $p['id']; ?>"
<?php echo (isset($_GET['province']) && $_GET['province']==$p['id']) ? 'selected':'';?>>

<?php echo htmlspecialchars($p['name']); ?>

</option>

<?php } ?>

                                </select>

                            </div>
                            <!-- Regency Filter -->
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Kabupaten/Kota</label>
                                <select
                                    class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary"
                                    id="city" name="city">
                                    <option value="">Pilih Kabupaten</option>

                                </select>
                            </div>
                            <!-- Category Filter -->
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Kategori
                                    Khusus</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input class="rounded text-primary focus:ring-primary border-slate-300"
                                            type="checkbox" name="category[]" value="Sekolah" <?php echo (isset($_GET['category']) && in_array("Sekolah",$_GET['category']))?'checked':''; ?> >
                                        <span
                                            class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-primary">Sekolah
                                            (SD/SMP/SMA)</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input class="rounded text-primary focus:ring-primary border-slate-300"
                                            type="checkbox" name="category[]" value="Pemerintahan" <?php echo (isset($_GET['category']) && in_array("Pemerintahan",$_GET['category']))?'checked':''; ?>> 
                                            
                                        <span
                                            class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-primary">Institusi
                                            Pemerintah</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input class="rounded text-primary focus:ring-primary border-slate-300"
                                            type="checkbox" name="category[]" value="Perguruan Tinggi" <?php echo (isset($_GET['category']) && in_array("Perguruan Tinggi",$_GET['category']))?'checked':''; ?> >
                                        <span
                                            class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-primary">Perguruan Tinggi</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input class="rounded text-primary focus:ring-primary border-slate-300"
                                            type="checkbox" name="category[]" value="Organisasi" <?php echo (isset($_GET['category']) && in_array("Organisasi",$_GET['category']))?'checked':''; ?> >
                                        <span
                                            class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-primary">Organisasi</span>
                                    </label>
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                Cari
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
            <!-- Logo Grid -->
            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-slate-600 dark:text-slate-400 text-sm">
                        Menampilkan
                        <span class="font-bold text-slate-900 dark:text-white">
                            <?php echo $shown_logo; ?>
                        </span>
                        dari <?php echo number_format($total_logo); ?> aset logo
                    </p>
                    
                </div>

                

                <div id="logoGrid" class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-1 sm:gap-2">

<?php if(mysqli_num_rows($result) > 0){ ?>

<?php while ($row = mysqli_fetch_assoc($result)) { ?>

<?php
$badgeClass = "";

switch ($row['category']) {

case "Sekolah":
$badgeClass = "bg-gradient-to-r from-orange-500 to-yellow-400 text-white";
break;

case "Pemerintahan":
$badgeClass = "bg-gradient-to-r from-red-600 to-amber-500 text-white";
break;

case "Perguruan Tinggi":
$badgeClass = "bg-gradient-to-r from-green-600 to-teal-500 text-white";
break;

case "Organisasi":
$badgeClass = "bg-gradient-to-r from-blue-600 to-cyan-500 text-white";
break;

default:
$badgeClass = "bg-gradient-to-r from-emerald-600 to-lime-500 text-white";
}
?>

<div class="group flex flex-col h-full bg-white dark:bg-slate-900 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 hover:shadow-xl transition-all">
    
<div class="aspect-square w-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center p-3 sm:p-6 overflow-hidden">

<img src="../../uploads/logos/<?php echo $row['file_name']; ?>" class="max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal hover:scale-105 transition-transform duration-300">

</div>

<div class="p-3 sm:p-4 flex flex-col flex-1 gap-2 sm:gap-3">

<h4 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white leading-tight line-clamp-2">
<?php echo $row['title']; ?>
</h4>

<p class="w-fit text-[9px] sm:text-[10px] px-2 py-1 rounded font-bold uppercase tracking-wider <?php echo $badgeClass; ?>">
<?php echo $row['category']; ?>
</p>

<!-- The mt-auto margin pushes the following block to the bottom of the flex container -->
<div class="mt-auto pt-2">
    <a href="../../uploads/logos/<?php echo $row['file_name']; ?>" class="w-full inline-block" download="<?php echo htmlspecialchars($row['title']) . '.' . pathinfo($row['file_name'], PATHINFO_EXTENSION); ?>">

    <button class="w-full bg-primary text-white text-sm font-bold py-2 rounded-lg hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
    Unduh
    </button>

    </a>
</div>

</div>

</div>

<?php } ?>

<?php } else { ?>

<div class="col-span-full text-center py-20">

<p class="text-slate-500 text-lg font-semibold">
Tidak ada data
</p>

<p class="text-slate-400 text-sm mt-2">
Logo yang Anda cari belum tersedia
</p>

</div>

<?php } ?>

</div>
                <!-- Pagination -->
                <div class="mt-12 flex justify-center">
                    <div class="flex justify-center mt-8 gap-2">

                        <?php for ($i = 1;$i <= $total_pages;$i++) { ?>

                        <a href="?page=<?php echo $i; ?>" class="px-4 py-2 border rounded 
                        <?php echo $page == $i ? 'bg-blue-500 text-white' : 'bg-white'; ?>">

                            <?php echo $i; ?>

                        </a>

                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-12 px-4 lg:px-20">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-3 md:grid-cols-4 gap-4 md:gap-12 mb-12">
                <div class="col-span-3 md:col-span-2">
                    <img src="../assets/images/Logo Biru-TurikalePrint.png" alt="Turikale Print"
                        class="h-10 w-auto" />
                    <p class="text-slate-500 max-w-sm mb-6">
                        <br />Partner terpercaya untuk segala kebutuhan bisnis dan
                        personal anda. Cepat, berkualitas, dan terjangkau.
                    </p>
                    <div class="flex items-center gap-4">
                        <a class="size-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-primary hover:text-white transition-colors"
                            href="https://www.tiktok.com/@turikale_digitalprinting?is_from_webapp=1&sender_device=pc"
                            target="_blank" rel="noopener noreferrer ">
                            <svg fill="#000000" viewBox="0 0 14 14" role="img" focusable="false"
                                aria-hidden="true" xmlns="https://www.w3.org/2000/svg">
                                <path
                                    d="m 4.9520184,12.936803 c -1.12784,-0.2039 -2.19411,-0.9875 -2.66789,-1.9606 -0.32895,-0.6757 -0.45541,-1.3901003 -0.37418,-2.1137003 0.15054,-1.3412 0.84482,-2.4395 1.92406,-3.0439 0.56899,-0.3186 1.38421,-0.4769 1.991,-0.3867 l 0.35091,0.052 9e-5,1.0725 9e-5,1.0725 -0.332,-0.014 c -0.79998,-0.033 -1.39595,0.3096 -1.70379,0.9784 -0.1473,0.32 -0.18461,0.8887 -0.081,1.2351 0.12773,0.4273003 0.50542,0.8132003 0.96145,0.9825003 0.15535,0.058 0.32344,0.08 0.61152,0.079 0.35862,-4e-4 0.42448,-0.013 0.67663,-0.1323 0.36505,-0.1726 0.63141,-0.4231 0.78797,-0.7411 0.10147,-0.2061003 0.13414,-0.3430003 0.16587,-0.6951003 0.0217,-0.2412 0.0401,-2.2122 0.0409,-4.38 l 10e-4,-3.94149998 0.68371,0 c 0.37605,0 0.8277,0.012 1.00368,0.027 l 0.31995,0.027 0,0.1584 c 0,0.3813 0.22299,1.1127 0.45156,1.4812 0.0571,0.092 0.2564996,0.3178 0.4431796,0.5018 0.36068,0.3555 0.66494,0.5352 1.13352,0.6692 0.138,0.04 0.28359,0.089 0.32353,0.109 0.0399,0.02 0.11522,0.038 0.16728,0.038 0.0521,4e-4 0.13701,0.012 0.18876,0.026 l 0.0941,0.025 0,0.9948 0,0.9948 -0.17773,-0.019 c -0.9611,-0.1037 -1.72925,-0.3601 -2.3423096,-0.782 -0.30468,-0.2096 -0.33102,-0.222 -0.30218,-0.1422 0.0104,0.029 0.003,1.1249 -0.0164,2.436 -0.0336,2.2728 -0.0396,2.3992 -0.12781,2.7173003 -0.33904,1.2222 -1.09994,2.1297 -2.10183,2.5068 -0.6126,0.2306 -1.39679,0.2932 -2.09405,0.1671 z" />
                            </svg>
                        </a>
                        <a class="size-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-primary hover:text-white transition-colors"
                            href="https://www.instagram.com/turikale.print?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="
                            target="_blank" rel="noopener noreferrer">
                            <svg fill="#000000" viewBox="0 0 24 24" xmlns="https://www.w3.org/2000/svg"
                                data-name="Layer 1">
                                <path
                                    d="M17.34,5.46h0a1.2,1.2,0,1,0,1.2,1.2A1.2,1.2,0,0,0,17.34,5.46Zm4.6,2.42a7.59,7.59,0,0,0-.46-2.43,4.94,4.94,0,0,0-1.16-1.77,4.7,4.7,0,0,0-1.77-1.15,7.3,7.3,0,0,0-2.43-.47C15.06,2,14.72,2,12,2s-3.06,0-4.12.06a7.3,7.3,0,0,0-2.43.47A4.78,4.78,0,0,0,3.68,3.68,4.7,4.7,0,0,0,2.53,5.45a7.3,7.3,0,0,0-.47,2.43C2,8.94,2,9.28,2,12s0,3.06.06,4.12a7.3,7.3,0,0,0,.47,2.43,4.7,4.7,0,0,0,1.15,1.77,4.78,4.78,0,0,0,1.77,1.15,7.3,7.3,0,0,0,2.43.47C8.94,22,9.28,22,12,22s3.06,0,4.12-.06a7.3,7.3,0,0,0,2.43-.47,4.7,4.7,0,0,0,1.77-1.15,4.85,4.85,0,0,0,1.16-1.77,7.59,7.59,0,0,0,.46-2.43c0-1.06.06-1.4.06-4.12S22,8.94,21.94,7.88ZM20.14,16a5.61,5.61,0,0,1-.34,1.86,3.06,3.06,0,0,1-.75,1.15,3.19,3.19,0,0,1-1.15.75,5.61,5.61,0,0,1-1.86.34c-1,.05-1.37.06-4,.06s-3,0-4-.06A5.73,5.73,0,0,1,6.1,19.8,3.27,3.27,0,0,1,5,19.05a3,3,0,0,1-.74-1.15A5.54,5.54,0,0,1,3.86,16c0-1-.06-1.37-.06-4s0-3,.06-4A5.54,5.54,0,0,1,4.21,6.1,3,3,0,0,1,5,5,3.14,3.14,0,0,1,6.1,4.2,5.73,5.73,0,0,1,8,3.86c1,0,1.37-.06,4-.06s3,0,4,.06a5.61,5.61,0,0,1,1.86.34A3.06,3.06,0,0,1,19.05,5,3.06,3.06,0,0,1,19.8,6.1,5.61,5.61,0,0,1,20.14,8c.05,1,.06,1.37.06,4S20.19,15,20.14,16ZM12,6.87A5.13,5.13,0,1,0,17.14,12,5.12,5.12,0,0,0,12,6.87Zm0,8.46A3.33,3.33,0,1,1,15.33,12,3.33,3.33,0,0,1,12,15.33Z" />
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
                            <a class="hover:text-primary transition-colors"
                                href="https://wa.me/6282188579988?text=Halo%20saya%20ingin%20bertanya" target="_blank"
                                rel="noopener noreferrer">Hubungi Kami</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 border-t border-slate-500 text-center text-sm text-slate-500">
                <p>© 2026 Turikale Print.</p>
            </div>
        </div>
    </footer>

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

        // Mobile filter panel toggle
        const filterToggleBtn = document.getElementById("filter-toggle");
        const filterPanel = document.getElementById("filter-panel");
        const filterArrow = document.getElementById("filter-arrow");
        let filterOpen = false;

        if (filterToggleBtn && filterPanel) {
            filterToggleBtn.addEventListener("click", () => {
                filterOpen = !filterOpen;
                if (filterOpen) {
                    filterPanel.classList.remove("hidden");
                    filterArrow.style.transform = "rotate(180deg)";
                } else {
                    filterPanel.classList.add("hidden");
                    filterPanel.classList.add("lg:block");
                    filterArrow.style.transform = "rotate(0deg)";
                }
            });
        }
    </script>
    <script>
       document.addEventListener("DOMContentLoaded", function(){

const province = document.getElementById("province")
const city = document.getElementById("city")

if(!province || !city) return

province.addEventListener("change", function(){

let province_id = this.value

if(province_id === ""){
city.innerHTML = '<option value="">Pilih Kabupaten</option>'
return
}

fetch('../../admin/api/get-cities.php?province_id=' + province_id)

.then(res => res.json())

.then(data => {

let html = '<option value="">Pilih Kabupaten</option>'

data.forEach(c=>{
html += `<option value="${c.id}">${c.name}</option>`
})

city.innerHTML = html

})

})

})
    </script>
    <script>
        document.addEventListener("DOMContentLoaded",function(){

const province = document.getElementById("province")
const city = document.getElementById("city")

const selectedCity = "<?php echo isset($_GET['city']) ? $_GET['city'] : '' ?>"

if(province.value !== ""){

fetch('../../admin/api/get-cities.php?province_id=' + province.value)

.then(res=>res.json())

.then(data=>{

let html = '<option value="">Pilih Kabupaten</option>'

data.forEach(c=>{

let selected = (c.id == selectedCity) ? "selected" : ""

html += `<option value="${c.id}" ${selected}>${c.name}</option>`

})

city.innerHTML = html

})

}

})
    </script>
    
</body>

</html>
