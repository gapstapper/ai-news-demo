### AI News Hub — Overview
A WordPress project that combines a REST‑enabled “Articles” Custom Post Type (CPT) a Vite‑built React Headlines widget, and an admin “AI Research” workflow that drafts Articles via an OpenAI‑compatible API; it includes GitHub Actions to build, test in Docker, and optionally deploy via SFTP.

### Architecture diagram
```
wp-content/                                           # AI News WP Theme 
├─ plugins/                                           # AI News WP Theme 
│  └─ ai-news-hub-cpt/                                # AI News WP Theme 
│     └─ ai-news-hub-cpt.php                          # AI News WP Theme Plugin registers the “article” Custom Post Type 
└─ themes/                                            # AI News WP Theme 
   └─ ai-news-hub/                                    # AI News WP Theme 
      ├─ style.css                                    # Theme metadata + base styles 
      ├─ functions.php                                # Enqueues React assets, registers shortcode, and includes AI automation module 
      ├─ header.php                                   # Required head with wp_head 
      ├─ footer.php                                   # Required footer with wp_footer 
      ├─ index.php                                    # Index page - (includes Front Page React Widget - Search Article Archive)
      ├─ archive-article.php                          # Articles archive template 
      ├─ single-article.php                           # Single Article template 
      ├─ template-parts/                              # React Article Search Widget 
      │  └─ react-headlines.php                       # Server‑rendered mount for the React Headlines widget via functions.php
      ├─ inc/                                         # AI-Research Integration 
      │  └─ ai-research.php                           # Includes AI-Research Integration class AINews_Research class (submenu, Ajax, WP-CLI, LLM) 
      ├─ react/                                       # React Article Search Widget 
      │  └─ headlines/                                # React Article Search Widget 
      │     ├─ package.json                           # React/Vite project manifest (dependencies, scripts + build entry) so the widget compiles to static assets
      │     ├─ vite.config.js                         # Vite build config targeting assets/headlines output path, producing hashed JS/CSS bundles for functions.php use
      │     └─ src/                                   # React source 
      │        ├─ main.jsx                            # Client entry locates server rendered mount, hydrates React tree, and kicks off background fetch from REST API
      │        └─ Headlines.jsx                       # UI Headlines component implementing filter/sort + REST fetch with minimal fields
      └─ assets/                                      # React Article Search Widget (build artifacts) 
         └─ headlines/                                # React Article Search Widget 
            └─ assets/                                # Vite output (hash may vary) 
               ├─ main-BH2fKHtS.js                    # Built JS bundle (example hash) 
               └─ main.css                            # Built CSS (if emitted) 

.github/                                              # Theme CI/CD Workflows 
└─ workflows/                                         # Theme CI/CD Workflows 
   ├─ release-theme.yml                               # CI/CD Workflow for Build & Release of AI-News Theme Artifact ZIP + checksum 
   ├─ smoke-tests-workflow.yml                        # CI/CD Workflow including Docker WP+MySQL Smoke Tests (REST, archive, home and AI component) 
   └─ deploy-sftp.yml                                 # CI/CD Workflow for SFTP deploy / backup of theme path 

wp-config.php                                         # AI News WP Theme + AI-Research Integration (API constants) 
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
- AI-News Theme Build & Release Artifact: .github/workflows/release-theme.yml zips theme, generates SHA256, uploads artifact, and creates a Release on demand or tag push; demo without a server.
- AI-News Theme Smoke Tests Workflow: .github/workflows/smoke-tests-workflow.yml boots MySQL+WP in Docker, copies theme, registers CPT via mu-plugin, flushes permalinks, creates a sample Article, and curls homepage, archive, and REST. Also, runs wp ai-research in CI when AINEWS_OPENAI_API_KEY secret is present, verifying the automation path end‑to‑end.
- AI-News Theme SFTP BackUp: CI/CD Workflow for SFTP deploy / backup of theme path 
