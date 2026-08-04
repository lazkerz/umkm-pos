# UMKM POS

Aplikasi POS (kasir) + manajemen inventory + keuangan multi-toko untuk bisnis UMKM (awalnya dibuat untuk kedai kopi, sekarang digeneralisasi supaya bisa dipakai jenis UMKM apa saja — resep/BOM bersifat opsional per produk).

Stack: **Laravel 13 (PHP 8.3)**, Blade + Alpine.js + Tailwind CSS (Vite), MySQL, auth pakai Laravel Breeze.

## Daftar Isi

- [Konsep Utama](#konsep-utama)
- [Fitur](#fitur)
- [Cara Menjalankan di Lokal](#cara-menjalankan-di-lokal)
- [Login Demo](#login-demo)
- [Struktur & Arsitektur](#struktur--arsitektur)
- [Testing](#testing)
- [Branch & Alur Kerja](#branch--alur-kerja)
- [Yang Belum Dikerjakan / Backlog](#yang-belum-dikerjakan--backlog)

## Konsep Utama

Setiap **User** punya role `owner` atau `staff`:

- **Owner** bisa punya banyak **Store** (cabang/toko) dan dapat dashboard agregat semua tokonya.
- **Staff** terikat ke satu Store saja dan cuma bisa akses toko itu.

Hampir semua route ada di bawah `stores/{store}/...` dan dijaga dua middleware:
- `owner` — harus role owner (buat kelola toko, staff, distribusi stok, approve pengeluaran).
- `store.access` — owner cuma boleh akses toko miliknya, staff cuma boleh akses toko tempat dia kerja.

Karena route pakai model binding biasa (bukan scoped), setiap controller yang nyentuh resource anak toko (produk, kategori, stok, dst) **selalu** cek manual:
```php
abort_unless($resource->store_id === $store->id, 404);
```
Ini pola yang disengaja dan konsisten dipakai di seluruh codebase — kalau nambah resource baru yang scoped ke toko, ikuti pola yang sama.

## Fitur

- **Kasir (POS)** — pilih produk (search + filter kategori), keranjang reaktif (Alpine.js), pilih/tambah customer cepat tanpa reload, warning stok non-blocking kalau bahan baku kurang, checkout via AJAX (kalau gagal validasi, keranjang **tidak hilang** — beda dari versi lama yang full-page reload).
- **Manajemen stok** — bahan baku (`StockItem`) dengan audit trail tiap perubahan (`StockMovement`), resep/BOM per produk (`ProductRecipe`) — opsional, produk tanpa resep = item jual langsung/retail biasa.
- **Distribusi stok** Owner → Store, dengan pencatatan movement otomatis.
- **Promo** — persentase/nominal tetap, per channel (offline/online/keduanya), dengan rentang tanggal aktif.
- **Pengeluaran (Expense)** dengan alur approval — dibuat staff jadi `pending`, dibuat owner otomatis `approved`.
- **Laporan Laba Rugi** per toko & agregat semua toko, bisa export PDF & Excel.
- **Caching** — data referensi (produk, kategori, promo) di-cache per toko biar kasir nggak query database berulang tiap buka halaman. Auto-invalidate begitu ada perubahan data (lewat Model Observer).

## Cara Menjalankan di Lokal

```bash
# Clone & masuk folder
git clone https://github.com/lazkerz/umkm-pos.git
cd umkm-pos

# Setup otomatis: copy .env, generate key, migrate, install & build JS
composer run setup

# Jalankan dev server (Laravel serve + queue worker + log viewer + vite), satu terminal
composer run dev
```

Butuh: PHP 8.3+, Composer, Node.js, dan MySQL (database `umkm_kopi`, sesuaikan kredensial di `.env`).

Perintah lain yang sering dipakai:
```bash
php artisan migrate:fresh --seed   # reset DB + isi data unit global + data demo
php artisan test                   # jalankan semua test
php artisan test --filter=NamaTest # jalankan satu test tertentu
```

## Login Demo

Setelah `migrate:fresh --seed`:

| Role  | Email                          | Password   |
|-------|--------------------------------|------------|
| Owner | `owner@umkmkopi.test`          | `password` |
| Staff | `kasir.medan@umkmkopi.test`    | `password` |

## Struktur & Arsitektur

Ringkasan model domain (semua di `app/Models/`):

- `Store` — cabang/toko, dimiliki satu `User` (owner).
- `Category` / `Product` — menu/produk per toko.
- `Unit` — satuan; `store_id = null` berarti satuan global (dipakai semua toko), diisi berarti satuan custom milik toko itu.
- `StockItem` — stok bahan baku per toko, ubah `quantity` **cuma** lewat increment/decrement + catat `StockMovement`, jangan pernah edit langsung.
- `ProductRecipe` — BOM: berapa banyak `StockItem` dipakai untuk 1 unit `Product`.
- `Promotion`, `Expense`, `Customer`, `Transaction`/`TransactionItem` — cukup jelas dari namanya.

Alur checkout (`app/Http/Controllers/Store/TransactionController.php::store()`) adalah bagian paling kritis: dalam satu `DB::transaction()`, dia mengunci baris `StockItem` yang relevan (`lockForUpdate()`) dan validasi stok cukup **sebelum** menulis apa pun — supaya nggak oversell kalau ada dua transaksi jalan bersamaan. Kalau mau ubah logic checkout/stok, jaga urutan ini: lock → validasi → baru tulis.

Layer caching ada di `app/Support/StoreCache.php` (helper terpusat, cache-nya berupa array biasa, bukan objek Eloquent — supaya aman disimpan/dibaca ulang) dan `app/Observers/*.php` (auto-invalidate cache begitu data terkait berubah).

Dokumentasi lebih detail & konvensi ada di [`CLAUDE.md`](CLAUDE.md) — kalau kamu pakai Claude Code, file itu otomatis kebaca sebagai konteks project.

## Testing

```bash
php artisan test
```

Test domain (checkout, caching, customer quick-add) ada di `tests/Feature/Store/`. Test auth/profile bawaan Breeze ada beberapa yang belum disesuaikan dengan flow custom aplikasi ini (contoh: redirect setelah register beda dari default Breeze) — itu bukan bug baru, sudah begitu dari awal.

## Branch & Alur Kerja

- `master` — branch utama/stabil.
- `develop` — tempat kerja fitur/update sebelum masuk `master`.
- `athila` — branch kerja collaborator.

Alur yang disarankan: kerja di branch masing-masing → push → buka Pull Request ke `develop` → setelah direview, `develop` di-merge ke `master`.

## Yang Belum Dikerjakan / Backlog

- Redis untuk caching (saat ini masih pakai cache driver `database`, sudah cukup untuk skala sekarang, tapi bisa di-upgrade tanpa ubah kode).
- Held/parked order di kasir (simpan transaksi belum selesai, lanjut nanti).
- Struk PDF (sekarang baru bisa print langsung dari browser).
- Polish visual/UI secara keseluruhan — tampilan masih fungsional tapi belum "production-polished".
