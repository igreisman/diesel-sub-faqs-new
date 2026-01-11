const fs = require("fs");
const path = require("path");

const categoriesDir = path.join(__dirname, "..", "categories");

function titleFromSlug(slug) {
  return slug
    .split("-")
    .filter(Boolean)
    .map((word) => {
      const upper = word.toUpperCase();
      if (upper === "WW2" || upper === "US" || upper === "WWII") return upper;
      return word.charAt(0).toUpperCase() + word.slice(1);
    })
    .join(" ");
}

function stripWrapper(html) {
  const bodyMatch = html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
  let inner = bodyMatch ? bodyMatch[1] : html;

  // Strip dynamic loading alerts and redirect scripts
  inner = inner.replace(/<div id="dynamic-loading"[^>]*>[\s\S]*?<\/div>/gi, "");
  inner = inner.replace(
    /<script[^>]*>[\s\S]*?window\.location\.replace[\s\S]*?<\/script>/gi,
    "",
  );

  // Remove repeated wrappers
  inner = inner
    .replace(/<\/?(main|header|article)[^>]*>/gi, "")
    .replace(/<script[^>]+tabs\.js[^>]*><\/script>/gi, "")
    .replace(/<link[^>]+tabs\.css[^>]*>/gi, "");

  // Focus on the last tab-box (deepest content)
  const tabPos = inner.lastIndexOf('<div class="tab-box"');
  if (tabPos !== -1) {
    const contribPos = inner.lastIndexOf('<a href="/contribute.html', tabPos);
    const start = contribPos !== -1 ? contribPos : tabPos;
    return inner.slice(start).trim();
  }

  return inner.trim();
}

function buildPage({ title, slug, content }) {
  const targetUrl = `/faq.php?slug=${encodeURIComponent(slug)}`;
  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${title} | Submarine FAQs</title>
  <meta name="description" content="${title} - WWII diesel-electric submarine FAQs">
  <link rel="canonical" href="${targetUrl}">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/tabs.css">
  <style>
    body { background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.06), transparent 35%), #0f1624; color: #e6edf3; }
    .faq-shell { max-width: 1000px; margin: 0 auto; }
    .faq-card { background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 18px; box-shadow: 0 16px 48px rgba(0,0,0,0.45); }
    .faq-heading { letter-spacing: 0.3px; }
  </style>
</head>
<body>
  <main class="container py-5 faq-shell">
    <header class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
      <div>
        <a class="text-warning fw-semibold text-decoration-none" href="/index.php">← Back to FAQ Home</a>
        <h1 class="h3 mb-0 mt-2 faq-heading">${title}</h1>
        <p class="text-secondary small mb-0">Slug: ${slug}</p>
      </div>
      <img src="/images/dolphins-insignia-replacement.png" alt="Submarine Dolphins" style="height:48px; opacity:0.8;">
    </header>
    <article class="faq-card p-4">
      ${content}
    </article>
  </main>
  <script src="/assets/js/tabs.js"></script>
</body>
</html>`;
}

function rebuildFile(filePath) {
  const slug = path.basename(filePath, ".html");
  const title = titleFromSlug(slug);
  const raw = fs.readFileSync(filePath, "utf8");
  const content = stripWrapper(raw);
  const rebuilt = buildPage({ title, slug, content });
  fs.writeFileSync(filePath, rebuilt, "utf8");
  return { slug };
}

function walk(dir) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  let count = 0;

  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      count += walk(fullPath);
    } else if (entry.isFile() && entry.name.endsWith(".html")) {
      rebuildFile(fullPath);
      count += 1;
    }
  }

  return count;
}

const total = walk(categoriesDir);
console.log(`Rebuilt ${total} category FAQ pages.`);
