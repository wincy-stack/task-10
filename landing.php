<?php
require_once 'insert.php';
require_once 'update.php';
require_once 'delete.php';
require_once 'select.php';

$editUser = null;
if (isset($_GET['edit'])) {
    $u_id = intval($_GET['edit']);
    $stmt = $pdo->prepare(
        'SELECT u.*, o.product, o.amount
         FROM users u
         LEFT JOIN orders o ON u.user_id = o.user_id
         WHERE u.user_id = ?'
    );
    $stmt->execute([$u_id]);
    $editUser = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Management</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:        #f4f6fb;
    --surface:   #ffffff;
    --border:    #e2e6f0;
    --border2:   #cdd3e0;
    --accent:    #1a7a5e;
    --accent-lt: #e6f5f0;
    --accent2:   #5b3de8;
    --accent2-lt:#ede9fd;
    --danger:    #d93b55;
    --danger-lt: #fdeef1;
    --warn:      #c47c0a;
    --warn-lt:   #fef6e4;
    --text:      #1a1d2e;
    --text2:     #4a4f6a;
    --muted:     #8a90aa;
    --radius:    12px;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DM Mono', monospace;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    overflow-x: hidden;
  }

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(ellipse 55% 35% at 5% 0%, rgba(26,122,94,.06) 0%, transparent 55%),
      radial-gradient(ellipse 45% 45% at 95% 90%, rgba(91,61,232,.05) 0%, transparent 55%);
    pointer-events: none;
    z-index: 0;
  }

  .page-wrap {
    position: relative;
    z-index: 1;
    max-width: 1100px;
    margin: 0 auto;
    padding: 40px 24px 80px;
  }

  /* ── Header ── */
  header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 40px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border);
    animation: fadeDown .45s ease both;
  }

  .logo-block .eyebrow {
    font-size: 10px;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 4px;
  }

  .logo-block h1 {
    font-family: 'Syne', sans-serif;
    font-size: clamp(22px, 4vw, 32px);
    font-weight: 800;
    letter-spacing: -.025em;
    color: var(--text);
    line-height: 1;
  }

  .badge {
    font-size: 11px;
    padding: 5px 12px;
    border-radius: 99px;
    background: var(--accent-lt);
    color: var(--accent);
    border: 1px solid rgba(26,122,94,.2);
    letter-spacing: .05em;
    font-weight: 500;
  }

  /* ── Stats ── */
  .stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
    margin-bottom: 36px;
    animation: fadeUp .45s .08s ease both;
  }

  .stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px 22px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    transition: border-color .2s, box-shadow .2s, transform .2s;
  }

  .stat-card:hover {
    border-color: var(--accent);
    box-shadow: 0 4px 16px rgba(26,122,94,.10);
    transform: translateY(-2px);
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), var(--accent2));
  }

  .stat-label {
    font-size: 10px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 8px;
  }

  .stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 26px;
    font-weight: 800;
    color: var(--text);
  }

  /* ── Layout ── */
  .layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
    align-items: start;
    animation: fadeUp .45s .16s ease both;
  }

  @media (max-width: 800px) { .layout { grid-template-columns: 1fr; } }

  /* ── Form card ── */
  .form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 26px;
    position: sticky;
    top: 24px;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
  }

  .card-title {
    font-family: 'Syne', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .dot-green  { display:inline-block; width:7px; height:7px; border-radius:50%; background:#1a7a5e; box-shadow:0 0 0 3px rgba(26,122,94,.15); }
  .dot-purple { display:inline-block; width:7px; height:7px; border-radius:50%; background:#5b3de8; box-shadow:0 0 0 3px rgba(91,61,232,.12); }

  .field-group { display: flex; flex-direction: column; gap: 14px; }
  .field       { display: flex; flex-direction: column; gap: 5px; }

  .field label {
    font-size: 10px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--text2);
    font-weight: 500;
  }

  .field input {
    background: var(--bg);
    border: 1px solid var(--border2);
    border-radius: 8px;
    padding: 10px 13px;
    color: var(--text);
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
    width: 100%;
  }

  .field input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(26,122,94,.1);
    background: #fff;
  }

  .field input::placeholder { color: var(--muted); }

  .btn-row { display: flex; gap: 10px; margin-top: 20px; }

  .btn {
    flex: 1;
    padding: 11px 16px;
    border: none;
    border-radius: 8px;
    font-family: 'Syne', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .04em;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    transition: opacity .15s, transform .15s, box-shadow .15s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }

  .btn:hover { opacity: .88; transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }

  .btn-add {
    background: linear-gradient(135deg, #1a7a5e, #0fa870);
    color: #fff;
    box-shadow: 0 2px 10px rgba(26,122,94,.25);
  }

  .btn-update {
    background: linear-gradient(135deg, #c47c0a, #e8920e);
    color: #fff;
    box-shadow: 0 2px 10px rgba(196,124,10,.22);
  }

  .btn-cancel {
    background: var(--bg);
    color: var(--muted);
    border: 1px solid var(--border2);
    flex: 0 0 auto;
    padding: 11px 18px;
    box-shadow: none;
  }

  /* ── Table card ── */
  .table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
  }

  .table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1px solid var(--border);
    background: rgba(244,246,251,.6);
  }

  .count-pill {
    font-size: 11px;
    padding: 3px 10px;
    background: var(--accent2-lt);
    color: var(--accent2);
    border: 1px solid rgba(91,61,232,.18);
    border-radius: 99px;
    font-weight: 500;
  }

  .table-wrap { overflow-x: auto; }

  table { width: 100%; border-collapse: collapse; font-size: 13px; }

  thead th {
    padding: 11px 16px;
    text-align: left;
    font-size: 10px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--muted);
    background: rgba(244,246,251,.8);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
  }

  tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
  tbody tr:last-child { border-bottom: none; }
  tbody tr:hover { background: #f8faff; }
  tbody td { padding: 13px 16px; vertical-align: middle; }

  .id-chip {
    display: inline-block;
    padding: 2px 9px;
    background: var(--bg);
    border: 1px solid var(--border2);
    border-radius: 6px;
    font-size: 11px;
    color: var(--muted);
  }

  .user-name  { font-family: 'Syne', sans-serif; font-weight: 700; color: var(--text); }
  .user-email { font-size: 11px; color: var(--muted); margin-top: 2px; }

  .product-tag {
    display: inline-block;
    padding: 3px 10px;
    background: var(--accent-lt);
    color: var(--accent);
    border: 1px solid rgba(26,122,94,.18);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
  }

  .amount {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 14px;
    color: var(--text);
  }

  .action-cell { display: flex; gap: 8px; align-items: center; }

  .action-btn {
    padding: 5px 13px;
    border-radius: 6px;
    font-size: 11px;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    letter-spacing: .04em;
    text-decoration: none;
    transition: opacity .12s, transform .12s;
    white-space: nowrap;
    display: inline-block;
  }

  .action-btn:hover { opacity: .8; transform: translateY(-1px); }

  .btn-edit   { background: var(--warn-lt);   color: var(--warn);   border: 1px solid rgba(196,124,10,.22); }
  .btn-delete { background: var(--danger-lt); color: var(--danger); border: 1px solid rgba(217,59,85,.2);  }

  .empty-row td { text-align: center; padding: 56px; color: var(--muted); font-size: 13px; }

  @keyframes fadeDown {
    from { opacity: 0; transform: translateY(-14px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  tbody tr { animation: fadeUp .3s ease both; }
  tbody tr:nth-child(1)  { animation-delay: .04s; }
  tbody tr:nth-child(2)  { animation-delay: .08s; }
  tbody tr:nth-child(3)  { animation-delay: .12s; }
  tbody tr:nth-child(4)  { animation-delay: .16s; }
  tbody tr:nth-child(5)  { animation-delay: .20s; }
  tbody tr:nth-child(6)  { animation-delay: .24s; }
  tbody tr:nth-child(7)  { animation-delay: .28s; }
  tbody tr:nth-child(8)  { animation-delay: .32s; }
  tbody tr:nth-child(9)  { animation-delay: .36s; }
  tbody tr:nth-child(10) { animation-delay: .40s; }
</style>
</head>
<body>
<div class="page-wrap">

  <header>
    <div class="logo-block">
      <div class="eyebrow">// management console</div>
      <h1>Order System</h1>
    </div>
    <div class="badge">PDO CRUD</div>
  </header>

  <?php
    $totalUsers   = count(array_unique(array_column($users, 'user_id')));
    $totalOrders  = count($users);
    $totalRevenue = array_sum(array_column($users, 'amount'));
  ?>
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-label">Total Users</div>
      <div class="stat-value"><?= $totalUsers ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Orders</div>
      <div class="stat-value"><?= $totalOrders ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Revenue</div>
      <div class="stat-value">$<?= number_format($totalRevenue, 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Avg Order</div>
      <div class="stat-value">$<?= $totalOrders ? number_format($totalRevenue / $totalOrders, 0) : '0' ?></div>
    </div>
  </div>

  <div class="layout">

    <div class="form-card">
      <div class="card-title">
        <span class="dot-green"></span>
        <?= $editUser ? 'Edit Record' : 'New Record' ?>
      </div>
      <form method="POST">
        <?php if ($editUser): ?>
          <input type="hidden" name="user_id" value="<?= $editUser['user_id'] ?>">
        <?php endif; ?>
        <div class="field-group">
          <div class="field">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Jane Doe"
                   value="<?= htmlspecialchars($editUser['name'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="jane@example.com"
                   value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Product</label>
            <input type="text" name="product" placeholder="Product name"
                   value="<?= htmlspecialchars($editUser['product'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Amount ($)</label>
            <input type="number" step="0.01" name="amount" placeholder="0.00"
                   value="<?= htmlspecialchars($editUser['amount'] ?? '') ?>" required>
          </div>
        </div>
        <div class="btn-row">
          <button type="submit"
                  name="<?= $editUser ? 'update' : 'add' ?>"
                  class="btn <?= $editUser ? 'btn-update' : 'btn-add' ?>">
            <?= $editUser ? '↑ Update Record' : '+ Add Record' ?>
          </button>
          <?php if ($editUser): ?>
            <a href="landing.php" class="btn btn-cancel">✕</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="table-card">
      <div class="table-header">
        <div class="card-title"><span class="dot-purple"></span>Records</div>
        <div class="count-pill"><?= count($users) ?> entries</div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Product</th>
              <th>Amount</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($users)): ?>
              <tr class="empty-row">
                <td colspan="5">No records yet — add your first entry</td>
              </tr>
            <?php else: ?>
              <?php foreach ($users as $user): ?>
              <tr>
                <td><span class="id-chip">#<?= $user['user_id'] ?></span></td>
                <td>
                  <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                  <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                </td>
                <td><span class="product-tag"><?= htmlspecialchars($user['product']) ?></span></td>
                <td><span class="amount">$<?= number_format($user['amount'], 2) ?></span></td>
                <td>
                  <div class="action-cell">
                    <a href="?edit=<?= $user['user_id'] ?>" class="action-btn btn-edit">Edit</a>
                    <a href="?delete=<?= $user['user_id'] ?>"
                       class="action-btn btn-delete"
                       onclick="return confirm('Delete this record?')">Delete</a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
</body>
</html>