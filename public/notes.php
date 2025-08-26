<?php
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Secure Notes – Your Notes</title>
  <link rel="stylesheet" href="/public/css/app.css">
  <style>
    body { min-height: 100vh; background: #0f172a; color: #e5e7eb; font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell; }
    header { display:flex; align-items:center; justify-content:space-between; gap:10px; padding: 18px 22px; border-bottom: 1px solid #1f2937; background:#0b1220; position: sticky; top:0; }
    .brand { font-weight: 600; }
    .btn { display:inline-flex; gap:8px; align-items:center; justify-content:center; padding:10px 14px; border-radius:10px; border:1px solid #374151; text-decoration:none; color:#e5e7eb; background:#1f2937; cursor:pointer; }
    .btn.danger { background:#ef4444; border-color:#ef4444; }
    main { max-width: 920px; margin: 0 auto; padding: 24px; }
    h1 { font-size: 24px; margin: 10px 0 12px; }
    .card { background: #111827; border: 1px solid #374151; border-radius: 16px; padding: 18px; box-shadow: 0 10px 30px rgba(0,0,0,.35); }
    textarea, input[type="text"] { width: 100%; margin-top: 6px; padding: 10px; border-radius: 10px; border:1px solid #374151; background:#0b1220; color:#e5e7eb;}
    .row { display:flex; gap:10px; margin-top: 10px; flex-wrap: wrap; }
    .note-item { background:#0b1220; border:1px solid #374151; border-radius:12px; padding:12px; }
    .meta { color:#94a3b8; font-size: 12px; margin-top: 6px; }
    .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; margin-top: 16px; }
    .msg { margin-top: 10px; font-size: 14px; }
    .msg.err { color: #fca5a5; }
    .msg.ok { color: #86efac; }
    .hid { display:none; }
  </style>
</head>
<body>
  <header>
    <div class="brand">Secure Notes</div>
    <button id="logoutBtn" class="btn danger" title="Log out">Log out</button>
  </header>

  <main>
    <section class="card">
      <h1>Your Notes</h1>

      <form id="createForm">
        <label>
          Title
          <input type="text" name="title" placeholder="Note title" />
        </label>

        <label style="display:block; margin-top:10px;">
          Content
          <textarea name="content" rows="8" placeholder="Write your secure note…"></textarea>
        </label>

        <div class="row">
          <button class="btn" type="submit">Save note</button>
        </div>
        <div id="msg" class="msg" role="alert" aria-live="polite"></div>
      </form>
    </section>

    <section style="margin-top:16px">
      <div id="listWrap" class="card">
        <h2 style="margin:0 0 8px 0; font-size:18px;">Saved Notes</h2>
        <div id="empty" class="msg">No notes yet.</div>
        <div id="notesGrid" class="grid"></div>
      </div>
    </section>
  </main>

  <script>
    const WITH_CREDS = { credentials: "include" };

    // Try a one-time refresh on 401
    async function maybeRefresh(res) {
      if (res && res.status === 401) {
        const rr = await fetch("/sase-exam/api/auth/refresh.php", { method: "POST", ...WITH_CREDS });
        if (rr.ok) return true;
      }
      return false;
    }

    // 1) Require auth with one refresh attempt
    (async function requireAuth() {
      try {
        let me = await fetch("/sase-exam/api/auth/me.php", WITH_CREDS);
        if (!me.ok) {
          const refreshed = await maybeRefresh(me);
          if (!refreshed) {
            window.location.href = "/sase-exam/public/login.php";
            return;
          }
          me = await fetch("/sase-exam/api/auth/me.php", WITH_CREDS);
          if (!me.ok) {
            window.location.href = "/sase-exam/public/login.php";
            return;
          }
        }
      } catch (e) {
        window.location.href = "/sase-exam/public/login.php";
      }
    })();

    // Load & render notes
    async function fetchNotes() {
      let res = await fetch("/sase-exam/api/notes.php", WITH_CREDS);
      if (!res.ok) {
        const refreshed = await maybeRefresh(res);
        if (refreshed) res = await fetch("/sase-exam/api/notes.php", WITH_CREDS);
      }
      if (!res.ok) return [];

      let j;
      try { j = await res.json(); } catch { return []; }

      const maybe =
        Array.isArray(j) ? j :
        (j && j.data && Array.isArray(j.data.notes)) ? j.data.notes :
        (j && Array.isArray(j.notes)) ? j.notes :
        (j && Array.isArray(j.data)) ? j.data : [];

      return Array.isArray(maybe) ? maybe : [];
    }

    function renderNotes(items) {
      const grid = document.getElementById("notesGrid");
      const empty = document.getElementById("empty");
      grid.innerHTML = "";

      if (!items || items.length === 0) {
        empty.classList.remove("hid");
        return;
      }

      empty.classList.add("hid");
      for (const n of items) {
        const card = document.createElement("div");
        card.className = "note-item";
        const title = (n.title ?? "").toString();
        const content = (n.content ?? "").toString();
        const created = n.created_at ? new Date(n.created_at) : null;

        card.innerHTML = `
          <div style="font-weight:600">${title || "(Untitled)"}</div>
          <div style="white-space:pre-wrap; margin-top:6px">${content}</div>
          <div class="meta">${created ? created.toLocaleString() : ""}</div>
        `;
        grid.appendChild(card);
      }
    }

    async function load() {
      const items = await fetchNotes();
      renderNotes(items);
    }
    load();

    // Create note retry once after refresh if needed
    const form = document.getElementById("createForm");
    const msg = document.getElementById("msg");

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      msg.textContent = "Saving…";
      msg.className = "msg";

      const data = Object.fromEntries(new FormData(form).entries());

      try {
        let res = await fetch("/sase-exam/api/notes.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          ...WITH_CREDS,
          body: JSON.stringify(data)
        });

        if (!res.ok) {
          const refreshed = await maybeRefresh(res);
          if (refreshed) {
            res = await fetch("/sase-exam/api/notes.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              ...WITH_CREDS,
              body: JSON.stringify(data)
            });
          }
        }

        const j = await res.json().catch(() => ({}));

        if (res.ok) {
          msg.textContent = "Saved.";
          msg.className = "msg ok";
          form.reset();
          load();
          return;
        }

        msg.textContent = (j && (j.error || j.message)) ? (j.error || j.message) : "Failed to save note.";
        msg.className = "msg err";
      } catch (err) {
        msg.textContent = "Network error. Please try again.";
        msg.className = "msg err";
      }
    });

    // Logout
    document.getElementById("logoutBtn").addEventListener("click", async () => {
      try { await fetch("/sase-exam/api/auth/logout.php", { method: "POST", ...WITH_CREDS }); } catch (_) {}
      window.location.href = "/sase-exam/public/index.php";
    });
  </script>
</body>
</html>
