import { isAdmin } from "@/lib/auth";
import LoginForm from "./LoginForm";
import AdminApp from "./AdminApp";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Yönetim Paneli | Kırmızı Mangal",
  robots: { index: false, follow: false },
};

export default async function AdminPage() {
  const authed = await isAdmin();
  return authed ? <AdminApp /> : <LoginForm />;
}
