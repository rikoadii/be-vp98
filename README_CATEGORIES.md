# Categories Management

## Fitur yang Tersedia

### 1. Create (Tambah Kategori)
- Form input untuk nama kategori
- Validasi data (tidak boleh kosong)
- Pesan sukses/error setelah proses

### 2. Read (Tampil Kategori)
- Daftar seluruh kategori dalam tabel
- Paginasi dan pencarian menggunakan DataTables
- Informasi ID dan nama kategori

### 3. Update (Edit Kategori)
- Form edit dengan data yang sudah diisi
- Validasi data (tidak boleh kosong)
- Pesan sukses/error setelah proses

### 4. Delete (Hapus Kategori)
- Konfirmasi sebelum penghapusan
- Penghapusan data dari database
- Pesan sukses/error setelah proses

## Struktur Database

Tabel `categories` memiliki struktur:

```sql
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Cara Penggunaan

1. **Menambah Kategori Baru:**
   - Masukkan nama kategori
   - Klik tombol "Add Category"

2. **Edit Kategori:**
   - Klik tombol "Edit" pada data yang ingin diubah
   - Edit nama kategori
   - Klik tombol "Update Category"

3. **Hapus Kategori:**
   - Klik tombol "Delete" pada data yang ingin dihapus
   - Konfirmasi penghapusan

## Integrasi dengan Modul Lain

Kategori yang sudah dibuat dapat digunakan untuk mengkategorikan produk, artikel, atau konten lainnya dalam aplikasi. Hubungkan dengan tabel lain menggunakan foreign key yang mengacu ke `id` dari tabel `categories`.

## Notes

- Kategori memiliki unique ID yang auto increment
- Nama kategori disimpan dalam kolom `categories_name`
- Tampilan tabel menggunakan DataTables untuk fitur pencarian dan pengurutan
- CRUD dilakukan dengan metode POST untuk keamanan
- Validasi data dilakukan di server side
