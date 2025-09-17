import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { resolve, dirname } from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

export default defineConfig({
  plugins: [react()],
  root: resolve(__dirname),
  build: {
    // Build output goes inside the theme so WP can serve it.
    outDir: resolve(__dirname, "../../assets/headlines"),
    emptyOutDir: true,
    rollupOptions: {
      input: resolve(__dirname, "src/main.jsx")
    }
  },
  server: { port: 5173, strictPort: true }
});
