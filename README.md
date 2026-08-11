# Kırmızzı Mangal — Web Sitesi & QR Menü (PHP)

Kırmızzı Mangal restoranı için web sitesi, QR menü ve yönetim paneli.
**PHP + JSON depolama** ile çalışır; Hostinger Single/Premium gibi paylaşımlı
hosting paketlerinde ek kurulum gerektirmeden çalışacak şekilde tasarlanmıştır.

## Özellikler

- **Web sitesi** (`index.php`) — Tanıtım sayfası: hakkımızda, öne çıkan lezzetler, iletişim
- **QR Menü** (`menu.php`, kısa adres: `/menu`) — Mobil öncelikli menü; kategori sekmeleri, görseller, fiyatlar, "tükendi" durumu
- **Yönetim Paneli** (`/admin`) — Şifreli panel:
  - Kategori ekleme / silme / yeniden adlandırma / sıralama
  - Ürün ekleme / düzenleme / silme, görsel yükleme, satışta/tükendi
  - QR kod oluşturma ve yüksek çözünürlüklü PNG indirme
  - Restoran bilgileri ve şifre değiştirme

Varsayılan admin şifresi: **admin123** — canlıya aldıktan sonra ilk iş değiştirin!

## Yerel çalıştırma

```bash
php -S localhost:8000 router.php
```

Site: http://localhost:8000 · Admin: http://localhost:8000/admin

## Hostinger'a kurulum

1. hPanel → **Web Sitesi → Kontrol Paneli → Gelişmiş → GIT**
2. Depo: `https://github.com/meeertysl/kirmizzi-mangal.git`, dal: `master`, dizin: (boş = public_html)
3. **Deploy** edin. Sonraki güncellemelerde aynı ekrandan tekrar deploy edebilir
   veya otomatik dağıtım (webhook) açabilirsiniz.

Veriler `data/db.json` dosyasında, görseller `uploads/` klasöründe tutulur;
ikisi de repoda olmadığından (untracked) git dağıtımları bunları silmez.
Yedek almak için hPanel Dosya Yöneticisi'nden bu iki konumu indirmeniz yeterlidir.

## Teknik

- PHP 8+ (framework yok, tek başına çalışır)
- Veri: JSON dosyası (`data/db.json`) — veritabanı gerekmez
- Kimlik doğrulama: PHP session + `password_hash` (bcrypt), CSRF korumalı formlar
- QR üretimi: tarayıcıda `qrcode` JS kütüphanesi (CDN)
- `data/` ve `inc/` klasörleri `.htaccess` ile dışarıya kapalıdır
