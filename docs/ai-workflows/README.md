# AI Workflows — PA GTS BMKG Sabang

Direktori ini berisi panduan lokal untuk membantu penyusunan Proyek Akhir D3 Manajemen Informatika USK dan dokumentasi pengembangan aplikasi Laravel/PHP.

## Target
- Saat ini: seminar proposal BAB I–III.
- Berikutnya: seminar hasil BAB IV–V sebagai kelanjutan proposal yang disetujui.

## Prinsip penggunaan
1. Template/pedoman resmi D3 Manajemen Informatika USK menjadi acuan format utama. Panduan FMIPA USK 2024 digunakan sejauh relevan.
2. Proposal teman hanya contoh organisasi dan visual; jangan menyalin kalimat, data pribadi, isi substantif, atau menganggap formatnya sebagai aturan resmi.
3. Pisahkan fakta terverifikasi, informasi dari pengguna, hasil pemeriksaan kode, asumsi, dan rencana.
4. Jangan mengarang hasil observasi, wawancara, referensi, fitur, atau hasil uji.
5. Jangan menganggap status HTTP/request sukses sebagai bukti pesan diterima sistem tujuan. Catat tingkat konfirmasi yang benar-benar diberikan mekanisme BMKG.
6. Jangan simpan token, kredensial, file .env, atau pesan operasional sensitif di dokumen/AI.
7. Perubahan aplikasi harus dilakukan hanya setelah audit dan persetujuan yang jelas.

## Struktur
- `skill-references/`: salinan lokal repositori skill publik untuk referensi; jangan instal atau jalankan skrip sebelum ditinjau.
- `workflows/`: prompt dan prosedur kerja yang dapat digunakan ulang.
- `project-evidence/`: template bukti masalah, inventaris fitur, dan matriks pengujian.
- `templates/`: template kerja netral; format akhir tetap mengikuti template resmi prodi.

## Alur utama
1. Petakan pedoman resmi dan perbedaan dengan contoh proposal.
2. Audit source code secara read-only.
3. Verifikasi masalah dan mekanisme pengiriman melalui dokumen yang diizinkan.
4. Bangun matriks literatur.
5. Susun BAB I, kemudian BAB II, kemudian BAB III.
6. Audit konsistensi dan persiapkan presentasi seminar.
7. Setelah proposal disetujui, catat revisi dan lanjutkan implementasi/pengujian untuk BAB IV–V.

## Penggunaan Desktop Commander
Gunakan Desktop Commander untuk membaca/menulis file dan menjalankan perintah lokal secara bertahap. Mulai dengan pemeriksaan read-only. Jangan mengubah source code, menjalankan migrasi, mengirim pesan operasional, atau menjalankan perintah destruktif tanpa persetujuan eksplisit.
