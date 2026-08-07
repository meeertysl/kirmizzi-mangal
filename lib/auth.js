import crypto from "crypto";
import fs from "fs";
import path from "path";
import { cookies } from "next/headers";
import { readDb } from "./db";

const SECRET_PATH = path.join(process.cwd(), "data", "secret.key");
const DEFAULT_PASSWORD = "admin123";
export const SESSION_COOKIE = "km_session";

function getSecret() {
  const dir = path.dirname(SECRET_PATH);
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  if (!fs.existsSync(SECRET_PATH)) {
    fs.writeFileSync(SECRET_PATH, crypto.randomBytes(32).toString("hex"), "utf8");
  }
  return fs.readFileSync(SECRET_PATH, "utf8").trim();
}

export function hashPassword(password) {
  const salt = crypto.randomBytes(16).toString("hex");
  const hash = crypto.scryptSync(password, salt, 64).toString("hex");
  return `${salt}:${hash}`;
}

export function verifyPassword(password, stored) {
  if (!stored) return password === DEFAULT_PASSWORD;
  const [salt, hash] = stored.split(":");
  const candidate = crypto.scryptSync(password, salt, 64).toString("hex");
  return crypto.timingSafeEqual(Buffer.from(hash, "hex"), Buffer.from(candidate, "hex"));
}

export function checkAdminPassword(password) {
  const db = readDb();
  return verifyPassword(password, db.settings.adminPassword);
}

export function createSessionToken() {
  const payload = JSON.stringify({ admin: true, exp: Date.now() + 1000 * 60 * 60 * 24 * 7 });
  const data = Buffer.from(payload).toString("base64url");
  const sig = crypto.createHmac("sha256", getSecret()).update(data).digest("base64url");
  return `${data}.${sig}`;
}

export function verifySessionToken(token) {
  if (!token || !token.includes(".")) return false;
  const [data, sig] = token.split(".");
  const expected = crypto.createHmac("sha256", getSecret()).update(data).digest("base64url");
  if (sig.length !== expected.length) return false;
  if (!crypto.timingSafeEqual(Buffer.from(sig), Buffer.from(expected))) return false;
  try {
    const payload = JSON.parse(Buffer.from(data, "base64url").toString("utf8"));
    return payload.admin === true && payload.exp > Date.now();
  } catch {
    return false;
  }
}

export async function isAdmin() {
  const store = await cookies();
  const token = store.get(SESSION_COOKIE)?.value;
  return verifySessionToken(token);
}
