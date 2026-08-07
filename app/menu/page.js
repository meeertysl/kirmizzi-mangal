import Link from "next/link";
import Logo from "@/components/Logo";
import { readDb } from "@/lib/db";
import CategoryTabs from "./CategoryTabs";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Menü | Kırmızzı Mangal",
  description: "Kırmızzı Mangal QR menü - kebaplar, ızgaralar, mezeler ve daha fazlası.",
};

export default function MenuPage() {
  const db = readDb();
  const categories = [...db.categories].sort((a, b) => a.order - b.order);
  const products = [...db.products].sort((a, b) => a.order - b.order);

  const sections = categories
    .map((c) => ({ ...c, items: products.filter((p) => p.categoryId === c.id) }))
    .filter((c) => c.items.length > 0);

  return (
    <>
      <div className="menu-header">
        <Link href="/">
          <Logo size="sm" light />
        </Link>
        <p style={{ marginTop: 6, fontSize: "0.9rem", color: "#c9c1b6" }}>{db.settings.slogan}</p>
      </div>

      <CategoryTabs categories={sections.map(({ id, name }) => ({ id, name }))} />

      <div className="container" style={{ paddingBottom: 60 }}>
        {sections.map((section) => (
          <section className="menu-section" id={section.id} key={section.id}>
            <h2>{section.name}</h2>
            <div className="menu-list">
              {section.items.map((p) => (
                <div className={p.available ? "menu-item" : "menu-item unavailable"} key={p.id}>
                  {p.image ? (
                    <img className="menu-item-img" src={p.image} alt={p.name} />
                  ) : (
                    <div className="menu-item-img-placeholder">🍖</div>
                  )}
                  <div className="menu-item-body">
                    <h3>{p.name}</h3>
                    {p.description && <p className="desc">{p.description}</p>}
                    {!p.available && <span className="badge-unavailable">Tükendi</span>}
                  </div>
                  <span className="price">{p.price} ₺</span>
                </div>
              ))}
            </div>
          </section>
        ))}

        {sections.length === 0 && (
          <p style={{ textAlign: "center", padding: "60px 0", color: "var(--gray)" }}>
            Menü henüz hazırlanıyor, çok yakında burada!
          </p>
        )}
      </div>

      <footer className="site-footer" style={{ marginTop: 0 }}>
        <div className="footer-bottom" style={{ borderTop: "none", marginTop: 0, paddingTop: 0 }}>
          © {new Date().getFullYear()} {db.settings.name} · {db.settings.phone}
        </div>
      </footer>
    </>
  );
}
