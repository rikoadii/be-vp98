# Teams Management - Image Upload Feature

## Perubahan yang Dilakukan

### 1. Struk### 3. Keamanan
- File `.htaccess` di folder uploads untuk mencegah eksekusi PHP
- Validasi ekstensi file yang diizinkan (JPG, JPEG, PNG, WEBP)
- Escape string untuk mencegah SQL injectionDatabase
Tambahkan kolom baru `profile_image` ke tabel `teams`:
```sql
ALTER TABLE teams ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL;
```

### 2. Fitur yang Ditambahkan

#### Upload Gambar Profile
- Menambahkan input file untuk upload gambar profile
- Mendukung format: JPG, JPEG, PNG, WEBP
- Gambar disimpan di folder `uploads/`
- Nama file dibuat unik menggunakan `uniqid()`

#### Preview Gambar (BARU!)
- **Live Preview**: Gambar langsung muncul saat memilih file
- **Preview Styling**: Gambar preview dengan border dan shadow yang menarik
- **Remove Button**: Tombol × di pojok kanan atas untuk menghapus preview
- **Current Image Display**: Menampilkan gambar existing saat edit dengan label yang jelas
- **Modal View**: Klik gambar di tabel untuk melihat dalam ukuran penuh

#### Tampilan Gambar
- Menampilkan thumbnail gambar di tabel teams (clickable untuk modal view)
- Preview gambar saat edit data dengan styling yang konsisten
- Placeholder icon jika tidak ada gambar

#### Pengelolaan File
- Menghapus gambar lama saat update dengan gambar baru
- Menghapus gambar saat delete data team
- Validasi format file dan error handling

### 3. Fitur Preview Gambar Detail

#### Preview saat Upload:
- Muncul secara real-time saat memilih file
- Ukuran 96x96px dengan rounded corners
- Border biru untuk memberikan feedback visual
- Tombol remove dengan styling modern

#### Modal Image Viewer:
- Klik gambar thumbnail di tabel untuk melihat full size
- Modal dengan overlay gelap
- Gambar responsive dengan max height 384px
- Judul modal menampilkan nama team
- Close button dan click-outside-to-close functionality

#### Styling CSS:
```css
.image-preview-container {
    position: relative;
    display: inline-block;
}

.image-preview-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    /* styling lainnya */
}
```

### 4. JavaScript Functions

#### `previewImage(input, previewId)`
- Membaca file yang dipilih menggunakan FileReader
- Menampilkan preview dengan styling yang konsisten
- Menambahkan tombol remove dengan posisi absolute

#### `clearImagePreview(inputId, previewId)`
- Menghapus file yang dipilih dari input
- Membersihkan area preview

#### `showImageModal(imageSrc, title)`
- Menampilkan modal dengan gambar full size
- Set title modal dengan nama team

#### `closeImageModal()`
- Menutup modal image viewer

### 5. Keamanan
- File `.htaccess` di folder uploads untuk mencegah eksekusi PHP
- Validasi ekstensi file yang diizinkan
- Escape string untuk mencegah SQL injection

### 6. User Experience Improvements

#### Form Experience:
- Preview langsung saat memilih file
- Visual feedback dengan border biru pada preview
- Easy remove dengan tombol × yang intuitif
- Label yang jelas untuk current image vs preview

#### Table Experience:
- Thumbnail yang clickable dengan hover effect
- Modal view untuk melihat gambar detail
- Consistent styling untuk semua gambar
- Placeholder icon untuk data tanpa gambar

### 7. Cara Penggunaan

1. **Menambah Team Baru:**
   - Isi form dengan data team
   - Pilih gambar profile (opsional)
   - Preview akan muncul otomatis
   - Klik "Add Team"

2. **Edit Team:**
   - Klik tombol "Edit" pada data team
   - Lihat current image di form
   - Upload gambar baru jika diperlukan (preview akan muncul)
   - Klik "Update Team"

3. **View Image:**
   - Klik thumbnail gambar di tabel
   - Modal akan menampilkan gambar full size
   - Klik X atau area luar modal untuk menutup

4. **Hapus Team:**
   - Klik tombol "Delete" 
   - Data dan gambar profile akan dihapus

### 8. Persyaratan
- Folder `uploads/` harus memiliki permission write
- Database harus sudah diupdate dengan kolom `profile_image`
- PHP harus mendukung upload file
- Browser harus mendukung FileReader API (semua browser modern)

### 9. Troubleshooting
- Jika preview tidak muncul, periksa JavaScript console untuk error
- Jika upload gagal, periksa permission folder uploads
- Pastikan ukuran file tidak melebihi limit PHP
- Periksa setting `upload_max_filesize` dan `post_max_size` di php.ini
- Jika modal tidak muncul, pastikan FontAwesome loaded untuk icon close
