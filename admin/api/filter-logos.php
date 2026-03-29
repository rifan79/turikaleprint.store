<?php
require __DIR__ . '/../../config/database.php';

function esc($conn, $v)
{
    return mysqli_real_escape_string($conn, $v);
}

$where = [];

// categories (checkbox array)
if (isset($_GET['category'])) {
    $categories = $_GET['category'];
    if (is_array($categories) && count($categories)) {
        $escaped = array_map(function ($v) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $v) . "'";
        }, $categories);
        $where[] = 'category IN (' . implode(',', $escaped) . ')';
    }
}

// province filter (expect ID)
if (isset($_GET['province']) && $_GET['province'] !== '') {
    $prov = $_GET['province'];
    if (is_numeric($prov)) {
        $has = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM logos LIKE 'province_id'"));
        if ($has) {
            $where[] = 'province_id = ' . (int) $prov;
        } else {
            // fallback: lookup province name and compare against province column
            $q = mysqli_query($conn, 'SELECT name FROM provinces WHERE id = ' . (int) $prov . ' LIMIT 1');
            if ($r = mysqli_fetch_assoc($q)) {
                $where[] = "province = '" . esc($conn, $r['name']) . "'";
            }
        }
    } else {
        $where[] = "province = '" . esc($conn, $prov) . "'";
    }
}

// city filter (expect ID)
if (isset($_GET['city']) && $_GET['city'] !== '') {
    $city = $_GET['city'];
    if (is_numeric($city)) {
        $has = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM logos LIKE 'city_id'"));
        if ($has) {
            $where[] = 'city_id = ' . (int) $city;
        } else {
            $q = mysqli_query($conn, 'SELECT name FROM cities WHERE id = ' . (int) $city . ' LIMIT 1');
            if ($r = mysqli_fetch_assoc($q)) {
                $where[] = "city = '" . esc($conn, $r['name']) . "'";
            }
        }
    } else {
        $where[] = "city = '" . esc($conn, $city) . "'";
    }
}

// search
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $s = esc($conn, $_GET['search']);
    $where[] = "title LIKE '%$s%'";
}

// build where
$whereSQL = '';
if (!empty($where)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}

// limit (for API: we return first 24 by default)
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 24;
$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;

$sql = "SELECT * FROM logos $whereSQL ORDER BY id DESC LIMIT $start, $limit";
$res = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($res)) {

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

$img = "../../uploads/logos/" . htmlspecialchars($row['file_name']);
$title = htmlspecialchars($row['title']);
$cat = htmlspecialchars($row['category']);
$downloadName = strtolower(str_replace(' ', '-', $row['title']));

echo "
<div class='group flex flex-col bg-white dark:bg-slate-900 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 hover:shadow-xl transition-all'>

<div class='aspect-square w-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center p-6 relative overflow-hidden'>
<img src='$img' class='max-w-full max-h-full object-contain'>
</div>

<div class='p-3 flex flex-col gap-2'>

<h4 class='font-bold text-l text-slate-900 dark:text-white'>$title</h4>

<p class='w-fit text-[8px] px-1 py-1 rounded font-bold uppercase tracking-wider $badgeClass'>
$cat
</p>

<a href='$img' download='$downloadName.png' class='w-full'>
<button class='w-full bg-primary text-white text-sm font-bold py-2 rounded-lg hover:bg-primary/90 transition-colors'>
Unduh
</button>
</a>

</div>
</div>
";
}
?>
