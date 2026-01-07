<?php
session_start();
include 'datas.php';

// Restrict access to students only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Fetch Receipts
$sql = "
    SELECT s.id AS student_id, s.course, s.status, 
           r.id AS receipt_id, r.registration_fee, 
           r.first_installment, r.second_installment, r.third_installment, 
           r.mpesa_code, r.created_at
    FROM students s
    LEFT JOIN receipts r ON s.id = r.student_id
    WHERE s.user_id = ? AND r.id IS NOT NULL
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>User Dashboard - Receipts | Ubunifu Learning Hub</title>

<!-- Boxicons for icons (you already used it) -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
/* ===== RESET ===== */
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', Arial, sans-serif; }

/* ===== THEME ===== */
:root{
  --green-700:#046307;
  --green-600:#058c15;
  --green-100:#dff5de;
  --muted:#034f09;
  --card-bg:#fbfffb;
  --shadow: 0 10px 30px rgba(0,0,0,.14);
}

/* ===== BODY ===== */
body{
  background: linear-gradient(135deg,#d7f5d6,#a5d9ba);
  min-height:100vh;
  padding-top:120px;
  color:#123;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  display:flex;
  flex-direction:column;
}

/* ===== HEADER ===== */
.header {
  width:100%;
  position:fixed;
  top:0; left:0;
  z-index:999;
  display:flex;
  justify-content:center;
  align-items:center;
  gap:12px;
  padding:16px 20px;
  background:var(--green-700);
  color:#fff;
  box-shadow:0 3px 10px rgba(0,0,0,0.2);
}
.header img { width:60px; display:block; }
.header h1 { font-size:26px; margin:0; font-weight:700; }

/* ===== MAIN WRAPPER ===== */
.wrapper {
  width:100%;
  display:flex;
  justify-content:center;
  padding:20px;
}

/* ===== CARD ===== */
.card {
  width:90%;
  max-width:920px;
  background:#fff;
  border-radius:20px;
  padding:34px;
  box-shadow:var(--shadow);
  text-align:center;
  animation: fadeInUp .6s ease both;
}

/* Heading */
.card h2 { color:var(--green-700); font-size:28px; margin-bottom:12px; }
.card p.lead { color:var(--muted); margin-bottom:18px; font-weight:600; }

/* Action buttons */
.actions { display:flex; justify-content:center; gap:14px; margin:20px 0 18px; flex-wrap:wrap; }
.btn {
  display:inline-flex; align-items:center; gap:8px;
  padding:12px 20px; border-radius:10px; text-decoration:none; font-weight:600;
  transition:transform .18s ease, box-shadow .18s ease;
}
.btn.primary { background:var(--green-700); color:#fff; box-shadow:0 6px 18px rgba(4,99,7,0.12); }
.btn.primary:hover { transform:translateY(-3px); background:var(--green-600); }
.btn.danger { background:#d9534f; color:#fff; box-shadow:0 6px 18px rgba(217,83,79,0.12); }
.btn.danger:hover { transform:translateY(-3px); background:#c9302c; }

/* View receipts button (outlined) */
.view-btn {
  padding:12px 20px; border-radius:10px; background:#fff;
  border:2px solid var(--green-700); color:var(--green-700); font-weight:700; cursor:pointer;
  transition: transform .18s ease, background .18s ease, color .18s ease;
}
.view-btn:hover { transform:scale(1.03); background:var(--green-700); color:white; }

/* ===== POPUP (Receipt) ===== */
.popup-overlay {
  display:none;
  position:fixed; inset:0;
  background: rgba(0,0,0,0.55);
  z-index:10000;
  align-items:center;
  justify-content:center;
  padding:18px;
}

.popup {
  background:var(--card-bg);
  border-radius:18px;
  width:95%;
  max-width:980px;
  max-height:86vh;
  overflow:auto;
  padding:28px;
  box-shadow:0 14px 40px rgba(0,0,0,0.25);
  animation: fadeInUp .5s ease both;
}

/* Close button */
.popup .close {
  float:right; font-size:26px; cursor:pointer; color:var(--green-700); font-weight:800;
}

/* Table */
.table-wrap { overflow-x:auto; margin-top:8px; }
.receipt-table {
  width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden;
  font-size:14px;
}
.receipt-table thead th {
  background:var(--green-100); color:var(--muted); font-weight:700; text-align:left; padding:12px;
  border-bottom:2px solid #cdeccc;
}
.receipt-table tbody td {
  padding:12px; border-bottom:1px solid #eef6ef; vertical-align:middle;
}
.receipt-table tbody tr:hover {
  background:#eefbea; transform:scale(1.01); transition:all .18s ease;
}

/* small responsive table adjustments */
@media (max-width: 900px) {
  .receipt-table thead th { font-size:13px; padding:10px; }
  .receipt-table tbody td { padding:10px; font-size:13px; }
}

/* message when empty */
.no-receipt {
  padding:22px; color:#b00020; font-weight:700; text-align:center; font-size:16px;
}

/* FOOTER */
.footer {
  text-align:center; padding:20px 12px; color:#333; margin-top:24px; font-size:14px;
}

/* ANIMATION */
@keyframes fadeInUp { from { opacity:0; transform:translateY(18px);} to { opacity:1; transform:none;} }

/* small screens */
@media (max-width: 560px) {
  .card { padding:22px; border-radius:16px; }
  .header img { width:48px; }
  .header h1 { font-size:20px; }
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="header" role="banner">
    <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Ubunifu Logo">
    <h1>Learning Hub</h1>
</header>

<!-- MAIN -->
<main class="wrapper" role="main">
  <section class="card" aria-labelledby="welcome-heading">
    <h2 id="welcome-heading">Welcome <?= htmlspecialchars($_SESSION['user']['name']); ?> 👋</h2>
    <p class="lead">Student dashboard — register for courses, view receipts, and track payments.</p>

    <div class="actions" role="navigation" aria-label="Student actions">
      <a class="btn primary" href="student_form.php"><i class="bx bx-edit" aria-hidden="true"></i> Register for a Course</a>
      <a class="btn danger" href="logout.php"><i class="bx bx-log-out" aria-hidden="true"></i> Logout</a>
      <button class="view-btn" id="openReceiptsBtn" aria-controls="receiptPopup">📜 View Receipts</button>
    </div>

    <p style="color:#444; font-size:14px; margin-top:6px;">
      Tip: Click <strong>View Receipts</strong> to open your payment history and print receipts.
    </p>
  </section>
</main>

<!-- RECEIPT POPUP -->
<div id="receiptPopup" class="popup-overlay" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="receipts-title">
  <div class="popup" role="document">
    <button class="close" id="closePopup" aria-label="Close receipts">&times;</button>
    <h3 id="receipts-title" style="text-align:center; color:var(--green-700); margin-bottom:8px;">📜 Your Receipts</h3>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-wrap">
        <table class="receipt-table" role="table" aria-label="Receipts table">
          <thead>
            <tr>
              <th>#</th>
              <th>Course</th>
              <th>M-Pesa Code</th>
              <th>Registration Fee</th>
              <th>1st Install.</th>
              <th>2nd Install.</th>
              <th>3rd Install.</th>
              <th>Total Paid</th>
              <th>Expected Fee</th>
              <th>Balance</th>
              <th>Date</th>
              <th>Receipt</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $count = 1;
            $expected_fee = 1700;

            while ($row = $result->fetch_assoc()):
              $reg = floatval($row['registration_fee']);
              $i1  = floatval($row['first_installment']);
              $i2  = floatval($row['second_installment']);
              $i3  = floatval($row['third_installment']);
              $total_paid = $reg + $i1 + $i2 + $i3;
              $balance = $expected_fee - $total_paid;
          ?>
            <tr>
              <td><?= $count++; ?></td>
              <td><?= htmlspecialchars($row['course']); ?></td>
              <td><?= htmlspecialchars($row['mpesa_code']); ?></td>
              <td>KSh <?= number_format($reg,2); ?></td>
              <td>KSh <?= number_format($i1,2); ?></td>
              <td>KSh <?= number_format($i2,2); ?></td>
              <td>KSh <?= number_format($i3,2); ?></td>
              <td style="font-weight:700;">KSh <?= number_format($total_paid,2); ?></td>
              <td>KSh <?= number_format($expected_fee,2); ?></td>
              <td style="font-weight:700; color:<?= $balance <= 0 ? 'green' : 'red'; ?>;">
                KSh <?= number_format($balance,2); ?>
              </td>
              <td><?= htmlspecialchars($row['created_at']); ?></td>
              <td><a href="print_receipt.php?id=<?= $row['receipt_id']; ?>" target="_blank" style="color:var(--green-700); font-weight:700; text-decoration:none;">🖨️ Print</a></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="no-receipt">⚠️ No receipts found yet. Wait for admin approval.</div>
    <?php endif; ?>

  </div>
</div>

<!-- FOOTER -->
<footer class="footer" role="contentinfo">
  <strong>Business Contacts</strong><br>
  📞 0759635652 | 📧 jamboubunifuhub@gmail.com<br>
  📍 Barut Centre, Chelnet Plaza, 1st Floor, Nakuru Kenya<br><br>
  © <?= date('Y') ?> Ubunifu Learning Hub — All Rights Reserved
</footer>

<script>
// Open/close popup with animation and accessibility toggles
const openBtn = document.getElementById('openReceiptsBtn');
const popupOverlay = document.getElementById('receiptPopup');
const closeBtn = document.getElementById('closePopup');

function openReceiptPopup() {
  popupOverlay.style.display = 'flex';
  popupOverlay.setAttribute('aria-hidden','false');
  // focus management: move focus to close button
  closeBtn.focus();
  // add small entrance animation by reflow (if needed)
  const table = popupOverlay.querySelector('table');
  if (table) {
    table.classList.remove('animate');
    void table.offsetWidth;
    table.classList.add('animate');
  }
}

function closeReceiptPopup() {
  popupOverlay.style.display = 'none';
  popupOverlay.setAttribute('aria-hidden','true');
  openBtn.focus();
}

openBtn.addEventListener('click', openReceiptPopup);
closeBtn.addEventListener('click', closeReceiptPopup);

// Close on overlay click (but avoid closing when clicking inside the popup)
popupOverlay.addEventListener('click', function(e){
  if (e.target === popupOverlay) closeReceiptPopup();
});

// Close on Escape
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape' && popupOverlay.style.display === 'flex') {
    closeReceiptPopup();
  }
});
</script>

</body>
</html>
