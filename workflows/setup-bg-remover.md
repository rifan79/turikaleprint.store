---
description: Menjalankan Background Remover Backend API
---

Fitur Hapus Latar (AI) pada aplikasi ini sekarang didukung oleh proses AI murni di Backend yang menggunakan library Python `rembg`.

Langkah-langkah untuk menjalankan Server AI:

1. Buka terminal (Command Prompt/PowerShell) baru.
2. Pastikan Anda sudah menginstall Python.
3. Install semua *dependencies* yang dibutuhkan dengan perintah berikut:
   ```bash
   pip install flask flask-cors rembg pillow
   ```
4. Jalankan script API menggunakan Python dari root project (`d:\project web\turikaleprint`):
   ```bash
   python api/bg_remover.py
   ```
5. Server akan berjalan pada `http://0.0.0.0:5000`. Biarkan terminal ini terbuka selama Anda menggunakan fitur Hapus Latar di web.

> [!NOTE]
> Saat pertama kali dijalankan dan menerima request hapus latar, `rembg` akan mengunduh model AI (U-2-Net). Pastikan koneksi internet stabil. Setelah terunduh, proses hapus latar selanjutnya akan berjalan secara lokal dan cepat.
