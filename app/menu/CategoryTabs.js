"use client";

import { useEffect, useState } from "react";

export default function CategoryTabs({ categories }) {
  const [active, setActive] = useState(categories[0]?.id);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (entry.isIntersecting) setActive(entry.target.id);
        }
      },
      { rootMargin: "-20% 0px -70% 0px" }
    );
    for (const c of categories) {
      const el = document.getElementById(c.id);
      if (el) observer.observe(el);
    }
    return () => observer.disconnect();
  }, [categories]);

  if (categories.length === 0) return null;

  return (
    <nav className="category-tabs">
      {categories.map((c) => (
        <a
          key={c.id}
          href={`#${c.id}`}
          className={active === c.id ? "category-tab active" : "category-tab"}
          onClick={() => setActive(c.id)}
        >
          {c.name}
        </a>
      ))}
    </nav>
  );
}
