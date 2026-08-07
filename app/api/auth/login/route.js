import { NextResponse } from "next/server";
import { checkAdminPassword, createSessionToken, SESSION_COOKIE } from "@/lib/auth";

export async function POST(request) {
  const { password } = await request.json();
  if (!password || !checkAdminPassword(password)) {
    return NextResponse.json({ error: "Hatalı şifre" }, { status: 401 });
  }
  const res = NextResponse.json({ ok: true });
  res.cookies.set(SESSION_COOKIE, createSessionToken(), {
    httpOnly: true,
    sameSite: "lax",
    path: "/",
    maxAge: 60 * 60 * 24 * 7,
  });
  return res;
}
