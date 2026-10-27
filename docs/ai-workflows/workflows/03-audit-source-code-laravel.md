# Workflow 03 — Audit Source Code Laravel

## Mode awal: read-only
Jangan mengubah kode sebelum laporan audit ditinjau.

## Prompt
Audit source code aplikasi Laravel/PHP untuk proyek akhir GTS BMKG Sabang. Inventarisasi route, controller, model, migration, service/helper, job, validasi, antarmuka, proses pengiriman, penanganan respons, penyimpanan status, autentikasi/otorisasi, logging, dan pengujian yang relevan.

Buat tabel: fitur/alur, file dan lokasi, perilaku yang terbukti, dependensi, status VERIFIED/PARTIAL/PLANNED/UNKNOWN, risiko, pengujian yang diperlukan, dan dampak dokumentasi proposal.

Pada proses pengiriman, bedakan:
1. Pesan dibentuk/diedit.
2. Pesan tervalidasi.
3. Request diteruskan melalui mekanisme aktual.
4. Mekanisme memberi respons.
5. Sistem tujuan mengonfirmasi penerimaan (jika konfirmasi ini memang tersedia).

Jangan menyamakan keberhasilan HTTP dengan keberhasilan penerimaan pesan. Jangan menampilkan/membaca rahasia dari .env, token, kata sandi, kredensial atau data operasional sensitif. Jangan mengirim pesan nyata dalam pengujian. Jangan mengubah kode, menjalankan migration, atau mengakses sistem eksternal tanpa izin eksplisit.

Tutup laporan dengan daftar fakta untuk proposal, informasi belum terverifikasi, kasus uji yang disarankan, serta pertanyaan yang harus dikonfirmasi kepada pihak BMKG.
