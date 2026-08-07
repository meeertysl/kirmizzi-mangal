export default function Logo({ size = "md", light = false }) {
  const scale = { sm: 0.7, md: 1, lg: 1.5 }[size] ?? 1;
  return (
    <div className="logo" style={{ "--logo-scale": scale }}>
      <svg
        className="logo-flame"
        viewBox="0 0 100 90"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
      >
        {/* Alev */}
        <path
          d="M50 4 C46 18 34 24 32 38 C30 50 36 60 44 64 C40 56 42 48 50 40 C58 48 60 56 56 64 C64 60 70 50 68 38 C66 24 54 18 50 4 Z"
          fill={light ? "#f5f0eb" : "#1f1f1f"}
        />
        <path
          d="M50 30 C48 38 42 42 42 50 C42 57 46 61 50 62 C54 61 58 57 58 50 C58 42 52 38 50 30 Z"
          fill={light ? "#2b2b2b" : "#f5f0eb"}
        />
        {/* Çatal */}
        <g transform="rotate(-35 20 40)" fill={light ? "#f5f0eb" : "#1f1f1f"}>
          <rect x="17.5" y="36" width="5" height="34" rx="2.5" />
          <rect x="10" y="16" width="4" height="20" rx="2" />
          <rect x="18" y="14" width="4" height="22" rx="2" />
          <rect x="26" y="16" width="4" height="20" rx="2" />
        </g>
        {/* Spatula */}
        <g transform="rotate(35 80 40)" fill={light ? "#f5f0eb" : "#1f1f1f"}>
          <rect x="77.5" y="34" width="5" height="36" rx="2.5" />
          <rect x="70" y="12" width="20" height="24" rx="4" />
          <rect x="74" y="16" width="2.5" height="16" fill={light ? "#2b2b2b" : "#f5f0eb"} rx="1" />
          <rect x="79" y="16" width="2.5" height="16" fill={light ? "#2b2b2b" : "#f5f0eb"} rx="1" />
          <rect x="84" y="16" width="2.5" height="16" fill={light ? "#2b2b2b" : "#f5f0eb"} rx="1" />
        </g>
      </svg>
      <div className="logo-text">
        <span className="logo-kirmizi">KIRMIZI</span>
        <span className={light ? "logo-mangal logo-mangal-light" : "logo-mangal"}>MANGAL</span>
      </div>
    </div>
  );
}
