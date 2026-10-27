# Audit Panduan USK dan Proposal Contoh
Tanggal audit: 15 Oktober 2026  
Proyek: Pengembangan Sistem Informasi Pengelolaan serta Pengiriman Sandi Cuaca GTS dan Meteorologi Berbasis Web pada Stasiun Meteorologi BMKG Sabang  
Tujuan: menyiapkan landasan format dan isi untuk seminar proposal BAB I–III.

## 1. Ringkasan Eksekutif

1. Dua PDF yang diperiksa: Panduan TA-Tesis FMIPA USK 2024 (152 halaman PDF) dan Seminar Proposal M. Fadwa Maulyady (50 halaman PDF), sebagai contoh proposal aplikasi berbasis web.
2. Panduan FMIPA menyediakan struktur umum penelitian empiris dan aturan format, tetapi definisi TA pada dokumen mengacu ke Program Sarjana FMIPA. Dokumen itu bukan bukti tunggal bahwa semua ketentuan berlaku identik untuk Proyek Akhir D3 Manajemen Informatika. Gunakan sejauh relevan dan konfirmasikan perbedaannya kepada pembimbing/koordinator D3 MI.
3. Pemeriksaan read-only atas direktori proyek tidak menemukan PDF/DOC/DOCX/ODT pedoman atau template akademik tambahan di luar direktori referensi skill. Template khusus D3 MI yang disebutkan dalam percakapan belum dapat diidentifikasi sebagai file lokal tersendiri.
4. Dua PDF yang diperiksa tidak memuat bagian atau istilah “sinopsis” pada teks yang dicari. Format sinopsis perlu diperoleh dari template/instruksi resmi D3 MI atau dikonfirmasi kepada pembimbing/koordinator.
5. Proposal teman memiliki kesalahan penomoran di BAB III: subbagian waktu/tempat dan alat/bahan masih ditulis 2.1 dan 2.2 setelah judul BAB III. Jangan menyalin penomoran tersebut.
6. Proposal teman berguna sebagai contoh penyajian kebutuhan, diagram, implementasi, dan pengujian. Metode, diagram, teknologi, dan jenis uji dari contoh tidak otomatis sesuai dengan sistem pengelolaan/pengiriman sandi cuaca GTS.

## 2. Sumber dan Batas Keberlakuannya

| ID | Sumber | Bagian yang digunakan | Status penggunaan |
|---|---|---|---|
| S-01 | Panduan TA-Tesis FMIPA USK 2024 | Struktur penelitian empiris; prosedur seminar; rubrik proposal; sistematika; format, sitasi, dan referensi | RESMI untuk lingkup yang dijelaskan dokumen. Relevansi terhadap D3 MI harus dikonfirmasi karena panduan mendefinisikan TA pada konteks Program Sarjana FMIPA. |
| S-02 | Seminar Proposal M. Fadwa Maulyady | Daftar isi, BAB I–III, jadwal, alat/bahan, metode kerja, diagram, implementasi, pengujian, daftar pustaka | CONTOH SAJA. Bermanfaat untuk melihat bentuk penyajian, bukan menetapkan aturan resmi D3 MI. |
| S-03 | Template khusus D3 Manajemen Informatika USK | Tidak ditemukan sebagai file terpisah pada direktori proyek yang diperiksa dan bukan salah satu dari dua PDF yang ditinjau | BELUM TERVERIFIKASI / PERLU DISERTAKAN. Ini tidak berarti pengguna tidak memiliki file tersebut di lokasi lain. |

Nomor halaman di bawah menunjukkan halaman tercetak jika tersedia; nomor halaman PDF dicantumkan bila berbeda. Untuk proposal teman, nomor halaman yang disebut merupakan halaman PDF.

## 3. Matriks Perbandingan BAB I–III dan Unsur Pendukung

| Aspek | Panduan FMIPA USK 2024 | Proposal teman | Status dan tindakan untuk PA BMKG Sabang |
|---|---|---|---|
| Struktur proposal | Penelitian empiris: BAB I Pendahuluan, BAB II Tinjauan Kepustakaan, BAB III Metode Penelitian. BAB IV–V adalah bagian laporan akhir, bukan isi inti proposal BAB I–III. (Hal. tercetak 41; PDF 55) | Daftar isi menunjukkan BAB I–III, tetapi bagian implementasi dan pengujian juga ditulis di bawah BAB III. (PDF 4–5, 47–48) | RESMI + CONTOH SAJA. Target saat ini tetap BAB I–III. Konfirmasi susunan rinci BAB III ke template D3 MI; jangan memasukkan BAB IV–V sebagai hasil final proposal hanya karena muncul dalam sistematika laporan akhir. |
| BAB I: latar belakang | Memuat landasan pemikiran dan alasan pentingnya penelitian. (Hal. tercetak 47–48; PDF 61–62) | Menggunakan 1.1 Latar Belakang Masalah. (PDF 4) | RESMI pada panduan; contoh mendukung pola. Untuk proyek BMKG, alurkan konteks tugas meteorologi → proses aktual yang dikonfirmasi → masalah berbukti → kebutuhan sistem → tujuan. Jangan mengarang masalah operasional, statistik, atau dampak. |
| BAB I: rumusan masalah dan batasan | Masalah harus jelas, dibatasi, dan menjelaskan lingkup/asumsi; rumusan tidak harus berbentuk pertanyaan. (Hal. tercetak 48; PDF 62) | 1.2 Rumusan Masalah. Daftar isi tidak menunjukkan subbab batasan masalah tersendiri. (PDF 4) | RESMI + CONTOH SAJA. Rumuskan masalah yang dapat dijawab oleh pengembangan/evaluasi aplikasi. Tetapkan batasan secara eksplisit sebagai subbab tersendiri jika template D3 mewajibkannya, atau pada bagian yang ditentukan template. Konfirmasikan penempatan yang wajib. |
| BAB I: tujuan | Pernyataan singkat tentang hal yang hendak dilakukan, dijelaskan, diuji, dibuktikan, atau diterapkan. (Hal. tercetak 48; PDF 62) | 1.3 Tujuan Penelitian. (PDF 4) | RESMI + CONTOH SAJA. Setiap tujuan harus berpasangan dengan rumusan masalah. Gunakan kata kerja yang bisa dibuktikan dan sesuai lingkup nyata. |
| BAB I: hipotesis | Ditulis jika ada; penelitian eksploratif/deskriptif tidak selalu membutuhkan hipotesis eksplisit. (Hal. tercetak 48; PDF 62) | Tidak tampak sebagai subbab tersendiri pada daftar isi contoh. (PDF 4) | RESMI untuk penjelasan dalam panduan; tidak otomatis wajib pada PA ini. Konfirmasi jika template D3 mengharuskannya. Jangan membuat hipotesis statistik tanpa desain yang mendukungnya. |
| BAB I: manfaat | Menguraikan kontribusi penelitian bagi peneliti, ilmu, pemecahan masalah atau pengembangan institusi. (Hal. tercetak 48; PDF 62) | 1.4 Manfaat Penelitian. (PDF 4) | RESMI + CONTOH SAJA. Jelaskan manfaat realistis bagi proses kerja/pengguna dan pengembangan akademik. Hindari klaim peningkatan efisiensi/akurasi tanpa pengukuran. |
| BAB II: tinjauan kepustakaan | Memuat landasan teori dan penelitian terdahulu yang mirip/relevan; kajian pustaka pada laporan akhir mengembangkan yang ada di proposal. (Hal. tercetak 48–49; PDF 62–63) | 13 topik meliputi search engine, sistem informasi, SIMKOPDES, API, PHP, Laravel, MySQL, Waterfall, UML, ERD, Black-Box, SUS, dan penelitian terkait. (PDF 4–5) | RESMI untuk tujuan BAB II; contoh hanya pola pengelompokan. Jangan salin daftar teori. Pilih teori yang langsung menjelaskan domain GTS/meteorologi, mekanisme aktual, stack terverifikasi, pemodelan, dan metode uji yang akan digunakan. |
| Penelitian terkait / research gap | Meminta tinjauan hasil penelitian atas masalah yang mirip. (Hal. tercetak 48–49; PDF 62–63) | Bagian 2.13 membandingkan studi terdahulu dan membahas persamaan/perbedaan. (PDF 18) | RESMI untuk adanya kajian terkait; contoh SAJA untuk cara penyajian. Gunakan artikel yang benar-benar dibaca. Gap tidak boleh sekadar “belum dilakukan di BMKG Sabang” tanpa alasan penting yang dibuktikan. |
| BAB III: tempat dan waktu | Tempat/waktu adalah bagian dari metode penelitian. (Hal. tercetak 41, 48–49; PDF 55, 63) | Bagian waktu/tempat membedakan pengambilan kebutuhan di lokasi koperasi dengan pengembangan/pengujian di laboratorium. (PDF 19) | RESMI pada panduan; contoh memperlihatkan cara menjelaskan. Nyatakan lokasi dan waktu aktual yang direncanakan serta pisahkan lokasi observasi dari lingkungan pengembangan. |
| BAB III: jadwal | Jadwal pelaksanaan tercantum secara eksplisit dalam struktur proposal penelitian. (Hal. tercetak 41, 48–49; PDF 55, 63) | Jadwal April–Desember 2026 berisi pengumpulan data, analisis, rancangan, implementasi, pengujian dan dokumentasi. (PDF 19–20) | RESMI menurut panduan; rentang contoh hanya contoh. Susun jadwal berdasarkan target seminar, persetujuan pembimbing dan keadaan aplikasi yang sudah ada; jangan menyalin jadwal teman. |
| BAB III: alat dan bahan | Dicantumkan sebagai komponen metode penelitian. (Hal. tercetak 41, 48–49; PDF 55, 63) | Contoh memuat spesifikasi laptop, Ubuntu, VS Code, PHP, Laravel, Apache, MySQL, Node.js, Composer dan Figma. (PDF 20) | RESMI pada tingkat struktur; daftar perangkat hanya CONTOH SAJA. Catat versi aktual yang diverifikasi dari proyek/lingkungan. Jangan menyalin versi atau perangkat teman. Lindungi .env dan kredensial. |
| BAB III: prosedur/metode kerja | Bagian metode menjelaskan prosedur penelitian empiris. (Hal. tercetak 41, 48–49; PDF 55, 63) | Contoh memilih Waterfall, analisis kebutuhan, dan perancangan; pengumpulan kebutuhan melalui wawancara. (PDF 20–22) | RESMI pada tingkat prosedur umum; Waterfall/wawancara hanya contoh. Karena aplikasi telah dikembangkan, metode harus menggambarkan pekerjaan aktual yang tersisa; jangan menyatakan sistem dibangun seluruhnya dari nol. Konfirmasi metode dengan pembimbing. |
| Diagram dan rancangan | Panduan menjelaskan fungsi ilustrasi serta penomoran tabel/gambar. (Hal. tercetak 54–56; PDF 68–70) | Memuat Use Case, sejumlah Activity Diagram, ERD, dan tampilan antarmuka. (PDF 7, 21–47) | Contoh SAJA untuk jenis/jumlah diagram; format ilustrasi mengikuti pedoman yang relevan. Pilih diagram yang dibutuhkan oleh sistem aktual: aktor/hak akses, alur pembentukan-validasi-pengiriman-respons/status, data, integrasi/arsitektur, dan antarmuka terkait. Verifikasi pada audit source code. |
| Implementasi dalam BAB III | Panduan umum memasukkan prosedur penelitian di BAB III dan BAB IV sebagai hasil/pembahasan dalam laporan akhir. (Hal. tercetak 41, 49; PDF 55, 63) | Daftar isi memiliki 3.4 Implementasi dan 3.5 Pengujian di BAB III. (PDF 5, 47–48) | PERLU KONFIRMASI untuk format D3 MI. Jika implementasi/pengujian di BAB III ditulis sebagai rencana, hasil aktual tetap perlu ditempatkan menurut template laporan akhir. Ikuti struktur resmi prodi, bukan asumsi. |
| Pengujian | Panduan umum memerlukan metode/prosedur yang jelas, tetapi tidak menetapkan Black-Box atau SUS sebagai metode universal untuk proyek ini. (Hal. tercetak 48–49; PDF 62–63) | Menggabungkan Black-Box dan System Usability Scale (SUS), serta kuesioner kepada pengguna. (PDF 48) | CONTOH SAJA. Pertimbangkan uji fungsional untuk validasi, penyimpanan, respons/status dan error sesuai kode. SUS hanya jika tujuan usability, responden, instrumen dan prosedur relevan serta disetujui. Request HTTP sukses bukan bukti pesan diterima tujuan. Jangan menguji pengiriman operasional tanpa izin. |
| Biaya penelitian | Struktur proposal mencantumkan rincian biaya; terdapat format rincian biaya. (Hal. tercetak 41, 49; PDF 55, 63; lampiran biaya PDF 121) | Subbab biaya tidak tampak pada daftar isi contoh. (PDF 4–5) | RESMI dalam panduan FMIPA; kewajiban untuk D3 MI PERLU KONFIRMASI. Ikuti template khusus prodi. |
| Sinopsis | Tidak ditemukan bagian/istilah “sinopsis” pada teks yang dicari dalam kedua PDF. | Tidak ada bagian “sinopsis” yang ditemukan. | BELUM TERVERIFIKASI. Perlu template sinopsis resmi D3 MI atau konfirmasi pembimbing/koordinator. Jangan menyamakan sinopsis dengan abstrak tanpa dasar. |
| Sitasi dan daftar pustaka | Contoh rujukan menggunakan pola nama keluarga dan tahun, misalnya “Kim (2002)” atau “(Kim, 2002)”; daftar kepustakaan disusun alfabetis dan lengkap. Kutipan lebih dari lima baris memiliki aturan tersendiri. (Hal. tercetak 49, 52–53; PDF 63, 66–68) | Daftar pustaka menampilkan jurnal, buku, DOI/URL dan urutan alfabetis. (PDF 49–50) | RESMI untuk hal yang disebut panduan; contoh hanya format yang dipakai mahasiswa tersebut. Gunakan gaya sitasi yang ditetapkan template D3 MI. Jangan menyebut APA 7 sebagai kewajiban jika tidak dinyatakan oleh pedoman yang berlaku. Pastikan semua sitasi punya entri lengkap. |
| Format halaman dan teks | A4 80 gsm; Times New Roman 12 pt; margin kiri 3,5 cm, atas/kanan/bawah 3 cm; isi 1,5 spasi; halaman awal angka Romawi kecil dan isi/penutup angka Arab. Judul bab 14 pt, kapital dan tebal; paragraf baru inden 1,25 cm; daftar kepustakaan satu spasi dengan inden lanjutan 1,25 cm. (Hal. tercetak 50–53; PDF 64–67) | Tampilan proposal memberi contoh visual, tetapi bukan otoritas format. (PDF 4 dst.) | RESMI dalam lingkup panduan FMIPA; penerapan untuk D3 MI PERLU KONFIRMASI. Terapkan hanya jika template khusus D3 tidak memberikan aturan berbeda. |
| Penomoran subbab | Subjudul memakai angka Arab dimulai dengan nomor bab, diikuti nomor urut; subsubjudul meneruskan tingkat penomoran. (Hal. tercetak 52; PDF 66) | Daftar isi menunjukkan BAB III, tetapi waktu/tempat serta alat/bahan ditulis 2.1 dan 2.2; isi BAB III juga menggunakan 2.1/2.2. (PDF 4–5, 19–20) | Koreksi pada naskah Anda: bagian dalam BAB III harus konsisten memakai nomor BAB III sesuai template, bukan 2.1/2.2. Nomor final mengikuti template D3 MI. |
| Rubrik seminar proposal | Bobot: masalah dan metodologi 25%; relevansi/kemutakhiran literatur 15%; penulisan 20%; penguasaan materi 20%; presentasi/komunikasi 20%. (Hal. tercetak 34; PDF 48) | Banyak diagram dan rincian fitur, tetapi itu bukan rubrik resmi. | RESMI menurut panduan FMIPA; kesamaan untuk D3 MI PERLU KONFIRMASI. Prioritaskan masalah dan metodologi; jangan menilai kualitas hanya dari jumlah gambar. |
| Administrasi seminar | Dalam prosedur panduan: naskah disampaikan ke penguji paling lambat empat hari kerja sebelum seminar; seminar maksimal 100 menit. (Hal. tercetak 11–12; PDF 26–27) | Proposal contoh tidak menggantikan ketentuan administratif resmi. | RESMI dalam panduan yang ditinjau; konfirmasi apakah batasan yang sama berlaku pada D3 MI dan periksa aturan terbaru dari koordinator. |

## 4. Rekomendasi Struktur Kerja untuk Proyek BMKG Sabang

Bagian ini adalah rekomendasi analitis, bukan klaim bahwa template D3 MI mewajibkan nama/urutan subbab persis seperti di bawah. Nomor dan urutan final harus diselaraskan dengan template khusus program studi.

### BAB I — Pendahuluan
- Latar belakang: konteks pertukaran informasi meteorologi, proses aktual yang sudah dikonfirmasi, masalah/kebutuhan berbukti, dan alasan sistem dikembangkan/disempurnakan.
- Rumusan masalah: persoalan yang dapat dijawab oleh pengembangan dan evaluasi aplikasi.
- Tujuan: tujuan yang berpasangan dengan rumusan masalah.
- Hipotesis: hanya bila desain penelitian memerlukannya dan template menghendakinya.
- Manfaat: kontribusi realistis bagi proses kerja/pengguna dan pembelajaran akademik.
- Batasan/ruang lingkup: tampilkan fitur yang sudah ada, yang akan disempurnakan, dan yang di luar penelitian sesuai struktur template.

Sebelum narasi final: kumpulkan deskripsi proses aktual yang boleh digunakan, bukti observasi/wawancara/dokumen yang diizinkan, cakupan fitur, dan hasil audit kode. Jangan mengarang bahwa proses saat ini lambat, manual, sering gagal atau tidak akurat.

### BAB II — Tinjauan Kepustakaan
Pilih hanya teori yang mendukung rancangan dan evaluasi. Kandidat setelah audit aplikasi:
- sistem informasi berbasis web;
- GTS/meteorologi dan terminologi/format pesan dari sumber resmi;
- mekanisme pengiriman yang benar-benar digunakan;
- teknologi aktual dari source code (mis. PHP/Laravel/database hanya jika terverifikasi);
- kebutuhan sistem, pemodelan proses dan data;
- validasi, penanganan status/error dan pencatatan aktivitas bila berada dalam ruang lingkup;
- metode pengembangan/perbaikan dan pengujian yang dipilih;
- penelitian terkait dan sintesis gap.

Susun matriks artikel sebelum sintesis. Jangan memasukkan API, Waterfall, UML, ERD, Black-Box, atau SUS sekadar karena ada di proposal contoh.

### BAB III — Metode Penelitian
Susun menurut struktur khusus D3 MI. Tetapkan:
- lokasi/waktu dan jadwal realistis;
- perangkat/perangkat lunak aktual beserta versi terverifikasi;
- teknik pengumpulan kebutuhan/bukti yang diizinkan;
- metode pengembangan/penyempurnaan yang menggambarkan keadaan aplikasi;
- analisis kebutuhan dan rancangan yang relevan;
- batas sistem dan hubungan dengan mekanisme pengiriman BMKG;
- prosedur uji, kondisi yang diharapkan, dan bukti yang akan dicatat;
- rincian biaya bila diwajibkan.

Karena aplikasi telah dikembangkan, dokumentasikan kondisi awal sebelum memilih metode. Jangan mendeskripsikan seluruh pekerjaan sebagai pembangunan dari nol apabila sebagian implementasi sudah ada.

## 5. Risiko yang Perlu Dihindari

1. Menganggap Panduan FMIPA otomatis menjadi template wajib D3 MI. Panduan itu resmi pada lingkupnya, tetapi relevansi aturan untuk program D3 perlu dipastikan.
2. Meniru daftar isi proposal teman secara mentah. Kesalahan 2.1/2.2 di bawah BAB III adalah contoh konkret yang perlu dihindari.
3. Menjadikan BAB II sebagai kamus teknologi. Tiap teori harus mendukung keputusan rancangan, metode, atau analisis.
4. Memakai Waterfall atau SUS hanya karena penulis contoh menggunakannya. Pemilihan harus cocok dengan keadaan aplikasi, tujuan, sumber daya dan persetujuan pembimbing.
5. Menulis hasil pada proposal seolah-olah sudah dilaksanakan. BAB III menjelaskan rancangan/prosedur; hasil aktual dilaporkan menurut struktur laporan akhir yang disahkan.
6. Menyamakan respons HTTP dengan penerimaan pesan. Jelaskan tingkat konfirmasi yang benar-benar ada pada mekanisme pengiriman.
7. Mengisi format sinopsis berdasarkan dugaan. Kedua PDF yang diperiksa tidak menjelaskan format sinopsis.
8. Memasukkan dokumen operasional sensitif tanpa izin. Contoh pesan dan bukti dalam dokumen akademik/AI harus disaring dan diizinkan.

## 6. Pertanyaan untuk Pembimbing/Koordinator D3 MI

Prioritas akademik:
1. Apa template resmi proposal khusus D3 Manajemen Informatika USK yang berlaku untuk tahun akademik dan jalur Proyek Akhir ini?
2. Apa format sinopsis yang wajib: struktur, panjang, bagian dan tata letak?
3. Bagaimana susunan BAB III untuk PA pengembangan sistem: implementasi/pengujian sebagai rencana di BAB III atau hasil aktual pada BAB IV? Apa ketentuan template D3?
4. Apakah hipotesis diperlukan untuk proyek pengembangan aplikasi ini?
5. Apakah rincian biaya penelitian wajib disertakan?
6. Gaya sitasi apa yang diwajibkan, dan apakah font, margin, spasi serta penomoran Panduan FMIPA 2024 berlaku sama untuk D3 MI?
7. Metode pengembangan dan pengujian apa yang diterima untuk aplikasi yang sudah dikembangkan sebagian?

Prioritas teknis/operasional (konfirmasi ke pihak BMKG yang berwenang):
8. Seberapa rinci mekanisme pengiriman yang sudah dipakai boleh dijelaskan dalam laporan?
9. Bukti respons/status apa yang boleh ditampilkan dalam screenshot, log, demonstrasi, atau lampiran?
10. Adakah lingkungan uji/simulasi untuk memeriksa alur pengiriman tanpa mengirim pesan operasional nyata?

## 7. Status Verifikasi dan Langkah Berikutnya

| Item | Status |
|---|---|
| Panduan TA-Tesis FMIPA 2024 dibaca pada bagian struktur, prosedur seminar, rubrik, sistematika, format dan sitasi | VERIFIED |
| Proposal seminar teman diperiksa pada daftar isi, BAB I–III, jadwal, alat/bahan, metode, diagram, implementasi dan pengujian | VERIFIED |
| Template khusus D3 MI di direktori proyek | NOT FOUND dalam pemeriksaan ini |
| Format sinopsis resmi D3 MI dari kedua PDF | NOT FOUND |
| Struktur final BAB I–III disahkan pembimbing | BELUM |
| Kesesuaian metode dengan source code aplikasi | BELUM; perlu workflow audit kode terpisah |

Urutan kerja yang disarankan:
1. Sertakan/konfirmasikan template D3 MI dan format sinopsis.
2. Jalankan workflow 03-audit-source-code-laravel.md secara read-only.
3. Lengkapi 00-ringkasan-proyek-template.md dengan fakta yang sudah diverifikasi.
4. Susun matriks literatur dan bukti masalah.
5. Setelah ketentuan dan kondisi aplikasi terkunci, susun kerangka BAB I, lalu BAB II dan BAB III.

## 8. Dokumen yang Ditinjau

- Panduan TA-Tesis FMIPA USK 2024: prosedur seminar proposal (hal. tercetak 11–12; PDF 26–27), rubrik proposal (hal. tercetak 34; PDF 48), struktur penelitian empiris (hal. tercetak 41; PDF 55), uraian BAB I–V (hal. tercetak 47–49; PDF 61–63), dan tata tulis/sitasi (hal. tercetak 50–56; PDF 64–70).
- Seminar Proposal M. Fadwa Maulyady: daftar isi (PDF 4–5), penelitian terkait (PDF 18), metode/jadwal/alat-bahan (PDF 19–22), diagram/rancangan (PDF 23–47), implementasi/pengujian/daftar pustaka (PDF 47–50).

Audit ini tidak mengubah source code Laravel dan tidak menetapkan aturan D3 MI sebagai final sebelum template khusus atau konfirmasi resmi tersedia.
