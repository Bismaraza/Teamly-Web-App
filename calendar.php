<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.html'); exit; }
require_once 'php/db.php';

// Auto-create reminders table if not exists
if ($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS reminders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        reminder_date DATE NOT NULL,
        reminder_time TIME DEFAULT '09:00:00',
        color VARCHAR(20) DEFAULT 'blue',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

$reminders = [];
if ($conn) {
    $r = $conn->query("SELECT * FROM reminders ORDER BY reminder_date ASC, reminder_time ASC");
    if ($r) while ($row = $r->fetch_assoc()) $reminders[] = $row;
}

// Group reminders by date for quick lookup
$byDate = [];
foreach ($reminders as $rem) {
    $byDate[$rem['reminder_date']][] = $rem;
}

$month = isset($_GET['m']) ? intval($_GET['m']) : intval(date('m'));
$year  = isset($_GET['y']) ? intval($_GET['y']) : intval(date('Y'));
if ($month < 1) { $month = 12; $year--; }
if ($month > 12){ $month = 1;  $year++; }

$daysInMonth  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstWeekday = date('w', mktime(0,0,0,$month,1,$year)); // 0=Sun
$monthName    = date('F Y', mktime(0,0,0,$month,1,$year));
$today        = date('Y-m-d');
$prevM = $month-1 < 1  ? 12 : $month-1;
$prevY = $month-1 < 1  ? $year-1 : $year;
$nextM = $month+1 > 12 ? 1  : $month+1;
$nextY = $month+1 > 12 ? $year+1 : $year;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendar — Teamly</title>
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
    .app-main{padding:28px 32px;display:flex;flex-direction:column;gap:20px}
    .page-title{font-size:26px;font-weight:900;letter-spacing:-.04em}
    .page-sub{color:#888;font-size:13px;margin-top:3px}

    /* Two column layout */
    .content-grid{display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start}

    /* Calendar */
    .cal-wrap{background:#fff;border:1px solid #e8e8ec;border-radius:20px;padding:22px;box-shadow:0 4px 16px rgba(0,0,0,.04)}
    .cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
    .cal-nav h2{font-size:18px;font-weight:900;letter-spacing:-.03em}
    .cal-nav a{background:#f3f3f7;border:none;border-radius:10px;padding:8px 14px;font:inherit;font-weight:800;font-size:13px;cursor:pointer;color:#555;transition:.18s;text-decoration:none}
    .cal-nav a:hover{background:#111;color:#fff}
    .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px}
    .cal-day-name{text-align:center;font-size:11px;font-weight:800;color:#aaa;padding:6px 0;text-transform:uppercase;letter-spacing:.05em}
    .cal-cell{min-height:80px;border-radius:12px;padding:8px;border:1.5px solid transparent;transition:.18s;cursor:pointer;position:relative}
    .cal-cell:hover{background:#f7f7fb;border-color:#e0e0e8}
    .cal-cell.today{background:#f0f0ff;border-color:#6366f1}
    .cal-cell.has-reminder{border-color:#e0e0e8;background:#fafafa}
    .cal-cell.other-month{opacity:.3}
    .cal-cell.selected{background:#111;border-color:#111}
    .cal-cell.selected .day-num{color:#fff}
    .day-num{font-size:13px;font-weight:800;color:#333;margin-bottom:4px}
    .cal-cell.today .day-num{color:#6366f1}
    .cal-cell.selected .day-num{color:#fff}
    .rem-dot{display:flex;align-items:center;gap:3px;margin-bottom:2px}
    .rem-dot span{display:block;font-size:10px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70px}
    .dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
    .dot-blue{background:#3b82f6}
    .dot-green{background:#10b981}
    .dot-red{background:#ef4444}
    .dot-orange{background:#f59e0b}
    .dot-purple{background:#8b5cf6}

    /* Right panel */
    .right-panel{display:flex;flex-direction:column;gap:16px}

    /* Add reminder form */
    .add-form{background:#fff;border:1px solid #e8e8ec;border-radius:20px;padding:20px;box-shadow:0 4px 16px rgba(0,0,0,.04)}
    .add-form h3{font-size:15px;font-weight:900;margin-bottom:14px}
    .field{display:flex;flex-direction:column;gap:5px;margin-bottom:12px}
    .field label{font-size:11px;font-weight:800;color:#888;text-transform:uppercase;letter-spacing:.04em}
    .field input,.field select{border:1px solid #e0e0e6;border-radius:10px;padding:9px 12px;font:inherit;font-size:14px;outline:none;background:#fafafa}
    .field input:focus,.field select:focus{border-color:#111;background:#fff}
    .color-row{display:flex;gap:8px;flex-wrap:wrap}
    .color-opt{width:26px;height:26px;border-radius:50%;cursor:pointer;border:3px solid transparent;transition:.15s}
    .color-opt:hover,.color-opt.selected{border-color:#111;transform:scale(1.15)}
    .btn-save{width:100%;background:#111;color:#fff;border:none;border-radius:11px;padding:11px;font:inherit;font-weight:800;font-size:14px;cursor:pointer;margin-top:4px}
    .btn-save:hover{background:#333}

    /* Reminders list */
    .rem-list{background:#fff;border:1px solid #e8e8ec;border-radius:20px;padding:20px;box-shadow:0 4px 16px rgba(0,0,0,.04)}
    .rem-list h3{font-size:15px;font-weight:900;margin-bottom:14px}
    .rem-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;background:#fafafa;border:1px solid #ededf2;margin-bottom:8px;transition:.18s}
    .rem-item:hover{box-shadow:0 4px 12px rgba(0,0,0,.06)}
    .rem-item:hover .del-btn{opacity:1}
    .rem-color{width:10px;height:10px;border-radius:50%;flex-shrink:0}
    .rem-info{flex:1;min-width:0}
    .rem-title{font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .rem-date{font-size:11px;color:#aaa;font-weight:700;margin-top:2px}
    .del-btn{background:none;border:none;cursor:pointer;font-size:14px;opacity:0;transition:.18s;padding:3px 5px;border-radius:7px;color:#ccc;flex-shrink:0}
    .del-btn:hover{background:#fff1f1;color:#ef4444}
    .no-rem{text-align:center;padding:24px 0;color:#bbb;font-size:13px;font-weight:700}

    @media(max-width:1100px){.content-grid{grid-template-columns:1fr}}
    @media(max-width:900px){
      .app-shell{grid-template-columns:1fr}
      .app-sidebar{position:relative;height:auto;flex-direction:row;flex-wrap:wrap;padding:12px 16px}
      .brand{margin-bottom:0}
      .nav-item.logout{margin-top:0}
      .app-main{padding:16px}
    }
  </style>
</head>
<body>
<div class="app-shell">
  <aside class="app-sidebar">
    <a class="brand" href="dashboard.php"><span class="brand-mark">T</span><span>Teamly</span></a>
    <a class="nav-item" href="dashboard.php">🏠 Dashboard</a>
    <a class="nav-item" href="tasks.php">✅ Tasks</a>
    <a class="nav-item" href="notes.php">📝 Notes</a>
    <a class="nav-item active" href="calendar.php">📅 Calendar</a>
    <a class="nav-item" href="integrations.html">🔗 Integrations</a>
    <a class="nav-item" href="settings.html">⚙️ Settings</a>
    <a class="nav-item logout" href="php/logout.php">🚪 Logout</a>
  </aside>

  <main class="app-main">
    <div>
      <div class="page-title">Calendar</div>
      <div class="page-sub">Plan and track your reminders</div>
    </div>

    <div class="content-grid">

      <!-- Calendar -->
      <div class="cal-wrap">
        <div class="cal-nav">
          <a href="calendar.php?m=<?= $prevM ?>&y=<?= $prevY ?>">← Prev</a>
          <h2><?= $monthName ?></h2>
          <a href="calendar.php?m=<?= $nextM ?>&y=<?= $nextY ?>">Next →</a>
        </div>

        <div class="cal-grid">
          <!-- Day names -->
          <?php foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
            <div class="cal-day-name"><?= $d ?></div>
          <?php endforeach; ?>

          <!-- Empty cells before first day -->
          <?php for($i=0;$i<$firstWeekday;$i++): ?>
            <div class="cal-cell other-month"></div>
          <?php endfor; ?>

          <!-- Days -->
          <?php for($d=1;$d<=$daysInMonth;$d++):
            $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $isToday = $dateKey === $today;
            $hasRem  = isset($byDate[$dateKey]);
            $cls = $isToday ? 'today' : '';
            if($hasRem) $cls .= ' has-reminder';
          ?>
          <div class="cal-cell <?= $cls ?>" onclick="selectDate('<?= $dateKey ?>')">
            <div class="day-num"><?= $d ?></div>
            <?php if($hasRem): ?>
              <?php foreach(array_slice($byDate[$dateKey],0,2) as $rem): ?>
              <div class="rem-dot">
                <span class="dot dot-<?= htmlspecialchars($rem['color']) ?>"></span>
                <span><?= htmlspecialchars($rem['title']) ?></span>
              </div>
              <?php endforeach; ?>
              <?php if(count($byDate[$dateKey])>2): ?>
                <div style="font-size:10px;color:#aaa;font-weight:800">+<?= count($byDate[$dateKey])-2 ?> more</div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Right panel -->
      <div class="right-panel">

        <!-- Add Reminder Form -->
        <div class="add-form">
          <h3>➕ Add Reminder</h3>
          <form method="POST" action="php/add_reminder.php">
            <div class="field">
              <label>Title</label>
              <input type="text" name="title" id="rem-title" placeholder="e.g. Submit assignment" required>
            </div>
            <div class="field">
              <label>Date</label>
              <input type="date" name="reminder_date" id="rem-date" value="<?= $today ?>" required>
            </div>
            <div class="field">
              <label>Time</label>
              <input type="time" name="reminder_time" value="09:00">
            </div>
            <div class="field">
              <label>Color</label>
              <div class="color-row">
                <?php foreach(['blue'=>'#3b82f6','green'=>'#10b981','red'=>'#ef4444','orange'=>'#f59e0b','purple'=>'#8b5cf6'] as $name=>$hex): ?>
                <div class="color-opt <?= $name==='blue'?'selected':'' ?>"
                     style="background:<?= $hex ?>"
                     onclick="pickColor('<?= $name ?>',this)"
                     title="<?= ucfirst($name) ?>"></div>
                <?php endforeach; ?>
                <input type="hidden" name="color" id="color-val" value="blue">
              </div>
            </div>
            <button class="btn-save" type="submit">Save Reminder</button>
          </form>
        </div>

        <!-- All Reminders List -->
        <div class="rem-list">
          <h3>📋 All Reminders (<?= count($reminders) ?>)</h3>
          <?php if(empty($reminders)): ?>
            <div class="no-rem">No reminders yet</div>
          <?php else: ?>
            <?php foreach($reminders as $rem):
              $isPast = $rem['reminder_date'] < $today;
            ?>
            <div class="rem-item" style="<?= $isPast?'opacity:.55':'' ?>">
              <div class="rem-color dot-<?= htmlspecialchars($rem['color']) ?>" style="background:<?= ['blue'=>'#3b82f6','green'=>'#10b981','red'=>'#ef4444','orange'=>'#f59e0b','purple'=>'#8b5cf6'][$rem['color']] ?? '#3b82f6' ?>"></div>
              <div class="rem-info">
                <div class="rem-title"><?= htmlspecialchars($rem['title']) ?></div>
                <div class="rem-date">
                  <?= date('M j, Y', strtotime($rem['reminder_date'])) ?>
                  <?php if($rem['reminder_time']): ?> · <?= date('g:i A', strtotime($rem['reminder_time'])) ?><?php endif; ?>
                  <?php if($isPast): ?> <span style="color:#ef4444">· Past</span><?php endif; ?>
                </div>
              </div>
              <form method="POST" action="php/delete_reminder.php" onsubmit="return confirm('Delete this reminder?')">
                <input type="hidden" name="id" value="<?= $rem['id'] ?>">
                <button class="del-btn" type="submit" title="Delete">🗑</button>
              </form>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </main>
</div>

<script>
function selectDate(dateStr) {
  document.getElementById('rem-date').value = dateStr;
  document.getElementById('rem-title').focus();
  // Highlight selected cell
  document.querySelectorAll('.cal-cell').forEach(c => c.classList.remove('selected'));
  event.currentTarget.classList.add('selected');
}

function pickColor(name, el) {
  document.querySelectorAll('.color-opt').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('color-val').value = name;
}
</script>
</body>
</html>
