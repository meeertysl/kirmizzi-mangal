import { NextResponse } from "next/server";
import { readDb, writeDb, slugify } from "@/lib/db";
import { isAdmin } from "@/lib/auth";

export async function GET() {
  const db = readDb();
  return NextResponse.json(db.categories.sort((a, b) => a.order - b.order));
}

export async function POST(request) {
  if (!(await isAdmin())) return NextResponse.json({ error: "Yetkisiz" }, { status: 401 });
  const { name } = await request.json();
  if (!name?.trim()) return NextResponse.json({ error: "Kategori adı gerekli" }, { status: 400 });
  const db = readDb();
  const category = {
    id: slugify(name),
    name: name.trim(),
    order: db.categories.length ? Math.max(...db.categories.map((c) => c.order)) + 1 : 1,
  };
  db.categories.push(category);
  writeDb(db);
  return NextResponse.json(category);
}

export async function PUT(request) {
  if (!(await isAdmin())) return NextResponse.json({ error: "Yetkisiz" }, { status: 401 });
  const { id, name, order } = await request.json();
  const db = readDb();
  const category = db.categories.find((c) => c.id === id);
  if (!category) return NextResponse.json({ error: "Kategori bulunamadı" }, { status: 404 });
  if (name?.trim()) category.name = name.trim();
  if (typeof order === "number") category.order = order;
  writeDb(db);
  return NextResponse.json(category);
}

export async function DELETE(request) {
  if (!(await isAdmin())) return NextResponse.json({ error: "Yetkisiz" }, { status: 401 });
  const { id } = await request.json();
  const db = readDb();
  db.categories = db.categories.filter((c) => c.id !== id);
  db.products = db.products.filter((p) => p.categoryId !== id);
  writeDb(db);
  return NextResponse.json({ ok: true });
}
