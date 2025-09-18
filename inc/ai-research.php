<?php
// File: wp-content/themes/ai-news-hub/inc/ai-research.php
if (!defined('ABSPATH')) { exit; }

class AINews_Research {
  public static function bootstrap() {
    add_action('admin_menu', [__CLASS__, 'menu']);
    add_action('wp_ajax_ai_news_research', [__CLASS__, 'ajax_research']);
    if (defined('WP_CLI') && WP_CLI) {
      WP_CLI::add_command('ai-research', [__CLASS__, 'cli']);
    }
  }

  // Prefer WP constants; fall back to environment variables for CI/containers.
  private static function api_key() {
    if (defined('AINEWS_OPENAI_API_KEY') && AINEWS_OPENAI_API_KEY) return AINEWS_OPENAI_API_KEY;
    $env = getenv('AINEWS_OPENAI_API_KEY');
    return $env ? $env : null;
  }
  private static function api_base() {
    if (defined('AINEWS_OPENAI_BASE') && AINEWS_OPENAI_BASE) return AINEWS_OPENAI_BASE;
    return getenv('AINEWS_OPENAI_BASE') ?: 'https://api.openai.com/v1';
  }
  private static function model() {
    if (defined('AINEWS_OPENAI_MODEL') && AINEWS_OPENAI_MODEL) return AINEWS_OPENAI_MODEL;
    return getenv('AINEWS_OPENAI_MODEL') ?: 'gpt-4o-mini';
  }

  public static function menu() {
    add_submenu_page(
      'edit.php?post_type=article',
      'AI Research',
      'AI Research',
      'edit_posts',
      'ai-research',
      [__CLASS__, 'render_admin']
    );
  }

  public static function render_admin() {
    if (!current_user_can('edit_posts')) { wp_die('No permission'); } ?>
    <div class="wrap">
      <h1>AI Research Draft</h1>
      <?php if (!self::api_key()) : ?>
        <div class="notice notice-error"><p>Missing AINEWS_OPENAI_API_KEY (constant or environment variable).</p></div>
      <?php endif; ?>
      <p>Type a topic. The assistant drafts a new Article with title, tags, and formatted HTML.</p>
      <input type="text" id="ai-news-topic" class="regular-text" placeholder="e.g., AI trends in healthcare" />
      <button class="button button-primary" id="ai-news-run">Research Draft</button>
      <pre id="ai-news-log" style="margin-top:1em;max-width:800px;white-space:pre-wrap;"></pre>
    </div>
    <script>
      (function () {
        const btn = document.getElementById('ai-news-run');
        const topic = document.getElementById('ai-news-topic');
        const log = document.getElementById('ai-news-log');
        btn?.addEventListener('click', async () => {
          log.textContent = 'Working...';
          const res = await fetch(ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'ai_news_research', topic: topic.value })
          });
          const data = await res.json();
          log.textContent = (data?.data?.message || data?.message || 'Done') + "\n" + JSON.stringify(data, null, 2);
          const edit = (data?.data?.editlink || data?.editlink);
          if (edit) {
            const a = document.createElement('a');
            a.href = edit; a.textContent = 'Edit new Article'; a.className = 'button';
            log.appendChild(document.createElement('br'));
            log.appendChild(a);
          }
        });
      })();
    </script>
    <?php
  }

  public static function ajax_research() {
    if (!current_user_can('edit_posts')) { wp_send_json_error(['message' => 'No permission'], 403); }
    $topic = sanitize_text_field($_POST['topic'] ?? '');
    $result = self::generate($topic ?: 'Latest AI news for this week');
    if (is_wp_error($result)) { wp_send_json_error(['message' => $result->get_error_message()], 500); }
    wp_send_json_success($result);
  }

  public static function cli($args, $assoc) {
    $topic = $assoc['topic'] ?? 'Latest AI news for this week';
    $res = self::generate($topic);
    if (is_wp_error($res)) { WP_CLI::error($res->get_error_message()); }
    WP_CLI::success('Created Article ID ' . $res['postid']);
    WP_CLI::line('Title: ' . $res['title']);
  }

  private static function generate($topic) {
    $key = self::api_key();
    if (!$key) return new WP_Error('no_key', 'AINEWS_OPENAI_API_KEY missing.');

    $prompt = "Research the topic and produce:\n- A concise SEO title (<=70 chars)\n- 3-6 comma-separated tags\n- An engaging 600-900 word article in HTML with headings and links.\nTopic: {$topic}";

    $body = [
      'model' => self::model(),
      'messages' => [
        ['role' => 'system', 'content' => 'You are a senior tech journalist. Return JSON only with keys: title, tags_csv, html.'],
        ['role' => 'user', 'content' => $prompt],
      ],
      'response_format' => ['type' => 'json_object'],
      'temperature' => 0.7
    ];

    $response = wp_remote_post(self::api_base() . '/chat/completions', [
      'timeout' => 60,
      'headers' => [
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer ' . $key,
      ],
      'body' => wp_json_encode($body),
    ]);

    if (is_wp_error($response)) return $response;
    $code = wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) return new WP_Error('bad_resp', 'LLM API HTTP ' . $code . '.');

    $raw = wp_remote_retrieve_body($response);
    $data = json_decode($raw, true);
    $content = $data['choices'][0]['message']['content'] ?? '';
    $json = json_decode($content, true);
    if (empty($json) || empty($json['html'])) return new WP_Error('bad_parse', 'Could not parse model JSON.');

    $title = sanitize_text_field($json['title'] ?? 'AI Article');
    $tags_csv = sanitize_text_field($json['tags_csv'] ?? '');
    $html = wp_kses_post($json['html']);

    $postid = wp_insert_post([
      'post_type'   => 'article',
      'post_status' => 'publish',
      'post_title'  => $title,
      'post_content'=> $html,
    ], true);
    if (is_wp_error($postid)) return $postid;

    if (!empty($tags_csv)) {
      $tags = array_map('trim', explode(',', $tags_csv));
      wp_set_post_terms($postid, $tags, 'post_tag', true);
    }

    return [
      'postid'   => $postid,
      'title'    => $title,
      'editlink' => get_edit_post_link($postid),
      'message'  => 'Article created.',
    ];
  }
}

AINews_Research::bootstrap();
