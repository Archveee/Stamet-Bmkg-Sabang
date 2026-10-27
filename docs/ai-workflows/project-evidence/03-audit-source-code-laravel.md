# Audit Source Code Laravel — Sistem GTS BMKG Sabang

Tanggal audit: 15 Oktober 2026
Mode: **read-only / audit statis**
Proyek: Pengembangan Sistem Informasi Pengelolaan serta Pengiriman Sandi Cuaca GTS dan Meteorologi Berbasis Web pada Stasiun Meteorologi BMKG Sabang.

## 1. Ringkasan eksekutif

1. Aplikasi menggunakan Laravel 11 (constraint `^11.0`), PHP `^8.2`, dan Filament 3; ini dapat didukung dari `composer.json`.
2. Modul pengelolaan pesan GTS yang utama ialah `GtsMessageResource`, form `GtsFormSchema`, halaman `CreateGtsMessage`, model `GtsMessage`, helper `GtsMessageHelper`, job `SendGtsMessageJob`, dan layanan `BmkgsatuApiService`.
3. Alur kode yang terlihat: input/manual atau komposisi dari data sinoptik → simpan pesan → sesuaikan payload → kirim HTTP ke endpoint API v21 BMKG Satu → simpan indikator pengiriman dan tampilkan notifikasi.
4. **Validasi isi sandi belum berjalan pada jalur yang diperiksa.** `GtsMessageVerifier::verifyMessage()` langsung mengembalikan array kosong sebelum logika validasi lainnya.
5. **Status terkirim bukan bukti yang cukup untuk mengklaim penerimaan akhir.** `SendGtsMessageJob` mengatur `is_sent = true` bila respons HTTP dinilai sukses; jika detail pesan dari API tidak tersedia, waktu kirim diisi waktu lokal saat itu.
6. Monitoring ketersediaan dan keterlambatan SINOPTIK/METAR/TAF tersedia secara sebagian. Perhitungan validitas sandi ditandai belum diimplementasikan, lalu data kolom `*_invalid_hours` disimpan sebagai `null`.
7. Ditemukan beberapa hal keamanan/operasional di luar alur utama GTS yang harus diprioritaskan sebelum aplikasi diekspos ke internet; detail dibatasi agar nilai token/rahasia tidak disalin ke laporan.
8. Dua file tes yang tersedia hanya tes contoh. Dua belas file PHP inti yang dicek dengan `php -l` tidak melaporkan syntax error. Tes fungsional/end-to-end tidak dijalankan.

## 2. Batas dan cara audit

- Membaca struktur, route, resource Filament, model, helper, service, job, command scheduler, migration terkait, policy, dan file tes yang relevan.
- Hanya melakukan lint sintaks PHP pada 12 file inti; tidak menjalankan migrasi, tidak mengubah source code aplikasi, tidak mengeksekusi perintah Artisan yang dapat memuat konfigurasi/akses database, dan tidak mengirim request ke BMKG Satu.
- Tidak membuka atau menyalin `.env`, log operasional, username/password, token, atau isi respons layanan eksternal.
- Kesimpulan di bawah adalah hasil inspeksi statis. Ketersediaan API, konfigurasi runtime, permission aktual di database, dan perilaku end-to-end masih perlu pengujian terkontrol serta konfirmasi operator.

**Definisi status:** VERIFIED = perilaku/struktur terlihat langsung pada kode (bukan berarti sudah dibuktikan berjalan di produksi); PARTIAL = sebagian ada namun ada batas/belum diverifikasi; PLANNED/INACTIVE = stub, TODO, atau belum diaktifkan; UNKNOWN = membutuhkan bukti runtime/dokumen/konfirmasi pihak BMKG.
## 3. Matriks audit modul dan alur

| Fitur/alur | File dan lokasi | Perilaku yang terbukti dari kode | Status | Risiko / pengujian yang diperlukan | Dampak untuk proposal |
|---|---|---|---|---|---|
| Form pengelolaan pesan | `app/Filament/Resources/GtsMessageResource.php`; `app/Filament/Components/GtsFormSchema.php` | Daftar pesan, jenis, jam data, teks sandi, waktu terkirim, selisih waktu, pencarian/filter tanggal, edit/hapus, salin sandi, dan form input tersedia. | VERIFIED (statis) | Uji hak akses setiap aksi dan validasi input form. | Dapat dijelaskan sebagai fitur pengelolaan, riwayat, dan pencarian pesan. |
| Pembuatan otomatis | `app/Console/Commands/GenerateGtsSinoptik.php`, `GenerateGtsMetar.php`, `GenerateGtsWxrev.php`, `GenerateGtsDch.php`; `app/Services/*Composer.php` | Generator menggunakan data `DataSinoptik`/helper untuk membuat SINOPTIK, METAR, WXREV, dan DCH, lalu memakai `firstOrCreate` berdasarkan waktu dan jenis. | PARTIAL | Perlu uji data lengkap/kosong, aturan pembulatan/kode sandi, duplikasi, serta kesesuaian SOP BMKG. | Nyatakan hanya jenis pesan yang benar-benar termasuk ruang lingkup dan diuji. |
| Jenis pesan lain | `GtsMessageHelper.php`; `GtsMessageResource/Pages/ListGtsMessages.php`; `routes/console.php` | Helper mengenali lebih banyak kode, termasuk SPECI, TAF, dan CLIMAT. Namun generator otomatis yang tampak di scheduler hanya SINOPTIK, METAR, WXREV, dan DCH; TAF tampak pada tab/monitoring dan input/pengelolaan. | PARTIAL | Pastikan jenis pesan mana yang dikembangkan, dihasilkan otomatis, hanya diinput manual, atau hanya disinkronkan. | Persempit rumusan masalah, batasan masalah, dan tujuan agar sesuai fitur nyata. |
| Autofill dari data sinoptik | `CreateGtsMessage.php` pada aksi `fillData`; `MetarComposer.php`, `SinoptikComposer.php`, `WxrevComposer.php`, `DchComposer.php` | Pengguna memilih jenis dan waktu; sistem mencari data sinoptik lalu mengisi teks sandi jika komposer mengembalikan hasil. | PARTIAL | Uji tipe/timestamp tidak cocok, data operasional tidak lengkap, serta keluaran sandi menurut contoh resmi. | Jelaskan sebagai bantuan penyusunan sandi; jangan klaim seluruh tipe otomatis dihasilkan. |
| Validasi sandi | `app/Filament/Resources/GtsMessageResource/Pages/CreateGtsMessage.php` (metode `afterValidate`); `app/Services/GtsMessageVerifier.php` baris sekitar 16–20 | Halaman memanggil verifier sebelum membuat record, tetapi verifier langsung `return []`; logika validasi sesudahnya tidak tercapai. | INACTIVE | Uji sandi valid, salah format, jenis tak dikenal, kode stasiun salah, karakter akhir, dan teks kosong/tidak lengkap setelah fungsi diaktifkan. | Jangan tulis “sistem memverifikasi validitas sandi” sebagai fitur aktif. Gunakan “pemeriksaan format oleh operator” kecuali validasi benar-benar diimplementasikan dan diuji. |
| Duplikasi pesan | `CreateGtsMessage.php` metode `beforeCreate` | Pembuatan dicegah jika teks `sandi_gts` persis sama dengan record yang sudah ada. | PARTIAL | Teks setara dengan spasi/baris berbeda atau payload sama dengan timestamp/jenis berbeda belum dibuktikan tertangani. | Deskripsikan sebagai pemeriksaan duplikasi teks persis, bukan pencegahan duplikasi menyeluruh. |
| Penyesuaian payload | `app/Helpers/GtsMessageHelper.php`, metode `mutateGTSMessageDataBeforeSent` (sekitar baris 58 dst.) | Menambahkan `=` bila belum ada; mendeteksi header WMO di baris pertama; jika belum ada, membentuk header dari konfigurasi dan timestamp; mengembalikan payload `sandi` dan `only_save=false`. | VERIFIED (statis) | Uji dengan/tanpa header, newline, tanda `=`, timestamp, semua tipe sandi, dan header konfigurasi kosong. | Dapat menjadi bagian penting dalam uraian proses pengiriman dan format payload. |
| Autentikasi API BMKG Satu | `app/Services/BmkgsatuApiService.php`, metode login/header/token | Base URL dan akun diambil dari helper pengaturan aplikasi; token disimpan di cache; request memakai header Bearer; login mencoba mengambil token dari beberapa kemungkinan kunci respons. | PARTIAL | Belum diuji terhadap lingkungan API; verifikasi masa berlaku token, rotasi, dan perlindungan log. | Jelaskan integrasi API yang ada, bukan mengklaim mekanisme autentikasi sudah diuji penuh. |
| Kirim pesan | `BmkgsatuApiService.php` metode `sendGtsMessage` (sekitar baris 347); `SendGtsMessageJob.php` metode `handle` | Mengirim POST ke endpoint API v21 GTS; request dibatasi timeout 30 detik dan melalui helper retry token bila HTTP 401/403. | PARTIAL | Belum ada request uji terhadap endpoint asli; uji melalui mock/fake HTTP dahulu. | Gunakan frasa “mengirim/menyerahkan payload ke API BMKG Satu” sampai bukti penerimaan akhir tersedia. |
| Pembaruan status pengiriman | `SendGtsMessageJob.php` sekitar baris 64–94; `GtsMessage` dan migration `create_gts_messages_table` | Jika respons POST sukses, sistem mengambil `@name` bila ada, mencoba mengambil detail, lalu memperbarui `is_sent` dan `timestamp_sent_data`. Timestamp lokal dipakai sebagai fallback ketika detail tidak tersedia. | PARTIAL | Respons sukses HTTP dan detail record API belum dibuktikan sebagai konfirmasi pengiriman sampai sistem tujuan. Uji respons tanpa `@name`, detail gagal, dan respons tak sukses. | Definisikan dengan cermat arti status “terkirim”; pisahkan status request/API dari status diterima sistem tujuan jika data konfirmasi tersedia. |
| Error dan percobaan ulang | `SendGtsMessageJob.php`; `BmkgsatuApiService.php`; `SendPendingGtsMessages.php` | Job mendefinisikan `tries=5` dan backoff 5 detik; respons 403 ditangani khusus dan berakhir tanpa menandai terkirim; command menangkap error per pesan agar pesan lain tetap diproses. Banyak pemanggil melakukan `dispatchSync`. | PARTIAL | Pemanggilan sinkron tidak boleh diasumsikan memperoleh retry seperti job worker async. Pesan gagal yang tetap `is_sent=0` berpotensi dicoba lagi oleh scheduler berikutnya. Uji efek samping dan idempotensi. | Laporkan penanganan error yang ada, tetapi jangan klaim mekanisme antrean/retry sudah andal sebelum diuji. |
| Jadwal pengiriman tertunda | `routes/console.php`; `app/Console/Commands/SendPendingGtsMessages.php` | Scheduler mendaftarkan `gts:send-pending-messages` pada menit 0–10 setiap jam. Command memilih pesan belum terkirim dengan waktu data tidak lebih dari sekarang dan masih dalam rentang sekitar 24 jam, lalu mengirimkannya secara sinkron. | VERIFIED (statis) | Perlu konfirmasi bahwa cron/scheduler benar-benar berjalan dan zona waktu sesuai UTC/SOP; uji batas rentang waktu serta pesan gagal. | Bisa dijelaskan sebagai mekanisme pemrosesan pesan tertunda; jadwal produksi perlu dibuktikan dari konfigurasi server/log yang diizinkan. |
| Mode Auto/Manual | `SentAndCreationModeSettingFormSchema.php`; `CreateGtsMessage.php`; `PendingGtsMessages.php`; `SendPendingGtsMessages.php` | UI memiliki pengaturan mode dan offset serta widget menampilkan mode. Namun command pengirim pesan tertunda yang diperiksa memfilter berdasarkan status/waktu tanpa membaca `*_sent_mode`. | PARTIAL / INDIKASI KETIDAKSESUAIAN | Konfirmasi apakah mode manual memang harus menghentikan pengiriman otomatis; jika iya, perlu perbaikan tersendiri dan pengujian regresi. | Jangan klaim konfigurasi Auto/Manual telah memengaruhi seluruh jalur pengiriman sebelum dikonfirmasi. |
| Riwayat/sinkronisasi | `FetchGtsMessagesJob.php`; `BmkgsatuApiService::getGtsMessages` | Mengambil record GTS dari API menurut jenis/rentang waktu dan menyimpannya/memperbarui record lokal; record hasil sinkronisasi diset sebagai terkirim. | PARTIAL | Aturan identifikasi record yang sudah ada harus diuji; sinkronisasi bukan bukti log end-to-end yang lengkap. | Bisa menjadi fitur riwayat dan sinkronisasi, dengan batas yang dijelaskan. |
| Monitoring keterlambatan/ketersediaan | `FetchGtsMonitoringDataJob.php`; `GtsMonitoring.php`; `GtsDailyMonitoringTable.php` | Menghitung jam hilang dan keterlambatan untuk SINOPTIK, METAR, dan TAF berdasarkan daftar jam harapan; UI menyediakan persentase terkirim dan tepat waktu. | PARTIAL | Pastikan jumlah jam yang diharapkan dan ambang 10 menit cocok dengan SOP resmi, termasuk hari/jenis khusus. | Dapat menjadi subfitur monitoring; rumus perlu dijelaskan dengan sumber operasional. |
| Monitoring validitas | `FetchGtsMonitoringDataJob.php`; `GtsMonitoring.php` | Komentar kode menyatakan pemeriksaan validitas belum dibuat; proses menyimpan kolom jam salah sandi sebagai `null`; persentase “Valid” mengembalikan `null` jika datanya bukan array. | INACTIVE | Jangan gunakan indikator “Valid” sebagai hasil ukur penelitian hingga logika dan data uji tersedia. | Hilangkan dari klaim keberhasilan atau jadikan rekomendasi pengembangan lanjutan. |
| Autentikasi dan izin | `AdminPanelProvider.php`; `User.php`; `GtsMessagePolicy.php`; `GtsMonitoringPolicy.php`; `AppServiceProvider.php` | Panel memiliki halaman login; user menggunakan role/permission; policy GTS mendefinisikan kemampuan view/create/update/delete. Metode `canAccessPanel` mengizinkan user yang telah terautentikasi masuk ke panel, sementara izin fitur bergantung pada policy/role. | PARTIAL | Tidak memeriksa isi database role/permission maupun akses langsung tiap aksi; perlu uji role minimum dan role admin. | Jelaskan autentikasi serta hak akses hanya dengan cakupan yang bisa dibuktikan. |
## 4. Temuan risiko dan konsistensi yang perlu diprioritaskan

### Risiko tinggi — route pengunduhan cadangan

- Pada `routes/web.php` (sekitar baris 80–95), route unduh cadangan menerima token lewat query parameter dan membandingkannya dengan nilai token yang ditanam langsung di source code. Jika route dapat dicapai dari jaringan yang tidak tepercaya, ini merupakan risiko akses terhadap berkas backup database.
- **Nilai token sengaja tidak disalin ke laporan.** Jangan memasukkannya dalam proposal, tangkapan layar, laporan bug, atau commit publik.
- Rekomendasi keamanan terpisah dari penulisan proposal: cabut/rotasi token yang tertanam, pindahkan rahasia ke konfigurasi aman, batasi route dengan autentikasi/otorisasi yang sesuai, hindari token pada URL/query string, dan periksa riwayat repo/deployment dengan petugas berwenang. Jangan melakukan rotasi atau perubahan saat audit tanpa izin.

### Risiko menengah — verifikasi sertifikat TLS

- Dua route proxy CCTV di `routes/web.php` mengatur agar verifikasi sertifikat SSL/TLS tidak dilakukan.
- Hal ini melemahkan pemeriksaan identitas server tujuan dan perlu ditinjau sebelum penggunaan pada jaringan yang tidak tepercaya. Bukan bagian inti pengiriman GTS, tetapi relevan pada audit aplikasi secara umum.

### Risiko proses dan kualitas data

- `GtsMessageVerifier` tidak aktif, meskipun halaman pembuatan memanggilnya sebelum record dibuat.
- Data monitoring validitas belum dihitung; UI memiliki kolom “Valid” tetapi sumber nilainya masih kosong.
- Status `is_sent` ditentukan dari respons HTTP sukses, dan timestamp dapat memakai waktu lokal sebagai fallback jika detail API tidak terbaca.
- Penanganan status 403 setelah refresh token tidak menandai pesan berhasil. Karena command terjadwal memilih semua pesan yang masih belum terkirim pada rentang waktu, record yang gagal dapat dicoba lagi pada siklus berikutnya.
- Pengaturan Auto/Manual ditampilkan di UI, tetapi command kirim pesan tertunda yang diperiksa tidak mengevaluasi flag mode.
- Komposer DCH memberi notifikasi saat beberapa data penting kosong tetapi tidak berhenti sesudah notifikasi; keluaran dengan input tak lengkap perlu diuji dan ditinjau.
- `SendGtsMessageJob` memiliki properti retry, tetapi beberapa jalur utama memanggilnya melalui `dispatchSync`. Jangan menyatakan retry otomatis job queue telah terbukti bekerja dari deklarasi properti semata.
- Data/konfigurasi aktual pada database, scheduler server, worker, URL tujuan, dan permission belum diperiksa.

## 5. Pengujian yang disarankan (tanpa mengirim pesan nyata)

Semua pengujian integrasi hendaknya memakai HTTP fake/mock, database uji, dan payload contoh yang telah disetujui. Jangan gunakan sandi operasional aktif untuk pengujian eksperimental dan jangan kirim ke API produksi.

| ID | Kasus uji | Hasil yang diharapkan |
|---|---|---|
| T-01 | Simpan sandi dengan format yang benar untuk setiap tipe dalam ruang lingkup | Jenis, timestamp, isi sandi dan record tersimpan sesuai aturan yang disepakati. |
| T-02 | Sandi kosong, jenis tak dikenal, header tidak cocok, terminator hilang | Sistem menolak atau menunjukkan peringatan yang benar setelah validator sungguhan diimplementasikan; pada keadaan kode sekarang, verifier tidak memblokir berdasarkan isi. |
| T-03 | Payload memiliki/tidak memiliki header WMO serta tanda akhir `=` | Payload akhir terbentuk tepat seperti kontrak API dan tidak mengubah teks secara tak terduga. |
| T-04 | API mengembalikan HTTP sukses dengan dan tanpa `@name`; endpoint detail sukses/gagal | Status lokal dan waktu kirim sesuai definisi status yang disepakati; fallback dicatat dengan jelas. |
| T-05 | API mengembalikan 400, 401, 403, 500, timeout, dan respons non-JSON | Pesan gagal tidak ditandai terkirim; notifikasi/log cukup informatif tanpa kredensial; perilaku refresh/retry konsisten. |
| T-06 | Pengiriman gagal lalu scheduler memproses siklus berikutnya | Tidak terjadi pengiriman ganda yang tidak diinginkan; jumlah percobaan dan kondisi henti jelas. |
| T-07 | Mode Auto dibanding Manual | Mode manual tidak dikirim otomatis jika itu adalah persyaratan operasional; jika sebaliknya, definisinya harus ditulis dan disetujui. |
| T-08 | Timestamp sebelum, tepat pada, sesudah waktu jadwal; zona UTC; batas rentang 24 jam | Waktu pengiriman sesuai keputusan operasional dan konsisten di UI/database/API. |
| T-09 | Data composer lengkap dan tidak lengkap (terutama DCH/WXREV) | Keluaran mengikuti contoh/sandi acuan yang disetujui; data wajib yang hilang tidak diam-diam menghasilkan sandi yang tampak sah. |
| T-10 | Pesan sama, pesan sama dengan perbedaan whitespace, dan jenis/waktu sama dengan teks berbeda | Aturan duplikasi terdokumentasi dan tidak memblokir pesan sah tanpa sengaja. |
| T-11 | Monitoring harian dengan jam hilang, terkirim tepat waktu, dan terlambat | Persentase cocok dengan data uji manual dan ambang resmi. |
| T-12 | Role/permission pembuat, operator, hanya pembaca, dan admin | Tiap halaman/aksi dibatasi sesuai matriks izin yang disetujui; tidak hanya mengandalkan menu tersembunyi. |
| T-13 | Route backup dan route proxy pada konfigurasi uji | Akses backup ditolak untuk pihak tak berwenang; verifikasi TLS aktif; tidak ada rahasia pada URL atau log. |

### Kondisi tes saat audit
- Sintaks PHP: **12 berkas inti lulus `php -l`**.
- Tes repository yang ditemukan: `tests/Feature/ExampleTest.php` dan `tests/Unit/ExampleTest.php`, keduanya masih berupa tes contoh, belum mencakup alur GTS.
- PHPUnit tidak dijalankan. Dalam mode audit non-invasif, boot aplikasi/test bisa memuat route console dan helper pengaturan yang mengakses skema/database, sehingga risiko akses ke konfigurasi operasional harus dihindari sebelum lingkungan pengujian dipastikan aman.
## 6. Fakta yang dapat dipakai dalam proposal (setelah dikonfirmasi pembimbing)

1. Sistem web dibangun dengan Laravel 11/PHP dan antarmuka admin Filament.
2. Sistem mengelola record pesan GTS yang memuat jenis, isi sandi, timestamp data, indikator `is_sent`, serta timestamp pengiriman.
3. Sistem memiliki fungsi menyusun beberapa jenis sandi dari data sinoptik dan/atau menerima input sandi melalui form.
4. Pengiriman memakai integrasi API v21 BMKG Satu dengan autentikasi token Bearer yang disimpan dalam cache aplikasi.
5. Pesan yang menunggu jadwal ditampilkan dalam widget; command scheduler memproses pesan yang belum terkirim ketika waktu pengamatan sudah tiba dan masih berada dalam rentang yang ditetapkan command.
6. Aplikasi memiliki mekanisme sinkronisasi riwayat GTS dan monitoring jam hilang/terlambat untuk SINOPTIK, METAR, dan TAF.
7. Uji yang paling defensible untuk laporan proyek akhir: uji black-box untuk fungsi yang sudah aktif, pengujian payload dengan HTTP fake, uji hak akses, dan evaluasi pemakai bila disetujui pembimbing. Hasil uji belum tersedia dari audit ini; jangan tulis angka hasil sebelum tes dilaksanakan.

### Batas klaim yang harus dipertahankan
- Jangan menyatakan validator sandi aktif.
- Jangan menyatakan semua tipe pesan mempunyai generator otomatis.
- Jangan menyatakan indikator “Valid” telah dihitung dengan benar.
- Jangan menyamakan HTTP 2xx atau nilai `is_sent` dengan kepastian diterima/diproses oleh sistem tujuan di luar bukti yang disediakan API.
- Jangan mengklaim fitur otomatis, scheduler produksi, tingkat keberhasilan, penghematan waktu, atau kepuasan operator tanpa hasil uji/data yang terukur.
## 7. Informasi yang masih harus dikonfirmasi

### Kepada pembimbing/koordinator Proyek Akhir D3 MI USK
1. Apakah sistem yang sudah ada ini akan dinilai sebagai pengembangan lanjutan, implementasi yang telah dibuat, atau prototipe yang harus dibatasi ulang?
2. Apa format resmi proposal, sinopsis, struktur BAB I–III, metode wajib, dan standar referensi untuk D3 Manajemen Informatika? Audit pedoman sebelumnya mencatat panduan FMIPA yang tersedia berlingkup Program Sarjana; kesesuaiannya untuk D3 MI belum dipastikan.
3. Metode evaluasi minimum apa yang disyaratkan—misalnya black-box, uji penerimaan pengguna, atau metode lain yang disepakati?
4. Fitur inti yang wajib masuk ruang lingkup: input/pengelolaan, generator sandi tertentu, pengiriman API, riwayat, monitoring, atau semuanya?

### Kepada pihak BMKG Sabang/administrator yang berwenang
1. Apa alur resmi dari sandi disusun sampai dinyatakan terkirim/diterima, termasuk sumber data dan siapa yang memverifikasi isinya?
2. Bagaimana definisi operasional status sukses: respons API, record tersimpan di BMKG Satu, pesan diteruskan oleh sistem switching, atau konfirmasi lain?
3. Tipe sandi mana yang digunakan aktif oleh stasiun, mana yang otomatis, dan mana yang manual; bagaimana jadwal/offset serta zona waktu yang sah?
4. Apakah mode Manual dimaksudkan untuk mencegah pengiriman otomatis? Bagaimana SOP menangani retry, pesan gagal, duplikasi, dan pengiriman ulang?
5. Apa contoh sandi resmi yang boleh digunakan sebagai fixture tes non-produksi, dan metrik monitoring apa yang ditetapkan SOP?
6. Siapa berwenang meninjau dan memperbaiki risiko route backup, token yang tertanam, dan verifikasi TLS proxy?

## 8. Rekomendasi langkah berikutnya

1. Simpan audit ini sebagai dasar faktual; jangan mengubah source code sampai temuan dibahas dan ada izin.
2. Audit kelayakan/tujuan sistem bersama pembimbing: pilih jenis pesan dan modul yang masuk ruang lingkup, dan tetapkan arti “berhasil terkirim”.
3. Siapkan matriks kebutuhan → fitur yang ada → bukti file → kasus uji → hasil aktual; jalankan tes hanya pada lingkungan uji dengan data contoh dan izin.
4. Setelah ruang lingkup disepakati, lanjutkan ke matriks literatur dan rancangan BAB I–III menggunakan fakta di laporan ini, bukan klaim fitur yang belum aktif.
5. Tangani temuan keamanan melalui proses perubahan terpisah dengan otorisasi administrator; pekerjaan proposal tidak boleh menutupi risiko akses backup atau pengaturan TLS.

## 9. Berkas yang diperiksa (referensi cepat)

- `app/Filament/Resources/GtsMessageResource.php`
- `app/Filament/Resources/GtsMessageResource/Pages/CreateGtsMessage.php`
- `app/Filament/Resources/GtsMessageResource/Pages/ListGtsMessages.php`
- `app/Filament/Components/GtsFormSchema.php`
- `app/Filament/Components/SentAndCreationModeSettingFormSchema.php`
- `app/Filament/Widgets/PendingGtsMessages.php`
- `app/Filament/Widgets/MonitoringGtsMessages.php`
- `app/Filament/Resources/GtsMonitoringResource.php` dan `app/Filament/Widgets/GtsDailyMonitoringTable.php`
- `app/Jobs/SendGtsMessageJob.php`, `FetchGtsMessagesJob.php`, `FetchGtsMonitoringDataJob.php`
- `app/Services/BmkgsatuApiService.php`, `GtsMessageVerifier.php`, dan komposer terkait
- `app/Helpers/GtsMessageHelper.php`
- `app/Console/Commands/SendPendingGtsMessages.php` dan empat generator GTS
- `app/Models/GtsMessage.php`, `GtsMonitoring.php`, `User.php`
- `database/migrations/2025_01_07_025730_create_gts_messages_table.php`, `2025_05_18_122339_create_gts_monitoring_table.php`
- `routes/web.php`, `routes/console.php`, `app/Providers/Filament/AdminPanelProvider.php`, `app/Policies/GtsMessagePolicy.php`
- `composer.json`, `phpunit.xml`, `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`

**Kesimpulan:** Kode menunjukkan kerangka pengelolaan, komposisi, pengiriman melalui API, riwayat, dan sebagian monitoring GTS. Sebelum dijadikan klaim ilmiah di proposal, batas validasi, cakupan tipe sandi, definisi status terkirim, mode pengiriman, dan hasil pengujian harus dinyatakan jujur dan dikonfirmasi.
