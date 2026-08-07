"use client";

import { useCallback, useEffect, useState } from "react";

const EMPTY_PRODUCT = { name: "", description: "", price: "", categoryId: "", image: "", available: true };

export default function AdminApp() {
  const [tab, setTab] = useState("products");
  const [categories, setCategories] = useState([]);
  const [products, setProducts] = useState([]);
  const [settings, setSettings] = useState(null);
  const [message, setMessage] = useState(null); // {type, text}

  const flash = useCallback((type, text) => {
    setMessage({ type, text });
    setTimeout(() => setMessage(null), 3500);
  }, []);

  const load = useCallback(async () => {
    const [c, p, s] = await Promise.all([
      fetch("/api/categories").then((r) => r.json()),
      fetch("/api/products").then((r) => r.json()),
      fetch("/api/settings").then((r) => r.json()),
    ]);
    setCategories(c);
    setProducts(p);
    setSettings(s);
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  async function logout() {
    await fetch("/api/auth/logout", { method: "POST" });
    window.location.reload();
  }

  return (
    <div className="admin-shell">
      <div className="admin-topbar">
        <div className="title">
          KIRMIZZI <span>MANGAL</span> · Yönetim
        </div>
        <div style={{ display: "flex", gap: 10 }}>
          <a href="/menu" target="_blank" className="btn-sm btn-ghost" style={{ color: "#eee", borderColor: "#555", textDecoration: "none", lineHeight: "1.4" }}>
            Menüyü Gör ↗
          </a>
          <button className="btn-sm btn-primary" onClick={logout}>
            Çıkış
          </button>
        </div>
      </div>

      <main className="admin-main">
        {message && (
          <div className={message.type === "error" ? "error-msg" : "success-msg"}>{message.text}</div>
        )}

        <div className="admin-tabs">
          <button className={tab === "products" ? "admin-tab active" : "admin-tab"} onClick={() => setTab("products")}>
            🍖 Ürünler
          </button>
          <button className={tab === "categories" ? "admin-tab active" : "admin-tab"} onClick={() => setTab("categories")}>
            📂 Kategoriler
          </button>
          <button className={tab === "qr" ? "admin-tab active" : "admin-tab"} onClick={() => setTab("qr")}>
            🔳 QR Kod
          </button>
          <button className={tab === "settings" ? "admin-tab active" : "admin-tab"} onClick={() => setTab("settings")}>
            ⚙️ Ayarlar
          </button>
        </div>

        {tab === "products" && (
          <ProductsTab categories={categories} products={products} reload={load} flash={flash} />
        )}
        {tab === "categories" && (
          <CategoriesTab categories={categories} products={products} reload={load} flash={flash} />
        )}
        {tab === "qr" && <QrTab />}
        {tab === "settings" && settings && <SettingsTab settings={settings} reload={load} flash={flash} />}
      </main>
    </div>
  );
}

/* ---------------- Ürünler ---------------- */

function ProductsTab({ categories, products, reload, flash }) {
  const [form, setForm] = useState(EMPTY_PRODUCT);
  const [editingId, setEditingId] = useState(null);
  const [saving, setSaving] = useState(false);
  const [filter, setFilter] = useState("all");

  const set = (key) => (e) => setForm({ ...form, [key]: e.target.value });

  async function uploadImage(file) {
    const fd = new FormData();
    fd.append("file", file);
    const res = await fetch("/api/upload", { method: "POST", body: fd });
    const data = await res.json();
    if (!res.ok) {
      flash("error", data.error || "Yükleme başarısız");
      return null;
    }
    return data.url;
  }

  async function onImageChange(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    const url = await uploadImage(file);
    if (url) setForm((f) => ({ ...f, image: url }));
  }

  async function save(e) {
    e.preventDefault();
    if (!form.name.trim()) return flash("error", "Ürün adı gerekli");
    if (!form.categoryId) return flash("error", "Kategori seçiniz");
    setSaving(true);
    const res = await fetch("/api/products", {
      method: editingId ? "PUT" : "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ...form, id: editingId ?? undefined, price: Number(form.price) || 0 }),
    });
    setSaving(false);
    if (res.ok) {
      flash("success", editingId ? "Ürün güncellendi" : "Ürün eklendi");
      setForm(EMPTY_PRODUCT);
      setEditingId(null);
      reload();
    } else {
      const data = await res.json().catch(() => ({}));
      flash("error", data.error || "Kaydedilemedi");
    }
  }

  function startEdit(p) {
    setEditingId(p.id);
    setForm({
      name: p.name,
      description: p.description,
      price: String(p.price),
      categoryId: p.categoryId,
      image: p.image,
      available: p.available,
    });
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  async function remove(p) {
    if (!confirm(`"${p.name}" silinsin mi?`)) return;
    await fetch("/api/products", {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: p.id }),
    });
    flash("success", "Ürün silindi");
    reload();
  }

  async function toggleAvailable(p) {
    await fetch("/api/products", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: p.id, available: !p.available }),
    });
    reload();
  }

  const visible = filter === "all" ? products : products.filter((p) => p.categoryId === filter);
  const catName = (id) => categories.find((c) => c.id === id)?.name ?? "-";

  return (
    <>
      <div className="panel">
        <h2>{editingId ? "Ürünü Düzenle" : "Yeni Ürün Ekle"}</h2>
        <form onSubmit={save}>
          <div className="form-grid">
            <div className="field">
              <label>Ürün Adı *</label>
              <input value={form.name} onChange={set("name")} placeholder="Örn: Adana Kebap" />
            </div>
            <div className="field">
              <label>Kategori *</label>
              <select value={form.categoryId} onChange={set("categoryId")}>
                <option value="">Seçiniz...</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name}
                  </option>
                ))}
              </select>
            </div>
            <div className="field">
              <label>Fiyat (₺) *</label>
              <input type="number" min="0" step="0.01" value={form.price} onChange={set("price")} placeholder="250" />
            </div>
            <div className="field">
              <label>Görsel</label>
              <input type="file" accept="image/*" onChange={onImageChange} />
              {form.image && (
                <span className="hint">
                  Yüklendi ✓{" "}
                  <button
                    type="button"
                    style={{ border: "none", background: "none", color: "#c0392b", cursor: "pointer" }}
                    onClick={() => setForm((f) => ({ ...f, image: "" }))}
                  >
                    Kaldır
                  </button>
                </span>
              )}
            </div>
          </div>
          <div className="field" style={{ marginTop: 14 }}>
            <label>Açıklama</label>
            <textarea value={form.description} onChange={set("description")} placeholder="Ürün içeriği, servis detayı..." />
          </div>
          <div style={{ display: "flex", gap: 10, marginTop: 16, alignItems: "center" }}>
            <button className="btn-sm btn-primary" disabled={saving}>
              {saving ? "Kaydediliyor..." : editingId ? "Güncelle" : "Ekle"}
            </button>
            {editingId && (
              <button
                type="button"
                className="btn-sm btn-ghost"
                onClick={() => {
                  setEditingId(null);
                  setForm(EMPTY_PRODUCT);
                }}
              >
                Vazgeç
              </button>
            )}
          </div>
        </form>
      </div>

      <div className="panel">
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 16, flexWrap: "wrap", gap: 10 }}>
          <h2 style={{ marginBottom: 0 }}>Ürünler ({visible.length})</h2>
          <select value={filter} onChange={(e) => setFilter(e.target.value)} style={{ padding: "8px 12px", borderRadius: 8, border: "1.5px solid var(--border)" }}>
            <option value="all">Tüm kategoriler</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>
        </div>
        <div style={{ overflowX: "auto" }}>
          <table className="admin-table">
            <thead>
              <tr>
                <th></th>
                <th>Ürün</th>
                <th>Kategori</th>
                <th>Fiyat</th>
                <th>Durum</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {visible.map((p) => (
                <tr key={p.id}>
                  <td>
                    {p.image ? (
                      <img className="admin-thumb" src={p.image} alt="" />
                    ) : (
                      <div className="admin-thumb-placeholder">🍖</div>
                    )}
                  </td>
                  <td>
                    <strong>{p.name}</strong>
                    {p.description && (
                      <div className="hint" style={{ maxWidth: 300, overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                        {p.description}
                      </div>
                    )}
                  </td>
                  <td>{catName(p.categoryId)}</td>
                  <td>
                    <strong style={{ color: "var(--red)" }}>{p.price} ₺</strong>
                  </td>
                  <td>
                    <label className="switch" title={p.available ? "Satışta" : "Tükendi"}>
                      <input type="checkbox" checked={p.available} onChange={() => toggleAvailable(p)} />
                      <span className="slider"></span>
                    </label>
                  </td>
                  <td>
                    <div className="row-actions">
                      <button className="btn-sm btn-ghost" onClick={() => startEdit(p)}>
                        Düzenle
                      </button>
                      <button className="btn-sm btn-danger" onClick={() => remove(p)}>
                        Sil
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {visible.length === 0 && (
                <tr>
                  <td colSpan={6} style={{ textAlign: "center", color: "var(--gray)", padding: 30 }}>
                    Henüz ürün yok. Yukarıdan ekleyebilirsiniz.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}

/* ---------------- Kategoriler ---------------- */

function CategoriesTab({ categories, products, reload, flash }) {
  const [name, setName] = useState("");
  const [editingId, setEditingId] = useState(null);
  const [editName, setEditName] = useState("");

  async function add(e) {
    e.preventDefault();
    if (!name.trim()) return;
    const res = await fetch("/api/categories", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name }),
    });
    if (res.ok) {
      setName("");
      flash("success", "Kategori eklendi");
      reload();
    }
  }

  async function rename(id) {
    if (!editName.trim()) return;
    await fetch("/api/categories", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id, name: editName }),
    });
    setEditingId(null);
    flash("success", "Kategori güncellendi");
    reload();
  }

  async function remove(c) {
    const count = products.filter((p) => p.categoryId === c.id).length;
    const warn = count > 0 ? ` Bu kategorideki ${count} ürün de silinecek!` : "";
    if (!confirm(`"${c.name}" silinsin mi?${warn}`)) return;
    await fetch("/api/categories", {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: c.id }),
    });
    flash("success", "Kategori silindi");
    reload();
  }

  async function move(c, dir) {
    const sorted = [...categories].sort((a, b) => a.order - b.order);
    const idx = sorted.findIndex((x) => x.id === c.id);
    const swap = sorted[idx + dir];
    if (!swap) return;
    await Promise.all([
      fetch("/api/categories", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: c.id, order: swap.order }),
      }),
      fetch("/api/categories", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: swap.id, order: c.order }),
      }),
    ]);
    reload();
  }

  return (
    <>
      <div className="panel">
        <h2>Yeni Kategori</h2>
        <form onSubmit={add} style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
          <input
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Örn: Çorbalar"
            style={{ flex: 1, minWidth: 200, padding: "10px 13px", border: "1.5px solid var(--border)", borderRadius: 9 }}
          />
          <button className="btn-sm btn-primary">Ekle</button>
        </form>
      </div>

      <div className="panel">
        <h2>Kategoriler ({categories.length})</h2>
        <table className="admin-table">
          <thead>
            <tr>
              <th>Sıra</th>
              <th>Kategori</th>
              <th>Ürün Sayısı</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {[...categories]
              .sort((a, b) => a.order - b.order)
              .map((c, i, arr) => (
                <tr key={c.id}>
                  <td>
                    <div className="row-actions">
                      <button className="btn-sm btn-ghost" disabled={i === 0} onClick={() => move(c, -1)}>
                        ↑
                      </button>
                      <button className="btn-sm btn-ghost" disabled={i === arr.length - 1} onClick={() => move(c, 1)}>
                        ↓
                      </button>
                    </div>
                  </td>
                  <td>
                    {editingId === c.id ? (
                      <div style={{ display: "flex", gap: 8 }}>
                        <input
                          value={editName}
                          onChange={(e) => setEditName(e.target.value)}
                          style={{ padding: "6px 10px", border: "1.5px solid var(--border)", borderRadius: 8 }}
                          autoFocus
                        />
                        <button className="btn-sm btn-primary" onClick={() => rename(c.id)}>
                          Kaydet
                        </button>
                        <button className="btn-sm btn-ghost" onClick={() => setEditingId(null)}>
                          İptal
                        </button>
                      </div>
                    ) : (
                      <strong>{c.name}</strong>
                    )}
                  </td>
                  <td>{products.filter((p) => p.categoryId === c.id).length}</td>
                  <td>
                    <div className="row-actions">
                      <button
                        className="btn-sm btn-ghost"
                        onClick={() => {
                          setEditingId(c.id);
                          setEditName(c.name);
                        }}
                      >
                        Düzenle
                      </button>
                      <button className="btn-sm btn-danger" onClick={() => remove(c)}>
                        Sil
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
          </tbody>
        </table>
      </div>
    </>
  );
}

/* ---------------- QR ---------------- */

function QrTab() {
  const [origin, setOrigin] = useState("");

  useEffect(() => {
    setOrigin(window.location.origin);
  }, []);

  if (!origin) return null;
  const menuUrl = `${origin}/menu`;
  const qrSrc = `/api/qr?url=${encodeURIComponent(menuUrl)}`;

  return (
    <div className="panel">
      <h2>QR Menü Kodu</h2>
      <div className="qr-preview">
        <img src={qrSrc} alt="QR menü kodu" />
        <p className="hint">
          Bu kod <strong>{menuUrl}</strong> adresine yönlendirir.
        </p>
        <a href={qrSrc} download="kirmizzi-mangal-qr.png" className="btn btn-red">
          ⬇ QR Kodu İndir (PNG)
        </a>
        <p className="hint" style={{ maxWidth: 480, textAlign: "center" }}>
          İndirdiğiniz yüksek çözünürlüklü QR kodu masa kartlarına, broşürlere veya vitrine
          bastırabilirsiniz. Müşterileriniz telefon kamerasıyla okutarak menüye ulaşır. Not: Site
          yayına alındığında (gerçek alan adınızla) QR kodu tekrar indirmeyi unutmayın.
        </p>
      </div>
    </div>
  );
}

/* ---------------- Ayarlar ---------------- */

function SettingsTab({ settings, reload, flash }) {
  const [form, setForm] = useState(settings);
  const [pw, setPw] = useState({ currentPassword: "", newPassword: "", confirm: "" });
  const [saving, setSaving] = useState(false);

  const set = (key) => (e) => setForm({ ...form, [key]: e.target.value });
  const setP = (key) => (e) => setPw({ ...pw, [key]: e.target.value });

  async function saveInfo(e) {
    e.preventDefault();
    setSaving(true);
    const res = await fetch("/api/settings", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(form),
    });
    setSaving(false);
    if (res.ok) {
      flash("success", "Bilgiler kaydedildi");
      reload();
    } else {
      flash("error", "Kaydedilemedi");
    }
  }

  async function savePassword(e) {
    e.preventDefault();
    if (pw.newPassword !== pw.confirm) return flash("error", "Yeni şifreler eşleşmiyor");
    const res = await fetch("/api/settings", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ currentPassword: pw.currentPassword, newPassword: pw.newPassword }),
    });
    const data = await res.json().catch(() => ({}));
    if (res.ok) {
      flash("success", "Şifre değiştirildi");
      setPw({ currentPassword: "", newPassword: "", confirm: "" });
    } else {
      flash("error", data.error || "Şifre değiştirilemedi");
    }
  }

  return (
    <>
      <div className="panel">
        <h2>Restoran Bilgileri</h2>
        <form onSubmit={saveInfo}>
          <div className="form-grid">
            <div className="field">
              <label>Restoran Adı</label>
              <input value={form.name} onChange={set("name")} />
            </div>
            <div className="field">
              <label>Slogan</label>
              <input value={form.slogan} onChange={set("slogan")} />
            </div>
            <div className="field">
              <label>Telefon</label>
              <input value={form.phone} onChange={set("phone")} />
            </div>
            <div className="field">
              <label>WhatsApp (opsiyonel)</label>
              <input value={form.whatsapp} onChange={set("whatsapp")} placeholder="05xxxxxxxxx" />
            </div>
            <div className="field">
              <label>Instagram (opsiyonel)</label>
              <input value={form.instagram} onChange={set("instagram")} placeholder="kirmizimangal" />
            </div>
            <div className="field">
              <label>Çalışma Saatleri</label>
              <input value={form.hours} onChange={set("hours")} />
            </div>
            <div className="field">
              <label>Google Maps Linki (opsiyonel)</label>
              <input value={form.mapsUrl} onChange={set("mapsUrl")} placeholder="https://maps.app.goo.gl/..." />
            </div>
          </div>
          <div className="field" style={{ marginTop: 14 }}>
            <label>Adres</label>
            <textarea value={form.address} onChange={set("address")} />
          </div>
          <div className="field" style={{ marginTop: 14 }}>
            <label>Hakkımızda Yazısı</label>
            <textarea value={form.about} onChange={set("about")} />
          </div>
          <button className="btn-sm btn-primary" style={{ marginTop: 16 }} disabled={saving}>
            {saving ? "Kaydediliyor..." : "Kaydet"}
          </button>
        </form>
      </div>

      <div className="panel">
        <h2>Admin Şifresi</h2>
        <form onSubmit={savePassword}>
          <div className="form-grid">
            <div className="field">
              <label>Mevcut Şifre</label>
              <input type="password" value={pw.currentPassword} onChange={setP("currentPassword")} />
            </div>
            <div className="field">
              <label>Yeni Şifre</label>
              <input type="password" value={pw.newPassword} onChange={setP("newPassword")} />
            </div>
            <div className="field">
              <label>Yeni Şifre (Tekrar)</label>
              <input type="password" value={pw.confirm} onChange={setP("confirm")} />
            </div>
          </div>
          <button className="btn-sm btn-primary" style={{ marginTop: 16 }}>
            Şifreyi Değiştir
          </button>
          <p className="hint" style={{ marginTop: 10 }}>
            Varsayılan şifre <strong>admin123</strong>&apos;tür. Güvenlik için ilk girişte değiştirmeniz önerilir.
          </p>
        </form>
      </div>
    </>
  );
}
