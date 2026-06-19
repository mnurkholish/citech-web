# context.md

# 📝 DOKUMENTASI SPESIFIKASI TEKNIS & PANDUAN PENGEMBANGAN WEB CITECH

Dokumentasi ini berfungsi sebagai panduan acuan utama untuk pengembang (programmer) dan agen AI kodegen (Antigravity) dalam membangun sistem aplikasi perlombaan **CITECH**. Pastikan struktur menu, aturan validasi, pengondisian state, dan skema database diimplementasikan secara presisi demi menjaga konsistensi konteks proyek.

---

## 1. STRUKTUR MENU & SITEMAP
Arsitektur halaman web dibagi berdasarkan status autentikasi dan peran (Role) pengguna:

* **Public / Unauthenticated Page**
    * `Landing Page` (Informasi umum lomba)
    * `Login / Register Page`
* **Role: Peserta Lomba (Dashboard Peserta)**
    * `Beranda` (Menampilkan *Timeline* & *Guidebook*)
    * `Menu Tim`
        * Buat Data Tim
        * Detail / Lihat Data Tim (Melihat status seleksi, edit data tim, hapus anggota)
    * `Menu Profil`
        * Lihat & Ubah Profil
        * Ubah Password
* **Role: Admin (Dashboard Admin)**
    * `Beranda / Dashboard` (Statistik: Total Tim, Total Submission, Total Tim Verified, Persyaratan Belum Dikonfirmasi, Pembayaran Belum Dikonfirmasi)
    * `Konfirmasi Persyaratan` (Daftar & Detail konfirmasi berkas pendaftaran)
    * `Konfirmasi Pembayaran` (Daftar & Detail verifikasi bukti transfer)
    * `Tim Terdaftar` (Daftar tim lolos verifikasi berkas & fitur download data)
    * `Submission` (Daftar berkas submission proposal/karya & fitur download file)
    * `Atur Tanggal` (Manajemen *timeline* penjadwalan sistem)

---

## 2. ARSITEKTUR BASIS DATA (DATABASE SCHEMA & DBML)

Sistem menggunakan database relasional dengan struktur tabel dan tipe enumerasi (*enum*) sebagai berikut:

### 2.1 Tipe Data Enumerasi (Enum)
```sql
Enum status_seleksi_type {
  belum_seleksi
  penyisihan
  tidak_lolos_final
  final
}

Enum role_member_type {
  ketua
  anggota
}

Enum status_verifikasi_type {
  pending
  ditolak
  berhasil
}

Enum tahap_timeline_type {
  pendaftaran_b1
  pendaftaran_b2
  penyisihan
  final
  awarding
}

```
// =======================================================
// 2.2 Struktur Tabel & Relasi Antar Tabel (Skema Entitas)
// =======================================================

Table users {
  id_user integer [primary key, increment]
  email varchar [unique, not null]
  password varchar [not null]
  is_admin boolean [default: false]
  created_at timestamp [default: `now()`]
  updated_at timestamp
}

Table tim {
  id_tim integer [primary key, increment]
  id_user integer [ref: > users.id_user]
  nama_tim varchar [unique, not null]
  universitas varchar [not null]
  status_seleksi status_seleksi_type [default: 'belum_seleksi']
  batch integer
  created_at timestamp [default: `now()`]
  updated_at timestamp
}

Table member_tim {
  id_member integer [primary key, increment]
  id_tim integer [ref: > tim.id_tim]
  nama_peserta varchar [not null]
  nim_peserta varchar [unique, not null]
  role role_member_type [not null]
}

Table dokumen_registrasi {
  id_registrasi integer [primary key, increment]
  id_tim integer [ref: > tim.id_tim]
  link_file_registrasi varchar
  status_registrasi status_verifikasi_type [default: 'pending']
  catatan_registrasi text
  uploaded_at timestamp [default: `now()`]
}

Table pembayaran {
  id_pembayaran integer [primary key, increment]
  id_tim integer [ref: > tim.id_tim]
  bukti_pembayaran varchar
  status_pembayaran status_verifikasi_type [default: 'pending']
  catatan_pembayaran text
  uploaded_at timestamp [default: `now()`]
}

Table dokumen_submission {
  id_submission integer [primary key, increment]
  id_tim integer [ref: > tim.id_tim]
  link_file_submission varchar
  uploaded_at timestamp [default: `now()`]
}

Table timeline {
  id_timeline integer [primary key, increment]
  tanggal_mulai timestamp
  tanggal_selesai timestamp
  tahap tahap_timeline_type
  updated_by integer [ref: > users.id_user]
  created_at timestamp [default: `now()`]
  updated_at timestamp
}


## 3. ATURAN VALIDASI SISTEM & ALUR WORKFLOW

### 3.1 Autentikasi & Registrasi

#### Registrasi Akun
* **Validasi Null:** Validasi form wajib memeriksa agar tidak ada field kosong (`Data Null`). Jika ada, berikan pesan error secara spesifik: `"Kolom [Nama Data] tidak boleh kosong"`.
* **Validasi Format:** Format input data harus tervalidasi dengan benar. Jika salah format: `"Format [Nama Data] tidak sesuai format"`.
* **Validasi Kredensial:** Password dan konfirmasi password wajib identik, jika berbeda tampilkan pesan: `"Password tidak cocok"`.
* **Validasi Keunikan Data:** Periksa keunikan email pada database. Jika email sudah terdaftar: `"Email sudah terdaftar"`.

#### Login Akun
* **Metode Otentikasi:** Mendukung metode login konvensional via Email Form atau OAuth pihak ketiga (Google).
* **Penanganan Kegagalan:** Jika terjadi kegagalan pencocokan kredensial (salah email/password), sistem wajib memunculkan pesan: `"Email/Password salah"`.
* **Pengalihan Hak Akses (Routing):** Akun dengan flag `is_admin = true` secara otomatis akan diarahkan ke halaman Beranda/Dashboard Admin, sedangkan user biasa ke Dashboard Peserta.

---

### 3.2 Manajemen Tim (Sisi Peserta)
* **Pendaftaran Entitas:** Satu user peserta dapat mendaftarkan satu entitas kelompok pada tabel `tim` melalui pengisian form pembentukan tim.
* **Struktur Peran Keanggotaan:** Peserta dapat mengelola data keanggotaan kelompok dengan batasan pembagian peran (`role_member_type`) yang jelas, yakni Ketua Tim dan Anggota Tim.
* **Konfirmasi Penghapusan:** Mekanisme penghapusan anggota tim dari sistem wajib memicu modal pop-up konfirmasi keselamatan tindakan: `"Apakah anda yakin ingin menghapus anggota [Nama Anggota]?"`.

---

### 3.3 Berkas Persyaratan & Pembayaran
* **Upload Persyaratan:** Berkas administratif yang diunggah akan masuk ke database (`dokumen_registrasi`) dengan status inisiasi awal: `'pending'`.
* **Upload Bukti Bayar & Auto-Redirect WhatsApp:** Setelah berkas bukti pembayaran diunggah dan disimpan ke database, sistem wajib melakukan auto-redirect ke WhatsApp Bendahara UKM LAOS menggunakan template pesan teks berikut:
  > `"Selamat [Pagi/Siang/Malam] Kak, izin konfirmasi bahwa kami Tim [Nama Tim] dari [Nama Univ] sudah melakukan pembayaran untuk CITECH. Terima kasih"`

---

### 3.4 Validasi Alur Pengkondisian State (State Conditional Validation)
* **Modifikasi Berkas Pendaftaran:** Aksi unggah ulang berkas persyaratan pendaftaran kelompok (`Ganti File`) hanya diperbolehkan oleh sistem jika `status_registrasi` bernilai `'ditolak'` (`Status = Rejected`). Jika berstatus *pending* atau *confirmed*, tombol aksi wajib dikunci.
* **Pembatalan Bukti Pembayaran:** Peserta hanya diizinkan membatalkan unggahan bukti transfer mandiri selama data pembayaran di sistem masih berstatus `'pending'` (`Belum (Status = Pending)`). Jika status telah disetujui (`Sudah (Status = Confirmed)`), sistem mengunci fungsi pembatalan tersebut.
* **Pengumpulan Karya / Proposal Babak Penyisihan:** Formulir pengunggahan proposal penyisihan (`dokumen_submission`) baru terbuka di dasbor tim apabila seluruh syarat pendaftaran dan bukti pembayaran dinyatakan sah/lolos verifikasi admin. Ketika sukses menyimpan tautan berkas submission, munculkan pesan: `"Selamat! Proposal babak penyisihan telah berhasil diunggah"`.

---

### 3.5 Manajemen Verifikasi Admin (Sisi Admin)
* **Verifikasi Berkas & Finansial:** Admin dibekali opsi `setuju` atau `tolak` pada setiap entitas pendaftaran/pembayaran yang masuk. Apabila admin memilih opsi `tolak`, sistem mewajibkan admin mengisi formulir alasan penolakan berkas (`mengisi form alasan penolakan`) sebelum perubahan status disimpan ke database.
* **Pengaturan Garis Waktu (Timeline):** Setiap kali admin melakukan pembaruan penanggalan batas waktu perlombaan pada modul `Atur Tanggal`, sistem harus menampilkan pop-up interogasi konfirmasi tindakan: `"Apakah kamu yakin untuk mengubah tanggal submission?"`.