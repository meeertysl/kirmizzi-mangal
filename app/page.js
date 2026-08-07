import Link from "next/link";
import Logo from "@/components/Logo";
import { readDb } from "@/lib/db";

export const dynamic = "force-dynamic";

export default function HomePage() {
  const db = readDb();
  const s = db.settings;
  const categories = [...db.categories].sort((a, b) => a.order - b.order);
  const featured = db.products.filter((p) => p.available).slice(0, 6);

  return (
    <>
      <header className="site-header">
        <div className="site-header-inner">
          <Link href="/">
            <Logo size="sm" light />
          </Link>
          <nav className="site-nav">
            <a href="#hakkimizda">Hakkımızda</a>
            <a href="#lezzetler">Lezzetler</a>
            <a href="#iletisim">İletişim</a>
            <Link href="/menu" className="nav-cta">
              Menü
            </Link>
          </nav>
        </div>
      </header>

      <section className="hero">
        <Logo size="lg" light />
        <h1>{s.slogan}</h1>
        <p>{s.about}</p>
        <div className="hero-buttons">
          <Link href="/menu" className="btn btn-red">
            🔥 Menüyü İncele
          </Link>
          {s.phone && (
            <a href={`tel:${s.phone.replace(/\s/g, "")}`} className="btn btn-outline">
              📞 Rezervasyon
            </a>
          )}
        </div>
      </section>

      <section className="section" id="hakkimizda">
        <div className="container">
          <h2 className="section-title">
            Neden <span>Kırmızzı Mangal</span>?
          </h2>
          <p className="section-sub">Ustalık, tazelik ve ateşin lezzeti bir arada</p>
          <div className="features">
            <div className="feature-card">
              <div className="icon">🥩</div>
              <h3>Günlük Taze Et</h3>
              <p>Etlerimiz her gün taze olarak hazırlanır, özel baharatlarla marine edilir.</p>
            </div>
            <div className="feature-card">
              <div className="icon">🔥</div>
              <h3>Odun Ateşi</h3>
              <p>Tüm kebap ve ızgaralarımız geleneksel yöntemle odun ateşinde pişer.</p>
            </div>
            <div className="feature-card">
              <div className="icon">👨‍🍳</div>
              <h3>Usta Ellerden</h3>
              <p>Yılların tecrübesine sahip ustalarımızın elinden çıkan eşsiz lezzetler.</p>
            </div>
            <div className="feature-card">
              <div className="icon">🏠</div>
              <h3>Sıcak Ortam</h3>
              <p>Ailenizle ve sevdiklerinizle keyifle vakit geçirebileceğiniz samimi atmosfer.</p>
            </div>
          </div>
        </div>
      </section>

      <section className="section section-alt" id="lezzetler">
        <div className="container">
          <h2 className="section-title">
            Öne Çıkan <span>Lezzetler</span>
          </h2>
          <p className="section-sub">Menümüzden seçmeler</p>
          <div className="menu-grid">
            {featured.map((p) => (
              <div className="menu-card" key={p.id}>
                {p.image ? (
                  <img className="menu-card-img" src={p.image} alt={p.name} />
                ) : (
                  <div className="menu-card-img-placeholder">🍖</div>
                )}
                <div className="menu-card-body">
                  <div className="menu-card-top">
                    <h3>{p.name}</h3>
                    <span className="price">{p.price} ₺</span>
                  </div>
                  {p.description && <p className="desc">{p.description}</p>}
                </div>
              </div>
            ))}
          </div>
          <div style={{ textAlign: "center", marginTop: 36 }}>
            <Link href="/menu" className="btn btn-red">
              Tüm Menüyü Gör ({categories.length} kategori)
            </Link>
          </div>
        </div>
      </section>

      <section className="section" id="iletisim">
        <div className="container">
          <h2 className="section-title">
            Bize <span>Ulaşın</span>
          </h2>
          <p className="section-sub">Sizi ağırlamaktan mutluluk duyarız</p>
          <div className="info-rows">
            <div className="info-card">
              <div className="icon">📍</div>
              <h3>Adres</h3>
              {s.mapsUrl ? (
                <a href={s.mapsUrl} target="_blank" rel="noopener noreferrer">
                  {s.address}
                </a>
              ) : (
                <p>{s.address}</p>
              )}
            </div>
            <div className="info-card">
              <div className="icon">📞</div>
              <h3>Telefon</h3>
              <a href={`tel:${s.phone.replace(/\s/g, "")}`}>{s.phone}</a>
            </div>
            <div className="info-card">
              <div className="icon">🕐</div>
              <h3>Çalışma Saatleri</h3>
              <p>{s.hours}</p>
            </div>
            {s.instagram && (
              <div className="info-card">
                <div className="icon">📷</div>
                <h3>Instagram</h3>
                <a
                  href={`https://instagram.com/${s.instagram.replace("@", "")}`}
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  @{s.instagram.replace("@", "")}
                </a>
              </div>
            )}
          </div>
        </div>
      </section>

      <footer className="site-footer">
        <div className="footer-grid">
          <div>
            <Logo size="sm" light />
            <p style={{ marginTop: 12 }}>{s.slogan}</p>
          </div>
          <div>
            <h4>İletişim</h4>
            <p>{s.address}</p>
            <p>
              <a href={`tel:${s.phone.replace(/\s/g, "")}`}>{s.phone}</a>
            </p>
            <p>{s.hours}</p>
          </div>
          <div>
            <h4>Menü</h4>
            {categories.map((c) => (
              <p key={c.id}>
                <Link href={`/menu#${c.id}`}>{c.name}</Link>
              </p>
            ))}
          </div>
        </div>
        <div className="footer-bottom">
          © {new Date().getFullYear()} {s.name}. Tüm hakları saklıdır.
        </div>
      </footer>
    </>
  );
}
