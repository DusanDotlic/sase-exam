<?php
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Secure Notes – Home</title>
  <link rel="stylesheet" href="/public/css/app.css">
  <style>
    body { min-height: 100vh; display: grid; place-items: center; background: #0f172a; color: #e5e7eb; font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell;}
    .card { max-width: 720px; width: 92vw; background: #111827; border: 1px solid #374151; border-radius: 16px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,.35);}
    h1 { font-size: 28px; margin: 0 0 8px; }
    p.lead { color: #cbd5e1; margin: 0 0 18px; }
    ul { margin: 6px 0 20px 18px; color: #cbd5e1; }
    .row { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn { display:inline-flex; gap:8px; align-items:center; justify-content:center; padding: 10px 16px; border-radius: 10px; border: 1px solid #374151; text-decoration:none; color:#e5e7eb; background:#1f2937; cursor:pointer; }
    .btn.primary { background:#2563eb; border-color:#2563eb; }
    .note { font-size: 14px; color:#94a3b8; margin-top: 10px; }
    .hid { display:none; }
  </style>
</head>
<body>
  <main class="card">
    <h1>Secure Notes</h1>
    <p class="lead">A minimal, privacy‑minded web app to store your notes securely with JWT‑based authentication.</p>
    <ul>
      <li>Sign up, then log in securely (access + refresh tokens).</li>
      <li>Create and manage your notes on your Notes page.</li>
      <li>Log out anytime (clears session) and you’re back here.</li>
    </ul>

    <div class="row" id="cta">
      <a class="btn primary" href="/sase-exam/public/login.php">Log in</a>
      <a class="btn" href="/sase-exam/public/signup.php">Sign up</a>
    </div>

    <p id="already" class="note hid">You’re already logged in. <a class="btn primary" href="/sase-exam/public/notes.php" style="margin-left:6px">Go to Notes</a></p>
  </main>

  <script>
    // If already authenticated offer a direct Notes link
    (async () => {
      try {
        const r = await fetch("/sase-exam/api/auth/me.php", { credentials: "include" });
        if (r.ok) {
          document.getElementById("cta").classList.add("hid");
          document.getElementById("already").classList.remove("hid");
        }
      } catch (_) {}
    })();
  </script>
</body>
</html>
