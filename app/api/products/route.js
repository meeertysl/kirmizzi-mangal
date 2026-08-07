import { NextResponse } from "next/server";
import { readDb, writeDb, slugify } from "@/lib/db";
import { isAdmin } from "@/lib/auth";

export async function GET() {
  const db = readDb();
  return NextResponse.json(db.products.sort((a, b) => a.order - b.order));
}

export async function POST(request) {
  if (!(await isAdmin())) return NextResponse.json({ error: "Yetkisiz" }, { status: 401 });
  const body = await request.json();
  if (!body.name?.trim()) return NextResponse.json({ error: "Ürün adı gerekli" }, { status: 400 });
  if (!body.categoryId) return NextResponse.json({ error: "Kategori gerekli" }, { status: 400 });
  const db = readDb();
  if (!db.categories.some((c) => c.id === body.categoryId)) {
    return NextResponse.json({ error: "Kategori bulunamadı" }, { status: 404 });
  }
  const siblings = db.products.filter((p) => p.categoryId === body.categoryId);
  const product = {
    id: slugify(body.name),
    categoryId: body.categoryId,
    name: body.name.trim(),
    description: body.description?.trim() ?? "",
    price: Number(body.price) || 0,
    image: body.image ?? "",
    available: body.available !== false,
    order: siblings.length ? Math.max(...siblings.map((p) => p.order)) + 1 : 1,
  };
  db.products.push(product);
  writeDb(db);
  return NextResponse.json(product);
}

export async function PUT(request) {
  if (!(await isAdmin())) return NextResponse.json({ error: "Yetkisiz" }, { status: 401 });
  const body = await request.json();
  const db = readDb();
  const product = db.products.find((p) => p.id === body.id);
  if (!product) return NextResponse.json({ error: "Ürün bulunamadı" }, { status: 404 });
  if (body.name?.trim()) product.name = body.name.trim();
  if (body.description !== undefined) product.description = body.description.trim();
  if (body.price !== undefined) product.price = Number(body.price) || 0;
  if (body.image !== undefined) product.image = body.image;
  if (body.available !== undefined) product.available = !!body.available;
  if (body.categoryId && db.categories.some((c) => c.id === body.categoryId)) {
    product.categoryId = body.categoryId;
  }
  if (typeof body.order === "number") product.order = body.order;
  writeDb(db);
  return NextResponse.json(product);
}

export async function DELETE(request) {
  if (!(await isAdmin())) return NextResponse.json({ error: "Yetkisiz" }, { status: 401 });
  const { id } = await request.json();
  const db = readDb();
  db.products = db.products.filter((p) => p.id !== id);
  writeDb(db);
  return NextResponse.json({ ok: true });
}
