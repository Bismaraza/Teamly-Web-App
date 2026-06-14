<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit;
}
require_once 'php/db.php';

$notes = [];
if ($conn) {
  $result = $conn->query("SELECT * FROM notes ORDER BY id DESC");
  if ($result)
    while ($row = $result->fetch_assoc())
      $notes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notes — Teamly</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    html,
    body {
      height: 100%;
      font-family: Inter, sans-serif;
      background: #f5f5f8;
      color: #111
    }

    a {
      text-decoration: none;
      color: inherit
    }

    .app-shell {
      display: grid;
      grid-template-columns: 240px 1fr;
      min-height: 100vh
    }

    /* Sidebar */
    .app-sidebar {
      background: #111;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 24px 16px;
      gap: 2px;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 18px;
      font-weight: 900;
      letter-spacing: -.04em;
      margin-bottom: 28px;
      color: #fff
    }

    .brand-mark {
      width: 30px;
      height: 30px;
      border-radius: 9px;
      background: #fff;
      color: #111;
      display: grid;
      place-items: center;
      font-size: 14px;
      font-weight: 900;
      flex-shrink: 0
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 11px 13px;
      border-radius: 11px;
      color: #aaa;
      font-weight: 700;
      font-size: 14px;
      transition: .18s
    }

    .nav-item:hover,
    .nav-item.active {
      background: rgba(255, 255, 255, .1);
      color: #fff
    }

    .nav-item.logout {
      margin-top: auto;
      color: #f55
    }

    /* Main */
    .app-main {
      padding: 32px 36px;
      display: flex;
      flex-direction: column;
      gap: 22px
    }

    .page-title {
      font-size: 26px;
      font-weight: 900;
      letter-spacing: -.04em
    }

    .page-sub {
      color: #888;
      font-size: 13px;
      margin-top: 3px
    }

    /* Add form */
    .add-form {
      background: #fff;
      border: 1px solid #e8e8ec;
      border-radius: 18px;
      padding: 22px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, .04)
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 6px
    }

    .field label {
      font-size: 12px;
      font-weight: 800;
      color: #666;
      text-transform: uppercase;
      letter-spacing: .04em
    }

    .field input,
    .field textarea {
      border: 1px solid #e0e0e6;
      border-radius: 11px;
      padding: 11px 13px;
      font: inherit;
      font-size: 14px;
      outline: none;
      background: #fafafa;
      resize: vertical
    }

    .field input:focus,
    .field textarea:focus {
      border-color: #111;
      background: #fff
    }

    .form-footer {
      grid-column: 1/-1;
      display: flex;
      justify-content: flex-end
    }

    .btn-add {
      background: #111;
      color: #fff;
      border: none;
      border-radius: 11px;
      padding: 11px 24px;
      font: inherit;
      font-weight: 800;
      font-size: 14px;
      cursor: pointer
    }

    .btn-add:hover {
      background: #333
    }

    /* Filter / Search bar */
    .filter-bar {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items: center
    }

    .filter-bar span {
      font-size: 13px;
      font-weight: 800;
      color: #888;
      margin-right: 4px
    }

    .fbtn {
      border: 1.5px solid #e0e0e6;
      background: #fff;
      border-radius: 99px;
      padding: 7px 16px;
      font: inherit;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      color: #555;
      transition: .18s
    }

    .fbtn:hover {
      border-color: #111;
      color: #111
    }

    .fbtn.active {
      background: #111;
      color: #fff;
      border-color: #111
    }

    .search-input {
      border: 1.5px solid #e0e0e6;
      border-radius: 99px;
      padding: 7px 16px;
      font: inherit;
      font-size: 13px;
      font-weight: 700;
      outline: none;
      min-width: 220px;
      margin-left: auto
    }

    .search-input:focus {
      border-color: #111
    }

    /* Notes grid */
    .notes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 16px
    }

    .note-card {
      background: #fff;
      border: 1px solid #e8e8ec;
      border-radius: 18px;
      padding: 22px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, .04);
      transition: .2s;
      display: flex;
      flex-direction: column;
      gap: 8px;
      position: relative
    }

    .note-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, .08)
    }

    .note-card:hover .del-btn {
      opacity: 1
    }

    .del-btn {
      position: absolute;
      top: 12px;
      right: 12px;
      background: none;
      border: none;
      cursor: pointer;
      font-size: 15px;
      opacity: 0;
      transition: .18s;
      padding: 3px 6px;
      border-radius: 7px;
      color: #ccc
    }

    .del-btn:hover {
      background: #fff1f1;
      color: #ef4444
    }

    .note-title {
      font-size: 16px;
      font-weight: 800
    }

    .note-body {
      font-size: 14px;
      color: #666;
      line-height: 1.6;
      flex: 1
    }

    .note-date {
      font-size: 11px;
      font-weight: 800;
      color: #bbb
    }

    .no-notes {
      text-align: center;
      padding: 80px 0;
      color: #bbb
    }

    .no-notes div {
      font-size: 48px;
      margin-bottom: 12px
    }

    .no-notes p {
      font-size: 15px;
      font-weight: 700
    }

    @media(max-width:900px) {
      .app-shell {
        grid-template-columns: 1fr
      }

      .app-sidebar {
        position: relative;
        height: auto;
        flex-direction: row;
        flex-wrap: wrap;
        padding: 12px 16px
      }

      .brand {
        margin-bottom: 0
      }

      .nav-item.logout {
        margin-top: 0
      }

      .app-main {
        padding: 20px 16px
      }

      .add-form {
        grid-template-columns: 1fr
      }

      .notes-grid {
        grid-template-columns: 1fr
      }
    }
  </style>
</head>

<body>
  <div class="app-shell">
    <aside class="app-sidebar">
      <a class="brand" href="dashboard.php"><span class="brand-mark">T</span><span>Teamly</span></a>
      <a class="nav-item" href="dashboard.php">🏠 Dashboard</a>
      <a class="nav-item" href="tasks.php">✅ Tasks</a>
      <a class="nav-item active" href="notes.php">📝 Notes</a>
      <a class="nav-item" href="calendar.php">📅 Calendar</a>
      <a class="nav-item" href="integrations.html">🔗 Integrations</a>
      <a class="nav-item" href="settings.html">⚙️ Settings</a>
      <a class="nav-item logout" href="php/logout.php">🚪 Logout</a>
    </aside>

    <main class="app-main">
      <div>
        <div class="page-title">Smart Notes</div>
        <div class="page-sub">Write and organize your ideas</div>
      </div>

      <!-- Add Note Form -->
      <form class="add-form" action="php/add_note.php" method="POST">
        <div class="field">
          <label>Note Title</label>
          <input type="text" name="title" placeholder="e.g. Research idea" required>
        </div>
        <div class="field">
          <label>Content</label>
          <textarea name="content" rows="3" placeholder="Write your note here…" required></textarea>
        </div>
        <div class="form-footer">
          <button class="btn-add" type="submit">💾 Save Note</button>
        </div>
      </form>

      <!-- Filter bar -->
      <div class="filter-bar">
        <span>Filter:</span>
        <button class="fbtn active" onclick="filterNotes('all',this)">All (<?= count($notes) ?>)</button>
        <button class="fbtn" onclick="filterNotes('recent',this)">Recent (Today)</button>
        <input class="search-input" type="text" id="note-search" placeholder="🔍 Search notes…"
          oninput="searchNotes(this.value)">
      </div>

      <!-- Notes Grid -->
      <?php if (empty($notes)): ?>
        <div class="no-notes">
          <div>📝</div>
          <p>No notes yet. Add your first note above!</p>
        </div>
      <?php else: ?>
        <div class="notes-grid" id="notes-grid">
          <?php foreach ($notes as $note):
            $dateStr = !empty($note['created_at']) ? date('Y-m-d', strtotime($note['created_at'])) : '';
            $today = date('Y-m-d');
            $isToday = $dateStr === $today ? 'today' : '';
            ?>
            <div class="note-card" data-date="<?= $isToday ?>"
              data-title="<?= strtolower(htmlspecialchars($note['title'])) ?>"
              data-content="<?= strtolower(htmlspecialchars($note['content'])) ?>">
              <form method="POST" action="php/delete_note.php" onsubmit="return confirm('Delete this note?')">
                <input type="hidden" name="id" value="<?= $note['id'] ?>">
                <button class="del-btn" type="submit" title="Delete">🗑</button>
              </form>
              <div class="note-title"><?= htmlspecialchars($note['title']) ?></div>
              <div class="note-body"><?= nl2br(htmlspecialchars($note['content'])) ?></div>
              <?php if (!empty($note['created_at'])): ?>
                <div class="note-date"><?= date('M j, Y • g:i A', strtotime($note['created_at'])) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div id="no-result" style="display:none" class="no-notes">
          <div>🔍</div>
          <p>No notes match your search</p>
        </div>
      <?php endif; ?>

    </main>
  </div>

  <script>
    function filterNotes(type, btn) {
      document.querySelectorAll('.fbtn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('note-search').value = '';

      const cards = document.querySelectorAll('.note-card');
      let visible = 0;
      cards.forEach(card => {
        const show = type === 'all' || (type === 'recent' && card.dataset.date === 'today');
        card.style.display = show ? '' : 'none';
        if (show) visible++;
      });
      const nr = document.getElementById('no-result');
      if (nr) nr.style.display = visible === 0 ? '' : 'none';
    }

    function searchNotes(val) {
      document.querySelectorAll('.fbtn').forEach(b => b.classList.remove('active'));
      const cards = document.querySelectorAll('.note-card');
      const q = val.toLowerCase().trim();
      let visible = 0;
      cards.forEach(card => {
        const match = !q || card.dataset.title.includes(q) || card.dataset.content.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      const nr = document.getElementById('no-result');
      if (nr) nr.style.display = visible === 0 ? '' : 'none';
    }
  </script>
</body>

</html>