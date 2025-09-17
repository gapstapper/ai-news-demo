import React from "react";
import { createRoot } from "react-dom/client";
import Headlines from "./Headlines.jsx";

const el = document.getElementById("ai-news-headlines-root");
if (el) {
  const initial = JSON.parse(el.getAttribute("data-initial") || "[]");
  const api = el.getAttribute("data-api");
  const root = createRoot(el);
  root.render(<Headlines initialItems={initial} api={api} />);
}
