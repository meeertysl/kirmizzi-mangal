# Kırmızzı Mangal — Web Sitesi & QR Menü

Kırmızzı Mangal restoranı için web sitesi, QR menü ve yönetim paneli.

## Özellikler

- **Web sitesi** (`/`) — Tanıtım sayfası: hakkımızda, öne çıkan lezzetler, iletişim bilgileri
- **QR Menü** (`/menu`) — Mobil öncelikli menü sayfası; kategori sekmeleri, ürün görselleri, fiyatlar, "tükendi" durumu
- **Yönetim Paneli** (`/admin`) — Şifreli panel:
  - Kategori ekleme / silme / yeniden adlandırma / sıralama
  - Ürün ekleme / düzenleme / silme, görsel yükleme, satışta/tükendi durumu
  - QR kod oluşturma ve PNG indirme
  - Restoran bilgileri (telefon, adres, saatler, Instagram, Google Maps) ve şifre değiştirme

## Kurulum

```bash
npm install
npm run dev
```

Site: http://localhost:3000
Admin paneli: http://localhost:3000/admin — varsayılan şifre: **admin123** (ilk girişte Ayarlar sekmesinden değiştirin).

## Yayına Alma (Production)

```bash
npm run build
npm start
```

Veriler `data/db.json` dosyasında, yüklenen görseller `public/uploads/` klasöründe tutulur.
Sunucu değiştirirken bu iki konumu yedeklemeniz yeterlidir.

> Not: Kalıcı dosya sistemi gerektirdiği için Vercel gibi salt-okunur (serverless) platformlar yerine
> bir VPS (ör. DigitalOcean, Hetzner) veya Railway/Render gibi kalıcı disk sunan platformlarda barındırın.

## Teknik

- Next.js (App Router) + React
- Veri: JSON dosyası (`data/db.json`) — harici veritabanı gerekmez
- Kimlik doğrulama: HMAC imzalı çerez, scrypt ile şifre özeti
- QR üretimi: `qrcode` paketi
