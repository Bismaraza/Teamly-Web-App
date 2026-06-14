<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.html'); exit; }
require_once 'php/db.php';

$tasks = [];
if ($conn) {
    $result = $conn->query("SELECT * FROM tasks ORDER BY id DESC");
    if ($result) while ($row = $result->fetch_assoc()) $tasks[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tasks — Teamly</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%;font-family:Inter,sans-serif;background:#f5f5f8;color:#111}
    a{text-decoration:none;color:inherit}

    .app-shell{display:grid;grid-template-columns:240px 1fr;min-height:100vh}

    /* Sidebar */
    .app-sidebar{background:#111;color:#fff;display:flex;flex-direction:column;padding:24px 16px;gap:2px;position:sticky;top:0;height:100vh;overflow-y:auto}
    .brand{display:flex;align-items:center;gap:10px;font-size:18px;font-weight:900;letter-spacing:-.04em;margin-bottom:28px;color:#fff}
    .brand-mark{width:30px;height:30px;border-radius:9px;background:#fff;color:#111;display:grid;place-items:center;font-size:14px;font-weight:900;flex-shrink:0}
    .nav-item{display:flex;align-items:center;gap:10px;padding:11px 13px;border-radius:11px;color:#aaa;font-weight:700;font-size:14px;transition:.18s}
    .nav-item:hover,.nav-item.active{background:rgba(255,255,255,.1);color:#fff}
    .nav-item.logout{margin-top:auto;color:#f55}

    /* Main */
    .app-main{padding:32px 36px;display:flex;flex-direction:column;gap:22px}
    .page-title{font-size:26px;font-weight:900;letter-spacing:-.04em}
    .page-sub{color:#888;font-size:13px;margin-top:3px}

    /* Add form */
    .add-form{background:#fff;border:1px solid #e8e8ec;border-radius:18px;padding:20px;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;box-shadow:0 4px 16px rgba(0,0,0,.04)}
    .field{display:flex;flex-direction:column;gap:6px;flex:1;min-width:160px}
    .field label{font-size:12px;font-weight:800;color:#666;text-transform:uppercase;letter-spacing:.04em}
    .field input,.field select{border:1px solid #e0e0e6;border-radius:11px;padding:10px 13px;font:inherit;font-size:14px;outline:none;background:#fafafa}
    .field input:focus,.field select:focus{border-color:#111;background:#fff}
    .btn-add{background:#111;color:#fff;border:none;border-radius:11px;padding:11px 22px;font:inherit;font-weight:800;font-size:14px;cursor:pointer;white-space:nowrap;height:42px}
    .btn-add:hover{background:#333}

    /* Filter bar */
    .filter-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .filter-bar span{font-size:13px;font-weight:800;color:#888;margin-right:4px}
    .fbtn{border:1.5px solid #e0e0e6;background:#fff;border-radius:99px;padding:7px 16px;font:inherit;font-size:13px;font-weight:800;cursor:pointer;color:#555;transition:.18s}
    .fbtn:hover{border-color:#111;color:#111}
    .fbtn.active{background:#111;color:#fff;border-color:#111}

    /* Board */
    .board{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .col{background:#fff;border:1px solid #e8e8ec;border-radius:18px;padding:18px;min-height:300px;box-shadow:0 4px 16px rgba(0,0,0,.04)}
    .col-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
    .col-title{font-size:14px;font-weight:900}
    .col-count{background:#f3f3f7;color:#666;font-size:12px;font-weight:800;border-radius:99px;padding:2px 9px}
    .col-todo{border-top:4px solid #e0e0e6}
    .col-prog{border-top:4px solid #3b82f6}
    .col-done{border-top:4px solid #10b981}

    .task-card{background:#fafafa;border:1px solid #ededf2;border-radius:13px;padding:13px 14px;margin-bottom:8px;transition:.18s}
    .task-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.07);transform:translateY(-2px)}
    .task-title{font-size:14px;font-weight:700;margin-bottom:8px}
    .task-title.done-text{text-decoration:line-through;opacity:.6}
    .badges{display:flex;gap:6px;flex-wrap:wrap}
    .badge{font-size:11px;font-weight:800;padding:3px 9px;border-radius:99px}
    .b-todo{background:#f3f3f7;color:#555}
    .b-prog{background:#eff6ff;color:#3b82f6}
    .b-done{background:#ecfdf5;color:#10b981}
    .b-high{background:#fff1f1;color:#ef4444}
    .b-normal{background:#f3f3f7;color:#555}
    .b-low{background:#f0fdf4;color:#16a34a}

    .empty{color:#bbb;font-size:13px;font-weight:700;padding:20px 0;text-align:center}
    .task-card{position:relative}
    .task-card:hover .del-btn{opacity:1}
    .del-btn{position:absolute;top:10px;right:10px;background:none;border:none;cursor:pointer;font-size:15px;opacity:0;transition:.18s;padding:2px 5px;border-radius:7px;color:#ccc}
    .del-btn:hover{background:#fff1f1;color:#ef4444}

    @media(max-width:900px){
      .app-shell{grid-template-columns:1fr}
      .app-sidebar{position:relative;height:auto;flex-direction:row;flex-wrap:wrap;padding:12px 16px}
      .brand{margin-bottom:0}
      .nav-item.logout{margin-top:0}
      .app-main{padding:20px 16px}
      .board{grid-template-columns:1fr}
    }
  </style>
</head>
<body>
<div class="app-shell">
  <aside class="app-sidebar">
    <a class="brand" href="dashboard.php"><span class="brand-mark">T</span><span>Teamly</span></a>
    <a class="nav-item" href="dashboard.php">🏠 Dashboard</a>
    <a class="nav-item active" href="tasks.php">✅ Tasks</a>
    <a class="nav-item" href="notes.php">📝 Notes</a>
    <a class="nav-item" href="calendar.php">📅 Calendar</a>
    <a class="nav-item" href="integrations.html">🔗 Integrations</a>
    <a class="nav-item" href="settings.html">⚙️ Settings</a>
    <a class="nav-item logout" href="php/logout.php">🚪 Logout</a>
  </aside>

  <main class="app-main">
    <div>
      <div class="page-title">Task Board</div>
      <div class="page-sub">Manage and track all your tasks</div>
    </div>

    <!-- Add Task -->
    <form class="add-form" action="php/add_task.php" method="POST">
      <div class="field" style="flex:2"><label>Task Title</label><input name="title" placeholder="Enter task title…" required></div>
      <div class="field"><label>Status</label>
        <select name="status">
          <option value="To Do">To Do</option>
          <option value="In Progress">In Progress</option>
          <option value="Done">Done</option>
        </select>
      </div>
      <div class="field"><label>Priority</label>
        <select name="priority">
          <option value="Low">Low</option>
          <option value="Normal" selected>Normal</option>
          <option value="High">High</option>
        </select>
      </div>
      <button class="btn-add" type="submit">+ Add Task</button>
    </form>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <span>Filter:</span>
      <button class="fbtn active" onclick="filterTasks('all',this)">All (<?= count($tasks) ?>)</button>
      <button class="fbtn" onclick="filterTasks('todo',this)">To Do (<?= count(array_filter($tasks,fn($t)=>in_array($t['status'],['To Do','Pending','Open']))) ?>)</button>
      <button class="fbtn" onclick="filterTasks('inprog',this)">In Progress (<?= count(array_filter($tasks,fn($t)=>in_array($t['status'],['In Progress','Working']))) ?>)</button>
      <button class="fbtn" onclick="filterTasks('done',this)">Done (<?= count(array_filter($tasks,fn($t)=>$t['status']==='Done')) ?>)</button>
      <button class="fbtn" onclick="filterTasks('high',this)">🔴 High Priority (<?= count(array_filter($tasks,fn($t)=>$t['priority']==='High')) ?>)</button>
    </div>

    <!-- Board -->
    <div class="board">
      <!-- To Do -->
      <div class="col col-todo">
        <div class="col-head">
          <span class="col-title">📋 To Do</span>
          <span class="col-count" id="cnt-todo">0</span>
        </div>
        <div id="col-todo-body">
          <?php foreach($tasks as $t):
            $isTodo = in_array($t['status'],['To Do','Pending','Open']);
            if(!$isTodo) continue;
            $pc = $t['priority']==='High'?'b-high':($t['priority']==='Low'?'b-low':'b-normal');
          ?>
          <div class="task-card" data-status="todo" data-priority="<?= strtolower($t['priority']) ?>">
            <form method="POST" action="php/delete_task.php" onsubmit="return confirm('Delete this task?')">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <button class="del-btn" type="submit" title="Delete">🗑</button>
            </form>
            <div class="task-title"><?= htmlspecialchars($t['title']) ?></div>
            <div class="badges">
              <span class="badge b-todo"><?= htmlspecialchars($t['status']) ?></span>
              <span class="badge <?= $pc ?>"><?= htmlspecialchars($t['priority']) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
          <div class="empty" id="empty-todo" style="display:none">No tasks here</div>
        </div>
      </div>

      <!-- In Progress -->
      <div class="col col-prog">
        <div class="col-head">
          <span class="col-title">⚙️ In Progress</span>
          <span class="col-count" id="cnt-prog">0</span>
        </div>
        <div id="col-prog-body">
          <?php foreach($tasks as $t):
            $isProg = in_array($t['status'],['In Progress','Working']);
            if(!$isProg) continue;
            $pc = $t['priority']==='High'?'b-high':($t['priority']==='Low'?'b-low':'b-normal');
          ?>
          <div class="task-card" data-status="inprog" data-priority="<?= strtolower($t['priority']) ?>">
            <form method="POST" action="php/delete_task.php" onsubmit="return confirm('Delete this task?')">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <button class="del-btn" type="submit" title="Delete">🗑</button>
            </form>
            <div class="task-title"><?= htmlspecialchars($t['title']) ?></div>
            <div class="badges">
              <span class="badge b-prog"><?= htmlspecialchars($t['status']) ?></span>
              <span class="badge <?= $pc ?>"><?= htmlspecialchars($t['priority']) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
          <div class="empty" id="empty-prog" style="display:none">No tasks here</div>
        </div>
      </div>

      <!-- Done -->
      <div class="col col-done">
        <div class="col-head">
          <span class="col-title">✅ Done</span>
          <span class="col-count" id="cnt-done">0</span>
        </div>
        <div id="col-done-body">
          <?php foreach($tasks as $t):
            if($t['status']!=='Done') continue;
            $pc = $t['priority']==='High'?'b-high':($t['priority']==='Low'?'b-low':'b-normal');
          ?>
          <div class="task-card" data-status="done" data-priority="<?= strtolower($t['priority']) ?>">
            <form method="POST" action="php/delete_task.php" onsubmit="return confirm('Delete this task?')">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <button class="del-btn" type="submit" title="Delete">🗑</button>
            </form>
            <div class="task-title done-text"><?= htmlspecialchars($t['title']) ?></div>
            <div class="badges">
              <span class="badge b-done">Done</span>
              <span class="badge <?= $pc ?>"><?= htmlspecialchars($t['priority']) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
          <div class="empty" id="empty-done" style="display:none">No tasks here</div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
function filterTasks(type, btn) {
  // Active button
  document.querySelectorAll('.fbtn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  const cards = document.querySelectorAll('.task-card');
  cards.forEach(card => {
    const st = card.dataset.status;
    const pr = card.dataset.priority;
    let show = false;
    if (type === 'all')    show = true;
    else if (type === 'todo')   show = st === 'todo';
    else if (type === 'inprog') show = st === 'inprog';
    else if (type === 'done')   show = st === 'done';
    else if (type === 'high')   show = pr === 'high';
    card.style.display = show ? '' : 'none';
  });

  // Update counts & empty states
  ['todo','prog','done'].forEach(col => {
    const colStatus = col === 'prog' ? 'inprog' : col;
    const visible = [...document.querySelectorAll(`.task-card[data-status="${colStatus}"]`)]
      .filter(c => c.style.display !== 'none');
    document.getElementById('cnt-' + col).textContent = visible.length;
    document.getElementById('empty-' + col).style.display = visible.length === 0 ? '' : 'none';
  });
}

// Init counts
filterTasks('all', document.querySelector('.fbtn.active'));
</script>
</body>
</html>
