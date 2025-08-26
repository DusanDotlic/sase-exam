<?php
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Secure Notes – Sign up</title>
  <link rel="stylesheet" href="/public/css/app.css">
  <style>
    body { min-height: 100vh; display: grid; place-items: center; background: #0f172a; color: #e5e7eb; font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell;}
    .card { max-width: 520px; width: 92vw; background: #111827; border: 1px solid #374151; border-radius: 16px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,.35);}
    h1 { font-size: 24px; margin: 0 0 14px; }
    label { display:block; font-size: 14px; color:#cbd5e1; margin-top: 10px;}
    input { width: 100%; margin-top: 6px; padding: 10px 12px; border-radius:10px; border:1px solid #374151; background:#0b1220; color:#e5e7eb;}
    .row { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top: 16px;}
    .btn { display:inline-flex; gap:8px; align-items:center; justify-content:center; padding: 10px 16px; border-radius: 10px; border: 1px solid #374151; text-decoration:none; color:#e5e7eb; background:#1f2937; cursor:pointer; }
    .btn.primary { background:#2563eb; border-color:#2563eb; }
    .msg { margin-top: 12px; font-size: 14px; }
    .msg.err { color: #fca5a5; }
    .msg.ok { color: #86efac; }
    .note { margin-top:14px; color:#94a3b8; font-size: 14px; }
    .hid { display:none; }
  </style>
</head>
<body>
  <main class="card">
    <h1>Create your account</h1>
    <form id="signupForm">
      <label>Name
        <input type="text" name="name" placeholder="Your name" required />
      </label>
      <label>Email
        <input type="email" name="email" placeholder="you@example.com" required />
      </label>
      <label>Password
        <input type="password" name="password" placeholder="Choose a strong password" required />
      </label>
      <div class="row">
        <a class="btn" href="/sase-exam/public/index.php">Back</a>
        <button class="btn primary" type="submit">Sign up</button>
      </div>
      <div id="msg" class="msg" role="alert" aria-live="polite"></div>
    </form>

    <div id="postSignup" class="hid">
      <p class="msg ok"> Account created successfully.</p>
      <div class="row" style="margin-top:12px">
        <a class="btn primary" href="/sase-exam/public/login.php">Go to Log in</a>
        <a class="btn" href="/sase-exam/public/index.php">Back to Home</a>
      </div>
    </div>

    <p class="note">Already have an account? <a class="btn" href="/sase-exam/public/login.php" style="margin-left:6px">Log in</a></p>
  </main>

  <script>
    const form = document.getElementById("signupForm");
    const msg = document.getElementById("msg");
    const postSignup = document.getElementById("postSignup");

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      msg.textContent = "Creating your account…";
      msg.className = "msg";

      const data = Object.fromEntries(new FormData(form).entries());

      try {
        const res = await fetch("/sase-exam/api/auth/signup.php", {
          method: "POST",
          headers: {"Content-Type":"application/json"},
          credentials: "include",
          body: JSON.stringify(data)
        });

        const j = await res.json().catch(() => ({}));

        if (res.ok) {
          form.classList.add("hid");
          postSignup.classList.remove("hid");
          return;
        }

        msg.textContent = (j && (j.error || j.message)) ? (j.error || j.message) : "Signup failed.";
        msg.className = "msg err";
      } catch (err) {
        msg.textContent = "Network error. Please try again.";
        msg.className = "msg err";
      }
    });
  </script>
</body>
</html>
