import { NextResponse } from "next/server";
import fs from "fs";
import path from "path";
import { UPLOAD_DIR } from "@/lib/uploads";

const MIME = {
  ".jpg": "image/jpeg",
  ".jpeg": "image/jpeg",
  ".png": "image/png",
  ".webp": "image/webp",
  ".gif": "image/gif",
};

export async function GET(request, { params }) {
  const { file } = await params;
  const safe = path.basename(file);
  const type = MIME[path.extname(safe).toLowerCase()];
  const fullPath = path.join(UPLOAD_DIR, safe);
  if (!type || !fs.existsSync(fullPath)) {
    return NextResponse.json({ error: "Bulunamadı" }, { status: 404 });
  }
  return new NextResponse(fs.readFileSync(fullPath), {
    headers: {
      "Content-Type": type,
      "Cache-Control": "public, max-age=31536000, immutable",
    },
  });
}
