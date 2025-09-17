import React, { useEffect, useMemo, useState } from "react";

export default function Headlines({ initialItems = [], api }) {
  const [items, setItems] = useState(initialItems);
  const [q, setQ] = useState("");
  const [sort, setSort] = useState("newest");
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    let canceled = false;
    (async () => {
      try {
        setBusy(true);
        const url = `${api}?per_page=20&_fields=id,title,link,date`;
        const res = await fetch(url, { headers: { Accept: "application/json" } });
        if (!res.ok) throw new Error(`Failed to fetch: ${res.status}`);
        const data = await res.json();
        if (!canceled) setItems(Array.isArray(data) ? data : []);
      } catch (e) {
        console.warn("Headlines fetch failed:", e);
      } finally {
        if (!canceled) setBusy(false);
      }
    })();
    return () => { canceled = true; };
  }, [api]);

  const filtered = useMemo(() => {
    const s = q.trim().toLowerCase();
    const base = s
      ? items.filter((i) => (i?.title?.rendered || "").toLowerCase().includes(s))
      : items.slice();
    base.sort((a, b) => {
      if (sort === "newest") return new Date(b.date) - new Date(a.date);
      return (a?.title?.rendered || "").localeCompare(b?.title?.rendered || "");
    });
    return base;
  }, [items, q, sort]);

  return (
    <div className="ai-headlines">
      <div style={{ display: "flex", gap: "0.5rem", alignItems: "center", marginBottom: "0.75rem" }}>
        <input
          placeholder="Filter headlines..."
          value={q}
          onChange={(e) => setQ(e.target.value)}
          style={{ flex: "1 1 auto", padding: "0.5rem" }}
        />
        <select value={sort} onChange={(e) => setSort(e.target.value)} style={{ padding: "0.5rem" }}>
          <option value="newest">Newest</option>
          <option value="title">Title A–Z</option>
        </select>
        {busy && <span style={{ fontSize: 12, opacity: 0.75 }}>Loading…</span>}
      </div>
      <ul style={{ listStyle: "none", padding: 0, margin: 0 }}>
        {filtered.map((post) => (
          <li key={post.id} style={{ padding: "0.5rem 0", borderBottom: "1px solid #eee" }}>
            <a href={post.link} dangerouslySetInnerHTML={{ __html: post?.title?.rendered || "untitled" }} />
            <div style={{ fontSize: 12, color: "#666" }}>
              <time dateTime={post.date}>{new Date(post.date).toLocaleDateString()}</time>
            </div>
          </li>
        ))}
        {!filtered.length && <li>No results</li>}
      </ul>
    </div>
  );
}
