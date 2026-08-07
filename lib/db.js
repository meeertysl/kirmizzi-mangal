import fs from "fs";
import path from "path";
import crypto from "crypto";

const DATA_DIR = path.join(process.cwd(), "data");
const DB_PATH = path.join(DATA_DIR, "db.json");

const DEFAULT_DB = {
  settings: {
    name: "Kırmızzı Mangal",
    slogan: "Ateşin ve lezzetin buluştuğu yer",
    about:
      "Kırmızzı Mangal olarak en taze etleri, ustalıkla hazırlanan mezeleri ve geleneksel mangal lezzetlerini sofranıza getiriyoruz. Odun ateşinde, ustaların elinde pişen kebaplarımızla damaklarda iz bırakıyoruz.",
    phone: "0500 000 00 00",
    whatsapp: "",
    address: "Adres bilgisini admin panelden güncelleyiniz",
    instagram: "",
    hours: "Her gün 11:00 - 23:00",
    mapsUrl: "",
    adminPassword: null, // scrypt hash, null = varsayılan "admin123"
  },
  categories: [
    { id: "kebaplar", name: "Kebaplar", order: 1 },
    { id: "izgaralar", name: "Izgaralar", order: 2 },
    { id: "mezeler", name: "Mezeler & Salatalar", order: 3 },
    { id: "icecekler", name: "İçecekler", order: 4 },
    { id: "tatlilar", name: "Tatlılar", order: 5 },
  ],
  products: [
    {
      id: "adana-kebap",
      categoryId: "kebaplar",
      name: "Adana Kebap",
      description: "Zırh ile çekilmiş dana eti, közlenmiş domates ve biber ile",
      price: 250,
      image: "",
      available: true,
      order: 1,
    },
    {
      id: "urfa-kebap",
      categoryId: "kebaplar",
      name: "Urfa Kebap",
      description: "Acısız, özel baharatlarla hazırlanmış kebap",
      price: 250,
      image: "",
      available: true,
      order: 2,
    },
    {
      id: "kusbasi-izgara",
      categoryId: "izgaralar",
      name: "Kuşbaşı Izgara",
      description: "Marine edilmiş dana kuşbaşı, mangalda",
      price: 300,
      image: "",
      available: true,
      order: 1,
    },
    {
      id: "ezme",
      categoryId: "mezeler",
      name: "Ezme",
      description: "Acılı, taze domates ve biberle",
      price: 60,
      image: "",
      available: true,
      order: 1,
    },
    {
      id: "ayran",
      categoryId: "icecekler",
      name: "Ayran",
      description: "",
      price: 30,
      image: "",
      available: true,
      order: 1,
    },
    {
      id: "kunefe",
      categoryId: "tatlilar",
      name: "Künefe",
      description: "Antep fıstıklı, sıcak servis",
      price: 120,
      image: "",
      available: true,
      order: 1,
    },
  ],
};

function ensureDb() {
  if (!fs.existsSync(DATA_DIR)) fs.mkdirSync(DATA_DIR, { recursive: true });
  if (!fs.existsSync(DB_PATH)) {
    fs.writeFileSync(DB_PATH, JSON.stringify(DEFAULT_DB, null, 2), "utf8");
  }
}

export function readDb() {
  ensureDb();
  return JSON.parse(fs.readFileSync(DB_PATH, "utf8"));
}

export function writeDb(db) {
  ensureDb();
  const tmp = DB_PATH + ".tmp";
  fs.writeFileSync(tmp, JSON.stringify(db, null, 2), "utf8");
  fs.renameSync(tmp, DB_PATH);
}

export function slugify(text) {
  const map = { ç: "c", ğ: "g", ı: "i", ö: "o", ş: "s", ü: "u", Ç: "c", Ğ: "g", İ: "i", Ö: "o", Ş: "s", Ü: "u" };
  const base = text
    .split("")
    .map((ch) => map[ch] ?? ch)
    .join("")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)/g, "");
  return base + "-" + crypto.randomBytes(3).toString("hex");
}
