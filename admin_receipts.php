<?php     
session_start();
include 'datas.php'; 

// Only admins can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle search input
$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string(trim($_GET['search']));
}

// Fetch receipts with optional search
$sql = "
    SELECT r.id, r.receipt_id, r.registration_fee, r.first_installment, r.second_installment, r.third_installment,
           r.course, r.payment_date, r.mpesa_code,
           u.name AS student_name, u.email AS student_email,
           a.name AS admin_name
    FROM receipts r
    JOIN users u ON r.student_id = u.id
    LEFT JOIN users a ON r.admin_id = a.id
";

if (!empty($search)) {
    $sql .= " WHERE r.receipt_id LIKE '%$search%' 
              OR r.id LIKE '%$search%' 
              OR u.name LIKE '%$search%' 
              OR u.email LIKE '%$search%' 
              OR r.mpesa_code LIKE '%$search%' 
              OR a.name LIKE '%$search%'";
}

$sql .= " ORDER BY r.payment_date DESC";
$result = $conn->query($sql);
if (!$result) { die("Query Error: " . $conn->error); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Receipts - Admin</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
/* ===== RESET & BODY ===== */
* { box-sizing:border-box; margin:0; padding:0; font-family:'Poppins',Arial,sans-serif; }
body { background: linear-gradient(135deg,#d7f5d6,#a5d9ba); min-height:100vh; display:flex; flex-direction:column; }

/* ===== HEADER ===== */
header {
    width:100%; background:#046307; color:white; display:flex; align-items:center; justify-content:center; gap:15px;
    padding:15px 20px; position:fixed; top:0; z-index:1000; flex-wrap:wrap; box-shadow:0 4px 12px rgba(0,0,0,0.25);
}
header img { width:60px; height:auto; object-fit:contain; }
header h1 { font-size:24px; font-weight:700; text-align:center; }

/* ===== MAIN WRAPPER ===== */
.main-wrapper { flex:1; display:flex; justify-content:center; padding:140px 15px 30px; width:100%; }
.container {
    width:100%; max-width:1200px; min-width:320px; background:#fff; padding:30px 25px; border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.18); display:flex; flex-direction:column; gap:20px;
}

/* ===== BACK BUTTON ===== */
.back-btn {
    display:inline-flex; align-items:center; gap:5px; background:#046307; color:white;
    padding:10px 18px; border-radius:8px; text-decoration:none; font-weight:500; width:max-content; transition:0.3s;
}
.back-btn:hover { background:#058c15; }

/* ===== SEARCH FORM ===== */
form.search-form {
    display:flex; flex-wrap:wrap; justify-content:center; gap:10px; margin-bottom:15px;
}
form.search-form input {
    padding:10px 15px; border-radius:6px; border:1px solid #ccc;
    min-width:200px; max-width:400px; flex:1;
    transition:0.3s; outline:none;
}
form.search-form input:focus { border-color:#046307; box-shadow:0 0 5px rgba(4,99,7,0.5); }
form.search-form button {
    padding:10px 18px; background:#43a047; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:500;
    transition:0.3s;
}
form.search-form button:hover { background:#2e7d32; }

/* ===== TABLE ===== */
table {
    width:100%; border-collapse:collapse; min-width:700px; box-shadow:0 5px 15px rgba(0,0,0,0.1);
}
thead { background:#046307; color:white; position:sticky; top:0; z-index:10; }
thead th { padding:12px 10px; text-align:left; font-weight:600; }
tbody td { padding:10px 8px; border-bottom:1px solid #ddd; }
tbody tr:hover { background:#f1fef1; transition:0.2s; }
.status-badge { padding:4px 10px; border-radius:12px; font-weight:500; color:#fff; }
.status-badge.admin { background:#f45b50; }
.status-badge.user { background:#43a047; }

/* ===== FOOTER ===== */
footer { text-align:center; padding:20px 15px; font-size:14px; color:#444; margin-top:auto; }

/* ===== RESPONSIVE ===== */
@media(max-width:768px){
    header h1 { font-size:20px; }
    form.search-form { flex-direction:column; gap:8px; }
    form.search-form button { width:100%; }
    table { font-size:14px; }
}
@media(max-width:480px){
    header img { width:50px; }
    header h1 { font-size:18px; }
    table { font-size:13px; }
}
</style>
</head>
<body>

<!-- HEADER -->
<header>
    <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Logo">
    <h1>Ubunifu Learning Hub - Receipts</h1>
</header>

<!-- MAIN WRAPPER -->
<div class="main-wrapper">
    <div class="container">

        <!-- BACK BUTTON -->
        <a href="admin_dashboard.php" class="back-btn"><i class='bx bx-arrow-back'></i> Back to Dashboard</a>

        <!-- SEARCH FORM -->
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="Search by Receipt ID, Student, Email, MPESA or Admin" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <!-- TABLE -->
        <div style="overflow-x:auto; border-radius:12px; width:100%;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Receipt ID</th>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Amount Paid</th>
                        <th>Payment Date</th>
                        <th>MPESA Code</th>
                        <th>Submitted By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): 
                            $total_paid = $row['registration_fee'] + $row['first_installment'] + $row['second_installment'] + $row['third_installment'];
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><strong><?= htmlspecialchars($row['receipt_id']) ?></strong></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['student_email']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td>KSh <?= number_format($total_paid,2) ?></td>
                            <td><?= $row['payment_date'] ?></td>
                            <td><?= htmlspecialchars($row['mpesa_code']) ?></td>
                            <td><?= htmlspecialchars($row['admin_name'] ?? 'N/A') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" style="text-align:center; padding:20px; color:red;">No receipts found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- FOOTER -->
<footer>
    <strong>Business Contacts</strong><br>
    📞 0759635652 | 📧 jamboubunifuhub@gmail.com<br>
    📍 Barut Centre, Chelnet Plaza, 1st Floor, Nakuru Kenya<br><br>
    © <?= date('Y') ?> Ubunifu Learning Hub — All Rights Reserved
</footer>

</body>
</html>
  