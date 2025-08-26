<?php
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Secure Notes – Profile</title>
  <link rel="stylesheet" href="/public/css/app.css">
  <style>
    body { min-height: 100vh; display: grid; place-items: center; background: #0f172a; color: #e5e7eb; font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell;}
    .card { max-width: 520px; width: 92vw; background: #111827; border: 1px solid #374151; border-radius: 16px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,.35);}
    h1 { font-size: 24px; margin: 0 0 14px; }
    .row { display:flex; gap:10px; margin-top: 16px; }
    .btn { display:inline-flex; gap:8px; align-items:center; justify-content:center; padding: 10px 16px; border-radius: 10px; border: 1px solid #374151; text-decoration:none; color:#e5e7eb; background:#1f2937; cursor:pointer; }
    .btn.primary { background:#2563eb; border-color:#2563eb; }
    .kv { display:grid; grid-template-columns: 120px 1fr; gap:8px; }
    .lbl { color:#94a3b8; }
    .msg { margin-top:10px; font-size: 14px; }
  </style>
</head>
<body>
  <main class="card">
    <h1>Your Profile</h1>
    <div id="wrap">
      <div class="msg" id="status">Loading…</div>
    </div>
    <div class="row">
      <a class="btn" href="/sase-exam/public/index.php">Home</a>
      <a class="btn primary" href="/sase-exam/public/notes.php">Go to Notes</a>
    </div>
  </main>

  <script>
    (async () => {
      const wrap = document.getElementById("wrap");
      const status = document.getElementById("status");
      try {
        const res = await fetch("/sase-exam/api/auth/me.php", { credentials: "include" });
        if (!res.ok) {
          wrap.innerHTML = '<div class="msg">You are not logged in. <a class="btn primary" style="margin-left:6px" href="/sase-exam/public/login.php">Log in</a></div>';
          return;
        }
        const me = await res.json();
        status.remove();

        const kv = document.createElement("div");
        kv.className = "kv";
        kv.innerHTML = `
          <div class="lbl">ID</div><div>${me.id ?? ""}</div>
          <div class="lbl">Name</div><div>${me.name ?? ""}</div>
          <div class="lbl">Email</div><div>${me.email ?? ""}</div>
        `;
        wrap.appendChild(kv);
      } catch (e) {
        status.textContent = "Could not load profile.";
      }
    })();
  </script>
</body>
</html>
