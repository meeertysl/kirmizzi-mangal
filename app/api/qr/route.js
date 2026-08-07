import { NextResponse } from "next/server";
import QRCode from "qrcode";
import { isAdmin } from "@/lib/auth";

export async function GET(request) {
  if (!(await isAdmin())) return NextResponse.json({ error: "Yetkisiz" }, { status: 401 });
  const { searchParams } = new URL(request.url);
  const url = searchParams.get("url") || `${new URL(request.url).origin}/menu`;
  const png = await QRCode.toBuffer(url, {
    type: "png",
    width: 1000,
    margin: 2,
    color: { dark: "#1a1a1a", light: "#ffffff" },
  });
  return new NextResponse(png, {
    headers: {
      "Content-Type": "image/png",
      "Content-Disposition": 'inline; filename="kirmizi-mangal-qr.png"',
    },
  });
}
