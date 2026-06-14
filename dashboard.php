<?php
session_start();
require_once 'php/db.php';

$tasks = [];
$notes = [];
if ($conn) {
    $r = $conn->query("SELECT * FROM tasks ORDER BY id DESC");
    if ($r) while ($row = $r->fetch_assoc()) $tasks[] = $row;

    $r2 = $conn->query("SELECT * FROM notes ORDER BY id DESC LIMIT 3");
    if ($r2) while ($row = $r2->fetch_assoc()) $notes[] = $row;
}

$total     = count($tasks);
$done      = count(array_filter($tasks, fn($t) => $t['status']==='Done'));
$pending   = count(array_filter($tasks, fn($t) => in_array($t['status'],['To Do','Pending','Open'])));
$inprog    = count(array_filter($tasks, fn($t) => in_array($t['status'],['In Progress','Working'])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Teamly</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/home.css">
  <style>
    /* Full-window dashboard — no outer page margins */
    html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; }
    body { background: #f5f5f8; }

    .app-shell {
      display: grid;
      grid-template-columns: 260px 1fr;
      min-height: 100vh;
      width: 100%;
    }

    /* Sidebar */
    .app-sidebar {
      background: #111;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 28px 18px;
      gap: 4px;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }
    .app-sidebar .brand {
      display: flex; align-items: center; gap: 10px;
      font-size: 20px; font-weight: 900; letter-spacing: -.04em;
      margin-bottom: 30px; color: #fff;
    }
    .app-sidebar .brand-mark {
      width: 32px; height: 32px; border-radius: 10px;
      background: #fff; color: #111;
      display: grid; place-items: center;
      font-size: 16px; font-weight: 900;
    }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 11px 14px; border-radius: 12px;
      color: #aaa; font-weight: 700; font-size: 14px;
      transition: .2s; text-decoration: none;
    }
    .nav-item:hover, .nav-item.active {
      background: rgba(255,255,255,.1); color: #fff;
    }
    .nav-item.logout { margin-top: auto; color: #f55; }

    /* Main content */
    .app-main {
      padding: 32px 36px;
      display: flex; flex-direction: column; gap: 24px;
      min-height: 100vh;
    }

    .page-title { font-size: 28px; font-weight: 900; letter-spacing: -.04em; }
    .page-sub   { color: #888; font-size: 14px; margin-top: 2px; }

    /* Stat cards */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }
    .stat-card {
      background: #fff; border: 1px solid #e8e8ec;
      border-radius: 20px; padding: 22px;
      box-shadow: 0 4px 20px rgba(0,0,0,.04);
    }
    .stat-card .label { font-size: 12px; font-weight: 800; color: #888; text-transform: uppercase; letter-spacing: .06em; }
    .stat-card .value { font-size: 40px; font-weight: 900; letter-spacing: -.05em; margin-top: 6px; }

    /* Two-column row */
    .two-col { display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; }

    .panel {
      background: #fff; border: 1px solid #e8e8ec;
      border-radius: 20px; padding: 24px;
      box-shadow: 0 4px 20px rgba(0,0,0,.04);
    }
    .panel-title { font-size: 16px; font-weight: 900; margin-bottom: 16px; }

    /* Add task form inline */
    .add-task-form { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
    .add-task-form input, .add-task-form select {
      border: 1px solid #e8e8ec; border-radius: 12px;
      padding: 10px 14px; font: inherit; font-size: 14px;
      outline: none; background: #fafafa;
    }
    .add-task-form input { flex: 1; min-width: 180px; }
    .add-task-form button {
      background: #111; color: #fff; border: none;
      border-radius: 12px; padding: 10px 20px;
      font: inherit; font-weight: 800; font-size: 14px;
      cursor: pointer; white-space: nowrap;
    }
    .add-task-form button:hover { background: #333; }

    .task-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 14px; border-radius: 14px;
      background: #fafafa; border: 1px solid #ededf2;
      margin-bottom: 8px; font-size: 14px; font-weight: 700;
    }
    .badge {
      font-size: 11px; font-weight: 800; padding: 3px 10px;
      border-radius: 99px;
    }
    .badge-todo     { background: #f3f3f7; color: #555; }
    .badge-progress { background: #eff6ff; color: #3b82f6; }
    .badge-done     { background: #ecfdf5; color: #10b981; }
    .badge-high     { background: #fff1f1; color: #ef4444; }
    .badge-normal   { background: #f3f3f7; color: #555; }
    .badge-low      { background: #f0fdf4; color: #16a34a; }

    /* Notes mini-cards */
    .notes-list { display: flex; flex-direction: column; gap: 10px; }
    .note-card {
      background: #fafafa; border: 1px solid #ededf2;
      border-radius: 14px; padding: 14px;
    }
    .note-card h4 { font-size: 14px; font-weight: 800; margin-bottom: 4px; }
    .note-card p  { font-size: 13px; color: #777; line-height: 1.5;
                    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }

    @media(max-width:900px){
      .app-shell { grid-template-columns: 1fr; }
      .app-sidebar { position: relative; height: auto; flex-direction: row; flex-wrap: wrap; padding: 16px; }
      .app-main { padding: 20px 16px; }
      .stats-row { grid-template-columns: repeat(2,1fr); }
      .two-col { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="app-shell">
  <!-- Sidebar -->
  <aside class="app-sidebar">
    <a class="brand" href="dashboard.php">
      <span class="brand-mark">T</span>
      <span>Teamly</span>
    </a>
    <a class="nav-item active" href="dashboard.php">🏠 Dashboard</a>
    <a class="nav-item" href="tasks.php">✅ Tasks</a>
    <a class="nav-item" href="notes.php">📝 Notes</a>
    <a class="nav-item" href="calendar.php">📅 Calendar</a>
    <a class="nav-item" href="integrations.html">🔗 Integrations</a>
    <a class="nav-item" href="settings.html">⚙️ Settings</a>
    <a class="nav-item logout" href="php/logout.php">🚪 Logout</a>
  </aside>

  <!-- Main -->
  <main class="app-main">
    <div>
      <div class="page-title">Welcome to Teamly 👋</div>
      <div class="page-sub">Here's your overview for today</div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="label">Total Tasks</div>
        <div class="value"><?= $total ?></div>
      </div>
      <div class="stat-card">
        <div class="label">To Do</div>
        <div class="value" style="color:#555"><?= $pending ?></div>
      </div>
      <div class="stat-card">
        <div class="label">In Progress</div>
        <div class="value" style="color:#3b82f6"><?= $inprog ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Done</div>
        <div class="value" style="color:#10b981"><?= $done ?></div>
      </div>
    </div>

    <!-- Two-column: Tasks + Notes -->
    <div class="two-col">

      <!-- Tasks panel -->
      <div class="panel">
        <div class="panel-title">Recent Tasks</div>

        <!-- Quick add form -->
        <form class="add-task-form" action="php/add_task.php" method="POST">
          <input name="title" placeholder="New task title…" required>
          <select name="status">
            <option value="To Do">To Do</option>
            <option value="In Progress">In Progress</option>
            <option value="Done">Done</option>
          </select>
          <select name="priority">
            <option value="Low">Low</option>
            <option value="Normal" selected>Normal</option>
            <option value="High">High</option>
          </select>
          <button type="submit">+ Add</button>
        </form>

        <?php if(empty($tasks)): ?>
          <p style="color:#aaa;font-size:14px">No tasks yet. Add your first task above!</p>
        <?php else: ?>
          <?php foreach(array_slice($tasks,0,8) as $t):
            $sc = $t['status']==='Done' ? 'badge-done' : ($t['status']==='In Progress'||$t['status']==='Working' ? 'badge-progress' : 'badge-todo');
            $pc = $t['priority']==='High' ? 'badge-high' : ($t['priority']==='Low' ? 'badge-low' : 'badge-normal');
          ?>
          <div class="task-row">
            <span><?= htmlspecialchars($t['title']) ?></span>
            <div style="display:flex;gap:6px">
              <span class="badge <?= $sc ?>"><?= htmlspecialchars($t['status']) ?></span>
              <span class="badge <?= $pc ?>"><?= htmlspecialchars($t['priority']) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if($total > 8): ?>
            <a href="tasks.php" style="font-size:13px;font-weight:800;color:#555;display:block;margin-top:10px">View all <?= $total ?> tasks →</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- Notes panel -->
      <div class="panel">
        <div class="panel-title">Recent Notes</div>
        <?php if(empty($notes)): ?>
          <p style="color:#aaa;font-size:14px">No notes yet. <a href="notes.php" style="color:#111;font-weight:800">Add one →</a></p>
        <?php else: ?>
          <div class="notes-list">
            <?php foreach($notes as $n): ?>
            <div class="note-card">
              <h4><?= htmlspecialchars($n['title']) ?></h4>
              <p><?= htmlspecialchars($n['content']) ?></p>
            </div>
            <?php endforeach; ?>
          </div>
          <a href="notes.php" style="font-size:13px;font-weight:800;color:#555;display:block;margin-top:14px">View all notes →</a>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

</body>
</html>
