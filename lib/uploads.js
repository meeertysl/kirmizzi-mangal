import path from "path";

// UPLOAD_DIR env ile uygulama klasörünün dışına taşınabilir; görseller
// /uploads/<dosya> adresinden app/uploads/[file]/route.js ile servis edilir.
export const UPLOAD_DIR = process.env.UPLOAD_DIR
  ? path.resolve(process.env.UPLOAD_DIR)
  : path.join(process.cwd(), "public", "uploads");
