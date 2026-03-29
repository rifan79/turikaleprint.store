<?php



session_start();
require '../config/database.php';

if (isset($_POST['login'])) {
    $username = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM admins WHERE username='$username'");
    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin'] = $user['username'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Username atau password salah';
    }
}
?>

<!doctype html>

<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Login Admin - Turikale Print</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
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
                        "accent-green": "#10b981",
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

<body class="bg-background-light dark:bg-background-dark min-h-screen flex items-center justify-center p-4">
    <div
        class="flex w-full max-w-[1000px] h-full min-h-[600px] bg-white dark:bg-slate-900 rounded-xl shadow-xl overflow-hidden">
        <!-- Left Side: Split Screen Branding -->
        <div
            class="hidden lg:flex lg:w-1/2 relative bg-primary items-center justify-center p-12 text-white overflow-hidden">
            <div class="absolute inset-0 opacity-20" data-alt="Abstract printing press machinery close up"
                style="
            background-image: url(&quot;https://lh3.googleusercontent.com/aida-public/AB6AXuC6NAQUTKz2TBezW6ImUmJYo37evjjFB1iY80SfJjTCHMf3RMLi5VPCGwNF9ZxWbT5IP9YdMFrJQ-VnkhFdUWzz3up4D9i1Wvzit5pd9aH8qc4MrPk9K1PevypUE2NrszOpdXy65Jd2R9vNTqymo5XBzGyrIhGzKLr75d3EQLGrh8k6WSjD6ooGdx6IOXVaa1ta1nNt8pHalW494k0zfuVHSbbSdoLDLQEm9mVXUCDnDnCmHIrZF6A46sujUKiPv8l2Zpfa0Dg6TH6d&quot;);
            background-size: cover;
            background-position: center;
          ">
            </div>
            <div class="relative z-10 flex flex-col items-start gap-6">
                
                <div>
                    <div class="p-6 flex items-center gap-3">
                    <img src="https://turikaleprint.store/public/assets/images/Logo Putih-TurikalePrint.png" alt="Turikale Print" class="h-20 w-auto" />
                    </div>
                    
                    <p class="mt-4 text-white/80 text-lg leading-relaxed">
                        Solusi cetak profesional untuk kebutuhan bisnis dan personal Anda.
                        Kelola operasional toko dengan lebih mudah dan efisien.
                    </p>
                </div>
                <div class="flex items-center gap-2 mt-4 text-accent-green font-semibold">
                    <span class="material-symbols-outlined">verified</span>
                    <span>Admin Dashboard System</span>
                </div>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-accent-green/20 rounded-full blur-3xl"></div>
        </div>
        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col p-8 md:p-12 lg:p-16">
            <div class="flex lg:hidden items-center gap-2 mb-8 text-primary">
                <div class="size-6">
                    <svg fill="none" viewbox="0 0 48 48" xmlns="https://www.w3.org/2000/svg">
                        <path
                            d="M24 4C25.7818 14.2173 33.7827 22.2182 44 24C33.7827 25.7818 25.7818 33.7827 24 44C22.2182 33.7827 14.2173 25.7818 4 24C14.2173 22.2182 22.2182 14.2173 24 4Z"
                            fill="currentColor"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold tracking-tight">Turikale Print</h2>
            </div>
            <div class="mb-10">
                <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100">
                    Login Admin
                </h2>
                <p class="text-slate-500 dark:text-slate-400 mt-2">
                    Masuk untuk mengelola konten toko Anda
                </p>
            </div>
            
            <?php if(isset($error)){ ?>
<div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm font-medium">
    <?php echo $error; ?>
</div>
<?php } ?>
             
            <form method="POST" class="flex flex-col gap-5">
                <!-- Email Address -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                        for="email">Username</label>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                            <span class="material-symbols-outlined text-[20px]">mail</span>
                        </div>
                        <input
                            class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none"
                            id="email" name="email" placeholder="Username" type="text" />
                    </div>
                </div>
                <!-- Password -->
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                            for="password">Password</label>
                        
                    </div>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                            <span class="material-symbols-outlined text-[20px]">lock</span>
                        </div>
                        <input
                            class="block w-full pl-11 pr-11 py-3.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none"
                            id="password" name="password" placeholder="••••••••" type="password" />
                        <div
id="togglePassword"
class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
<span id="eyeIcon" class="material-symbols-outlined text-[20px]">visibility</span>
</div>
                    </div>
                </div>
               
                <!-- Login Button -->
                <button
                    class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3.5 rounded-lg transition-all transform active:scale-[0.98] shadow-lg shadow-primary/20 mt-4"
                    name="login" type="submit">
                    Login
                </button>
            </form>
            <div class="mt-auto pt-10 flex flex-col items-center gap-4">
                <div class="w-full h-px bg-slate-100 dark:bg-slate-800"></div>
                
            </div>
        </div>
    </div>
    <script>

const togglePassword = document.getElementById("togglePassword")
const passwordInput = document.getElementById("password")
const eyeIcon = document.getElementById("eyeIcon")

togglePassword.addEventListener("click", function(){

const type = passwordInput.getAttribute("type") === "password" ? "text" : "password"

passwordInput.setAttribute("type", type)

eyeIcon.textContent = type === "password" ? "visibility" : "visibility_off"

})

</script>
</body>

</html>
