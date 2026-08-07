import { NextResponse } from "next/server";
import { readDb, writeDb } from "@/lib/db";
import { isAdmin, hashPassword, verifyPassword } from "@/lib/auth";

const PUBLIC_FIELDS = [
  "name",
  "slogan",
  "about",
  "phone",
  "whatsapp",
  "address",
  "instagram",
  "hours",
  "mapsUrl",
];

export async function GET() {
  const db = readDb();
  const settings = {};
  for (const key of PUBLIC_FIELDS) settings[key] = db.settings[key] ?? "";
  return NextResponse.json(settings);
}

export async function PUT(request) {
  if (!(await isAdmin())) return NextResponse.json({ error: "Yetkisiz" }, { status: 401 });
  const body = await request.json();
  const db = readDb();

  for (const key of PUBLIC_FIELDS) {
    if (body[key] !== undefined) db.settings[key] = String(body[key]);
  }

  if (body.newPassword) {
    if (!verifyPassword(body.currentPassword ?? "", db.settings.adminPassword)) {
      return NextResponse.json({ error: "Mevcut şifre hatalı" }, { status: 400 });
    }
    if (String(body.newPassword).length < 6) {
      return NextResponse.json({ error: "Yeni şifre en az 6 karakter olmalı" }, { status: 400 });
    }
    db.settings.adminPassword = hashPassword(String(body.newPassword));
  }

  writeDb(db);
  return NextResponse.json({ ok: true });
}
