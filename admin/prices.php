<?php
// ============================================================
//  H.A.P.A.G. — Admin: Bantay Presyo Price Manager
//  admin/prices.php
// ============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login(true);  // admin only

$pdo  = db();
$msg  = '';

// Handle form submit (upsert)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = clean(post('item_name'));
    $category  = clean(post('category', 'other'));
    $price_min = (float) post('price_min');
    $price_max = (float) post('price_max');
    $unit      = clean(post('unit', '1 kg'));

    if ($item_name) {
        $exist = $pdo->prepare('SELECT id FROM food_prices WHERE item_name = ? LIMIT 1');
        $exist->execute([$item_name]);
        $row = $exist->fetch();
        if ($row) {
            $pdo->prepare('UPDATE food_prices SET category=?,price_min=?,price_max=?,unit=?,updated_at=NOW() WHERE id=?')
                ->execute([$category, $price_min, $price_max, $unit, $row['id']]);
            $msg = "✅ Updated: $item_name";
        } else {
            $pdo->prepare('INSERT INTO food_prices (item_name,category,price_min,price_max,unit) VALUES (?,?,?,?,?)')
                ->execute([$item_name, $category, $price_min, $price_max, $unit]);
            $msg = "✅ Added: $item_name";
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($del = (int) get_param('delete'))) {
    $pdo->prepare('DELETE FROM food_prices WHERE id = ?')->execute([$del]);
    $msg = '🗑 Price entry deleted.';
}

// Fetch all prices
$prices = $pdo->query(
    'SELECT * FROM food_prices ORDER BY category, item_name'
)->fetchAll();

$categories = ['fish','meat','vegetable','grain','condiment','fruit','dairy','other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin — Bantay Presyo Prices · H.A.P.A.G.</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', system-ui, sans-serif; background: #f4f6f0; color: #1a1a1a; }
    .topbar { background: #2d6a4f; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
    .topbar h1 { font-size: 1.1rem; font-weight: 600; letter-spacing: .03em; }
    .topbar a { color: #a8d5b5; font-size: .85rem; text-decoration: none; }
    .container { max-width: 1100px; margin: 32px auto; padding: 0 16px; }
    .msg { background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 8px; padding: 10px 16px; margin-bottom: 20px; font-size: .9rem; }
    .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 6px rgba(0,0,0,.07); margin-bottom: 28px; }
    h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 18px; color: #2d6a4f; }
    .form-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: end; }
    label { font-size: .78rem; font-weight: 600; color: #555; display: block; margin-bottom: 4px; }
    input, select { width: 100%; padding: 8px 10px; border: 1px solid #d0d5dd; border-radius: 7px; font-size: .88rem; }
    .btn-add { background: #2d6a4f; color: #fff; border: none; border-radius: 7px; padding: 9px 18px; cursor: pointer; font-size: .88rem; white-space: nowrap; }
    .btn-add:hover { background: #1b4332; }
    table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    thead th { text-align: left; padding: 10px 12px; background: #f0f4ef; color: #444; font-weight: 600; border-bottom: 2px solid #d9e4d7; }
    tbody tr:hover { background: #f9fbf8; }
    tbody td { padding: 9px 12px; border-bottom: 1px solid #eaefea; }
    .cat-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: .75rem; font-weight: 600; text-transform: uppercase; }
    .cat-fish { background: #dbeafe; color: #1d4ed8; }
    .cat-meat { background: #fee2e2; color: #b91c1c; }
    .cat-vegetable { background: #d1fae5; color: #065f46; }
    .cat-grain { background: #fef3c7; color: #92400e; }
    .cat-condiment { background: #ede9fe; color: #5b21b6; }
    .cat-fruit { background: #fce7f3; color: #9d174d; }
    .cat-dairy { background: #e0f2fe; color: #0369a1; }
    .cat-other { background: #f3f4f6; color: #374151; }
    .btn-del { background: none; border: none; color: #dc2626; cursor: pointer; font-size: .88rem; text-decoration: underline; }
    @media (max-width: 768px) {
      .form-grid { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>

<div class="topbar">
  <h1>🌿 H.A.P.A.G. · Admin Panel — Bantay Presyo Prices</h1>
  <div>
    <a href="/hapag/index.php">← Back to Site</a>
    &nbsp;&nbsp;
    <a href="/hapag/api/logout.php">Log Out</a>
  </div>
</div>

<div class="container">

  <?php if ($msg): ?>
  <div class="msg"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <!-- Add / Update Price -->
  <div class="card">
    <h2>Add or Update a Price Entry</h2>
    <form method="POST">
      <div class="form-grid">
        <div>
          <label>Item Name</label>
          <input type="text" name="item_name" placeholder="e.g. Bangus (medium, 1pc)" required />
        </div>
        <div>
          <label>Category</label>
          <select name="category">
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c ?>"><?= ucfirst($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Min Price (₱)</label>
          <input type="number" name="price_min" step="0.01" min="0" placeholder="95" />
        </div>
        <div>
          <label>Max Price (₱)</label>
          <input type="number" name="price_max" step="0.01" min="0" placeholder="120" />
        </div>
        <div>
          <label>Unit</label>
          <input type="text" name="unit" placeholder="1 pc" />
        </div>
        <div>
          <label>&nbsp;</label>
          <button class="btn-add" type="submit">Save</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Price Table -->
  <div class="card">
    <h2>Current Prices (<?= count($prices) ?> entries)</h2>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Item Name</th>
          <th>Category</th>
          <th>Price Min</th>
          <th>Price Max</th>
          <th>Unit</th>
          <th>Last Updated</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($prices as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= htmlspecialchars($p['item_name']) ?></td>
          <td><span class="cat-badge cat-<?= $p['category'] ?>"><?= $p['category'] ?></span></td>
          <td>₱<?= number_format($p['price_min'], 2) ?></td>
          <td>₱<?= number_format($p['price_max'], 2) ?></td>
          <td><?= htmlspecialchars($p['unit']) ?></td>
          <td><?= date('M j, Y', strtotime($p['updated_at'])) ?></td>
          <td>
            <a href="?delete=<?= $p['id'] ?>"
               onclick="return confirm('Delete this price entry?')"
               class="btn-del">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
