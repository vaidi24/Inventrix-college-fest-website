<?php
// admin.php
include "db.php";

// ---------- Helpers ----------
function h($v){ return htmlspecialchars($v ?? "", ENT_QUOTES, 'UTF-8'); }

// ---------- Actions ----------
$editing = false;
$editRow = [
  'id' => '',
  'event_name' => '',
  'event_date' => '',
  'event_time' => '',
  'price' => '',
  'venue' => '',
  'rules' => '',
  'capacity' => '',
  'day' => 'day1'
];

// Delete
if (isset($_GET['delete'])) {
  $id = (int) $_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
  header("Location: admin.php");
  exit;
}

// Edit (load row into form)
if (isset($_GET['edit'])) {
  $editing = true;
  $id = (int) $_GET['edit'];
  $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res && $res->num_rows === 1) {
    $editRow = $res->fetch_assoc();
  } else {
    $editing = false; // not found
  }
  $stmt->close();
}

// Add / Update submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id         = $_POST['id'] ?? '';
  $name       = trim($_POST['event_name'] ?? '');
  $date       = $_POST['event_date'] ?? '';
  $time       = $_POST['event_time'] ?? '';
  $price      = $_POST['price'] ?? '0';
  $venue      = trim($_POST['venue'] ?? '');
  $rules      = trim($_POST['rules'] ?? '');
  $capacity   = $_POST['capacity'] ?? '0';
  $day        = $_POST['day'] ?? 'day1';



 if ($id === '') {
    // INSERT
    $stmt = $conn->prepare( "INSERT INTO events (event_name, event_date, event_time, price, venue, rules, capacity, day)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)" );
    // FIXED: rules -> s, capacity -> i  (types = s s s d s s i s)
    $stmt->bind_param("sssdssis", $name, $date, $time, $price, $venue, $rules, $capacity, $day);
  } else {
    // UPDATE
    $stmt = $conn->prepare( "UPDATE events SET event_name=?, event_date=?, event_time=?, price=?, venue=?, rules=?, capacity=?, day=?
                             WHERE id=?");
    // FIXED: rules -> s, capacity -> i  (types = s s s d s s i s i)
    $stmt->bind_param("sssdssisi", $name, $date, $time, $price, $venue, $rules, $capacity, $day, $id);
  }


  $stmt->execute();
  $stmt->close();

  header("Location: admin.php");
  exit;
}

// Fetch all events for list
$all = $conn->query("SELECT * FROM events ORDER BY event_date, event_time, id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
 <meta name="viewport" content="width=380, initial-scale=1.0">
<title>Admin - Manage Events</title>  
<style>
  body{font-family:Arial, sans-serif; background:#f4f4f8; margin:0; padding:30px;}
  h1{margin:0 0 20px; }
  .wrap{max-width:1100px; margin:0 auto;}
  .card{background:#fff; border: radius 1px;2px; box-shadow:0 4px 12px rgba(0,0,0,.08); padding:20px; margin-bottom:24px;}
  .row{display:grid; grid-template-columns:repeat(2,1fr); gap:16px;}
  .row-3{display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;}
  label{font-weight:bold; font-size:14px;}
  input, select, textarea{width:100%; padding:10px; border:1px solid #ddd;  font-size:14px;}
  textarea{min-height:80px;}
  .actions{display:flex; gap:10px; margin-top:12px;}
  .btn{border:none; border-radius:8px; padding:10px 14px; cursor:pointer; font-weight:bold;}
  .primary{background:#2563eb; color:#fff;}
  .muted{background:#e5e7eb;}
  table{width:100%; border-collapse:collapse;}
  th, td{padding:10px; border-bottom:1px solid #eee; text-align:left; font-size:14px;}
  th{background:#111827; color:#fff; position:sticky; top:0;}
  .pill{display:inline-block; padding:4px 10px; border-radius:999px; background:#eef2ff;}
  a.action{padding:6px 10px; background:#111827; color:#fff; border-radius:6px; text-decoration:none; font-size:12px;}
  a.delete{background:#dc2626;}
  .btn1{ border:none; border-radius:8px; padding:10px 14px; cursor:pointer; font-weight:bold; background:#2563eb ; color:white; }
  .btn1 a{text-decoration: none; color:white;}
</style>
</head>
<body>
<div class="wrap">
  <h1>Manage registrations - <button class="btn1" ><a href="admin2.php" >manage registrations</a></button><h1><hr>
  <h1>Manage Events</h1>

  <!-- Form -->
  <div class="card">
    <h2 style="margin-top:0;"><?php echo $editing ? "Edit Event #".h($editRow['id']) : "Add New Event"; ?></h2>
    <form method="POST">
      <input type="hidden" name="id" value="<?php echo h($editRow['id']); ?>">

      <div class="row">
        <div>
          <label>Event Name</label>
          <input type="text" name="event_name" required value="<?php echo h($editRow['event_name']); ?>">
        </div>
        <div class="row-3">
          <div>
            <label>Date</label>
            <input type="date" name="event_date" required value="<?php echo h($editRow['event_date']); ?>">
          </div>
          <div>
            <label>Time</label>
            <input type="time" name="event_time" required value="<?php echo h($editRow['event_time']); ?>">
          </div>
          <div>
            <label>Day</label>
            <select name="day" required>
              <option value="day1" <?php echo ($editRow['day']==='day1'?'selected':''); ?>>Day 1</option>
              <option value="day2" <?php echo ($editRow['day']==='day2'?'selected':''); ?>>Day 2</option>
            </select>
          </div>
        </div>
      </div>

      <div class="row">
        <div>
          <label>Venue</label>
          <input type="text" name="venue" required value="<?php echo h($editRow['venue']); ?>">
        </div>
        <div class="row-3">
          <div>
            <label>Price (₹)</label>
            <input type="number" step="0.01" name="price" required value="<?php echo h($editRow['price']); ?>">
          </div>
          <div>
            <label>Capacity</label>
            <input type="number" name="capacity" required value="<?php echo h($editRow['capacity']); ?>">
          </div>
          <div>
            <label>&nbsp;</label>
            <span class="pill"><?php echo $editing ? "Editing" : "Creating"; ?></span>
          </div>
        </div>
      </div>

      <div>
        <label>Rules / About</label>
        <textarea name="rules"><?php echo h($editRow['rules']); ?></textarea>
      </div>

      <div class="actions">
        <button class="btn primary" type="submit"><?php echo $editing ? "Update Event" : "Add Event"; ?></button>
        <?php if ($editing): ?>
          <a class="btn muted" href="admin.php">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- List -->
  <div class="card">
    <h2 style="margin-top:0;">Existing Events</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Name</th><th>Date</th><th>Time</th><th>Price</th><th>Venue</th><th>Cap.</th><th>Day</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $all->fetch_assoc()): ?>
          <tr>
            <td><?php echo h($row['id']); ?></td>
            <td><?php echo h($row['event_name']); ?></td>
            <td><?php echo h($row['event_date']); ?></td>
            <td><?php echo h($row['event_time']); ?></td>
            <td><?php echo h($row['price']); ?></td>
            <td><?php echo h($row['venue']); ?></td>
            <td><?php echo h($row['capacity']); ?></td>
            <td><?php echo h($row['day']); ?></td>
            <td>
              <a class="action" href="?edit=<?php echo h($row['id']); ?>">Edit</a>
              <a class="action delete" href="?delete=<?php echo h($row['id']); ?>" onclick="return confirm('Delete this event?');">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<!-- new part -->
   <!-- Expense Form -->
  <!-- <div class="card">
    <h2 style="margin-top:0;">Add Expense</h2>
    <form id="expenseForm" enctype="multipart/form-data">
      <div class="row">
        <div>
          <label>Expense Details</label>
          <textarea name="expense_detail" required></textarea>
        </div>
        <div>
          <label>Amount (₹)</label>
          <input type="number" name="amount" step="0.01" required>
        </div>
        <div>
          <label>Receipt</label>
          <input type="file" name="receipt" accept="image/*,application/pdf" required>
        </div>
      </div>
      <div class="actions">
        <button type="submit" class="btn primary">Submit Expense</button>
      </div>
    </form>

    <div id="expenseList" style="margin-top:20px;">
      <h3>Submitted Expenses</h3>
      <ul style="list-style:none; padding:0;" id="expenseItems"></ul>
    </div>
  </div> -->

  <script>
    const expenseForm = document.getElementById('expenseForm');
    const expenseItems = document.getElementById('expenseItems');

    expenseForm.addEventListener('submit', function(e){
      e.preventDefault();
      
      const detail = this.expense_detail.value;
      const amount = this.amount.value;
      const file = this.receipt.files[0] ? this.receipt.files[0].name : "No file";

      // Create a new expense entry
      const li = document.createElement('li');
      li.style.padding = "10px";
      li.style.borderBottom = "1px solid #ddd";
      li.innerHTML = `
        <strong>Detail:</strong> ${detail} <br>
        <strong>Amount:</strong> ₹${amount} <br>
        <strong>Receipt:</strong> ${file}
      `;
      expenseItems.appendChild(li);

      // Reset form
      this.reset();
    });
  </script>

</body>
</html>
