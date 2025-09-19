### AI News Hub — Overview
A WordPress demo that combines a REST‑enabled “Articles” Custom Post Type (CPT) a Vite‑built React Headlines widget, and an admin “AI Research” workflow that drafts Articles via an OpenAI‑compatible API; it includes GitHub Actions to build, test in Docker, and optionally deploy via SFTP.

### Architecture diagram
```
wp-content/                                           # AI News WP Theme [attached_file:12]
├─ plugins/                                           # AI News WP Theme [attached_file:12]
│  └─ ai-news-hub-cpt/                                # AI News WP Theme [attached_file:12]
│     └─ ai-news-hub-cpt.php                          # CPT plugin registers 'article' + REST [attached_file:12]
└─ themes/                                            # AI News WP Theme [attached_file:12]
   └─ ai-news-hub/                                    # AI News WP Theme [attached_file:12]
      ├─ style.css                                    # Theme metadata + base styles [attached_file:12]
      ├─ functions.php                                # Enqueues assets, shortcode, require AI module [attached_file:12]
      ├─ header.php                                   # Required head with wp_head [attached_file:12]
      ├─ footer.php                                   # Required footer with wp_footer [attached_file:12]
      ├─ index.php                                    # Fallback loop template [attached_file:12]
      ├─ archive-article.php                          # Articles archive template [attached_file:12]
      ├─ single-article.php                           # Single Article template [attached_file:12]
      ├─ template-parts/                              # React Article Search Widget [attached_file:12]
      │  └─ react-headlines.php                       # Server-rendered mount div + data attributes [attached_file:12]
      ├─ inc/                                         # AI-Research Integration [attached_file:12]
      │  └─ ai-research.php                           # AINews_Research class (submenu, Ajax, WP-CLI, LLM) [attached_file:12]
      ├─ react/                                       # React Article Search Widget [attached_file:12]
      │  └─ headlines/                                # React Article Search Widget [attached_file:12]
      │     ├─ package.json                           # React/Vite project manifest [attached_file:12]
      │     ├─ vite.config.js                         # Vite build config → assets/headlines output [attached_file:12]
      │     └─ src/                                   # React source [attached_file:12]
      │        ├─ main.jsx                            # Hydration entry (attach to mount) [attached_file:12]
      │        └─ Headlines.jsx                       # UI: filter/sort + REST fetch [attached_file:12]
      └─ assets/                                      # React Article Search Widget (build artifacts) [attached_file:12]
         └─ headlines/                                # React Article Search Widget [attached_file:12]
            └─ assets/                                # Vite output (hash may vary) [attached_file:12]
               ├─ main-BH2fKHtS.js                    # Built JS bundle (example hash) [attached_file:12]
               └─ main.css                            # Built CSS (if emitted) [attached_file:12]

.github/                                              # Theme Workflows [attached_file:12]
└─ workflows/                                         # Theme Workflows [attached_file:12]
   ├─ release-theme.yml                               # Build & Release artifact/ZIP + checksum [attached_file:12]
   ├─ ci-wordpress.yml                                # Docker WP+MySQL smoke tests (REST, archive, home) [attached_file:12]
   └─ deploy-sftp.yml                                 # Optional SFTP deploy to /wp-content theme path [attached_file:12]

wp-config.php                                         # AI News WP Theme + AI-Research Integration (API constants) [attached_file:12]
```

### Data flow
- Browser: renders a server HTML mount + hydrates React; calls WP REST for Articles; admin clicks AI Research to generate content.
- WordPress: custom theme + CPT plugin; shortcode prints mount; Ajax and WP‑CLI connect to AI service; wp-config.php supplies API constants.
- CI/CD: Actions builds ZIP artifact, spins WordPress+MySQL in Docker for smoke tests, and can SFTP deploy the theme to a remote /wp-content path.

### Quick start
- Local WP: Install WordPress under WAMP/LAMP and activate theme “AI News Hub”; activate the “AI News Hub CPT” plugin; save Permalinks.
- React build: cd wp-content/themes/ai-news-hub/react/headlines && npm i && npm run build; ensure functions.php enqueues the built JS/CSS.
- Place widget: Edit homepage and add the shortcode [ai_headlines]; load page, then use filter/sort to verify hydration and REST fetch.

### Run commands
- Build React widget:  
  - cd wp-content/themes/ai-news-hub/react/headlines && npm i && npm run build.
- AI generator (WP‑CLI, from container or host with WP‑CLI installed):  
  - wp ai-research --topic="AI trends in education" --path=/var/www/html --allow-root.
- Flush permalinks (WP‑CLI):  
  - wp rewrite structure '/%postname%/' --hard && wp rewrite flush --hard.

### Key components
- CPT plugin: Registers post_type article with show_in_rest and rest_base articles, archive at /articles/, and REST collection /wp-json/wp/v2/articles.
- React widget: Vite output lives under assets/headlines; functions.php enqueues main JS/CSS; template-parts/react-headlines.php prints a div with data-initial and data-api; shortcode [ai_headlines] inserts it on any page.
- AI Research: inc/ai-research.php adds a submenu under Articles, Ajax handler, and a WP‑CLI command; JSON response returns title/tags/html and inserts a post; keys/model/base come from wp-config.php constants or env.

### Configuration
- In wp-config.php, define AINEWS_OPENAI_API_KEY, AINEWS_OPENAI_BASE, AINEWS_OPENAI_MODEL, alongside WP_DEBUG flags and the standard DB settings; ABSPATH is set to load core reliably.
- Ensure the CPT plugin is active and Permalinks are saved; verify REST at /wp-json/ and /wp-json/wp/v2/articles before testing React fetch.

### GitHub Actions
- Build & Release: .github/workflows/release-theme.yml zips theme, generates SHA256, uploads artifact, and creates a Release on demand or tag push; demo without a server.
- WordPress Smoke Tests: .github/workflows/ci-wordpress.yml boots MySQL+WP in Docker, copies theme, registers CPT via mu-plugin, flushes permalinks, creates a sample Article, and curls homepage, archive, and REST.
- AI Draft Smoke (optional): .github/workflows/ai-draft-smoke.yml runs wp ai-research in CI when AINEWS_OPENAI_API_KEY secret is present, verifying the automation path end‑to‑end.
