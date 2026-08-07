import { NextResponse } from "next/server";
import fs from "fs";
import path from "path";
import crypto from "crypto";
import { isAdmin } from "@/lib/auth";

const ALLOWED = { "image/jpeg": ".jpg", "image/png": ".png", "image/webp": ".webp", "image/gif": ".gif" };
const MAX_SIZE = 8 * 1024 * 1024;

export async function POST(request) {
  if (!(await isAdmin())) return NextResponse.json({ error: "Yetkisiz" }, { status: 401 });
  const formData = await request.formData();
  const file = formData.get("file");
  if (!file || typeof file === "string") {
    return NextResponse.json({ error: "Dosya gerekli" }, { status: 400 });
  }
  const ext = ALLOWED[file.type];
  if (!ext) return NextResponse.json({ error: "Sadece JPG, PNG, WEBP veya GIF yükleyebilirsiniz" }, { status: 400 });
  if (file.size > MAX_SIZE) return NextResponse.json({ error: "Dosya 8MB'dan büyük olamaz" }, { status: 400 });

  const dir = path.join(process.cwd(), "public", "uploads");
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  const filename = crypto.randomBytes(8).toString("hex") + ext;
  const buffer = Buffer.from(await file.arrayBuffer());
  fs.writeFileSync(path.join(dir, filename), buffer);

  return NextResponse.json({ url: `/uploads/${filename}` });
}
