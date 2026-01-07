<?php   
session_start();
include 'datas.php';

// Only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get student ID
if (!isset($_GET['id'])) die("❌ Student ID not provided.");

$student_id = intval($_GET['id']);

// Fetch student info
$sql = "SELECT s.id AS student_id, u.name, s.course 
        FROM students s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if (!$student) die("❌ Student not found.");

// Handle receipt form
$msg = "";
$receipt = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_receipt'])) {
    $registration_fee = floatval($_POST['registration_fee'] ?? 0);
    $first = floatval($_POST['first_installment'] ?? 0);
    $second = floatval($_POST['second_installment'] ?? 0);
    $third = floatval($_POST['third_installment'] ?? 0);
    $mpesa = trim($_POST['mpesa_code'] ?? '');

    // Prevent duplicate M-Pesa
    $check = $conn->prepare("SELECT id FROM receipts WHERE mpesa_code = ?");
    $check->bind_param("s", $mpesa);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $msg = "❌ This M-Pesa code already exists!";
    } else {
        $expected_fee = 1700;
        $total_paid = $registration_fee + $first + $second + $third;
        $balance = $expected_fee - $total_paid;
        $receipt_id = "UBN" . strtoupper(uniqid());
        $admin_id = $_SESSION['user']['id'];

        $sql = "INSERT INTO receipts 
            (receipt_id, student_id, course, amount, mpesa_code, payment_date, created_at, registration_fee, first_installment, second_installment, third_installment, admin_id)
            VALUES (?, ?, ?, ?, ?, CURDATE(), NOW(), ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sisdiddddi",
            $receipt_id,
            $student_id,
            $student['course'],
            $total_paid,
            $mpesa,
            $registration_fee,
            $first,
            $second,
            $third,
            $admin_id
        );

        if ($stmt->execute()) {
            $msg = "✅ Receipt successfully recorded!";
            $admin_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
            $admin_stmt->bind_param("i", $admin_id);
            $admin_stmt->execute();
            $admin_name = $admin_stmt->get_result()->fetch_assoc()['name'];

            $receipt = [
                'receipt_id' => $receipt_id,
                'name' => $student['name'],
                'course' => $student['course'],
                'registration_fee' => $registration_fee,
                'first_installment' => $first,
                'second_installment' => $second,
                'third_installment' => $third,
                'mpesa_code' => $mpesa,
                'total_paid' => $total_paid,
                'balance' => $balance,
                'date' => date("Y-m-d"),
                'admin_name' => $admin_name
            ];
        } else {
            $msg = "❌ Error saving receipt: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generate Receipt - Admin</title>
<link rel="stylesheet" href="design.css">
<style>
/* ===== RESET & BODY ===== */
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
body { background: linear-gradient(135deg,#d4f1d2,#a9d6b9); min-height:100vh; padding-top:120px; font-size:16px; }

/* ===== HEADER ===== */
.myheader { width:100%; background:#046307; display:flex; justify-content:center; align-items:center; gap:15px; padding:18px 20px; position:fixed; top:0; left:0; z-index:1000; flex-wrap:wrap; }
.myheader img { width:65px; height:auto; }
.myheader h1 { font-size:26px; font-weight:700; color:#fff; letter-spacing:.7px; }

/* ===== FORM & RECEIPT CONTAINER ===== */
.container { width:90%; max-width:680px; margin:0 auto 50px auto; background:white; padding:40px 30px; border-radius:18px; box-shadow:0 8px 25px rgba(0,0,0,.2); text-align:center; animation:fadeInUp 0.7s ease forwards; }

form { display:flex; flex-direction:column; gap:18px; margin-top:20px; }
label { font-size:15px; color:#034f09; font-weight:600; text-align:left; }
input { width:100%; padding:14px; font-size:15px; border-radius:8px; border:2px solid #e0e0e0; background:#fafafa; transition:.3s; }
input:focus { border-color:#0e7e12; background:#fff; box-shadow:0 0 0 3px rgba(26,121,40,.15); }
button { padding:14px; background:#046307; border-radius:10px; color:#fff; font-size:16px; cursor:pointer; border:none; transition:.3s; }
button:hover { transform:scale(1.05); background:#058c15; }

/* ===== RECEIPT CARD ===== */
.receipt-card { margin-top:35px; background:#f8fff8; padding:30px; border-radius:18px; box-shadow:0 8px 30px rgba(0,0,0,.25); animation:fadeInUp 1s ease forwards; }
.receipt-card h2 { color:#046307; font-size:26px; }
.receipt-card p { font-weight:500; }
.receipt-card table { width:100%; margin-top:18px; border-collapse:collapse; font-size:15px; }
.receipt-card th { background:#dbf6db; padding:14px; color:#034f09; text-align:left; }
.receipt-card td { padding:13px; border-bottom:1px solid #d5ead5; font-weight:500; }
.print-btn { margin-top:20px; padding:12px 25px; font-size:17px; border-radius:10px; border:none; background:#046307; color:white; cursor:pointer; }
.print-btn:hover { background:#058c15; transform:scale(1.05); }

/* ===== ANIMATION ===== */
@keyframes fadeInUp { from {opacity:0; transform:translateY(30px);} to {opacity:1; transform:translateY(0);} }

/* ===== PRINT ===== */
@media print { form, .print-btn, .myheader { display:none; } body { background:white; } }

/* ===== RESPONSIVE ===== */
@media(max-width:768px){ .container{padding:30px 22px;} input{padding:12px;} .receipt-card{padding:25px;} table{font-size:14px;} }
@media(max-width:480px){ body{padding-top:130px;} .myheader img{width:50px;} .myheader h1{font-size:20px;} .container{width:92%; padding:25px 18px;} .receipt-card{padding:20px;} th,td{padding:10px;} }
.footer {
   
    color: #000000ff;
    padding: 20px 10px;
    text-align: center;
    font-size: 14px;
    margin-top: auto;
}

.footer-container p {
    margin: 6px 0;
}

.footer a {
    color: #a8e6a3;
    text-decoration: none;
}

.footer a:hover {
    text-decoration: underline;
}

/* Responsive */
@media(max-width: 480px){
    .footer { font-size: 12px; padding: 18px 10px; }
}

</style>
<script>
function calculateBalance() {
    const expected = 1700;
    const reg = parseFloat(document.getElementById('registration_fee').value)||0;
    const first = parseFloat(document.getElementById('first_installment').value)||0;
    const second = parseFloat(document.getElementById('second_installment').value)||0;
    const third = parseFloat(document.getElementById('third_installment').value)||0;
    const total = reg + first + second + third;
    const balance = expected - total;
    document.getElementById('total_paid').value = total.toFixed(2);
    document.getElementById('balance').value = balance.toFixed(2);
}
</script>
</head>
<body>

<div class="myheader">
    <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Ubunifu Logo">
    <h1>Learning Hub</h1>
</div>

<div class="container">
    <h3>🧾 Generate Receipt for <?= htmlspecialchars($student['name']) ?></h3>
    <?php if($msg): ?>
        <p style="color:<?= strpos($msg,'✅')!==false?'#00e676':'#ff5252'; ?>"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST" oninput="calculateBalance()">
        <label>Registration Fee:</label>
        <input type="number" id="registration_fee" name="registration_fee" step="0.01" required>
        <label>1st Installment:</label>
        <input type="number" id="first_installment" name="first_installment" step="0.01">
        <label>2nd Installment:</label>
        <input type="number" id="second_installment" name="second_installment" step="0.01">
        <label>3rd Installment:</label>
        <input type="number" id="third_installment" name="third_installment" step="0.01">
        <label>Total Paid:</label>
        <input type="number" id="total_paid" readonly>
        <label>Balance:</label>
        <input type="number" id="balance" readonly>
        <label>M-Pesa Code:</label>
        <input type="text" name="mpesa_code" required>
        <button style="margin-bottom: 15px;" type="submit" name="save_receipt">💾 Save Receipt</button>
    </form>
    <strong>
      <a style="margin-top: 20px;border-radius: 17px; background: skyblue; padding: 10px;" href="admin_dashboard.php" class="back-btn">&larr; Back to Dashboard</a>
    </strong>

    <?php if($receipt): ?>
    <div class="receipt-card">
        <h2>Ubunifu Learning Hub</h2>
        <p><strong>Receipt ID:</strong> <?= htmlspecialchars($receipt['receipt_id']) ?></p>
        <p><strong>Student:</strong> <?= htmlspecialchars($receipt['name']) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($receipt['course']) ?></p>
        <p><strong>Submitted By:</strong> <?= htmlspecialchars($receipt['admin_name']) ?></p>
        <table>
            <tr><th>Registration Fee</th><td>KSh <?= number_format($receipt['registration_fee'],2) ?></td></tr>
            <tr><th>1st Installment</th><td>KSh <?= number_format($receipt['first_installment'],2) ?></td></tr>
            <tr><th>2nd Installment</th><td>KSh <?= number_format($receipt['second_installment'],2) ?></td></tr>
            <tr><th>3rd Installment</th><td>KSh <?= number_format($receipt['third_installment'],2) ?></td></tr>
            <tr><th>Total Paid</th><td>KSh <?= number_format($receipt['total_paid'],2) ?></td></tr>
            <tr><th>Balance</th><td style="color:<?= $receipt['balance']>0?'red':'#046307' ?>;">KSh <?= number_format($receipt['balance'],2) ?></td></tr>
            <tr><th>M-Pesa Code</th><td><?= htmlspecialchars($receipt['mpesa_code']) ?></td></tr>
            <tr><th>Date</th><td><?= htmlspecialchars($receipt['date']) ?></td></tr>
        </table>
        <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
    </div>
    <?php endif; ?>
</div>
<footer class="footer">
    <div class="footer-container">
        <p><strong>Ubunifu Learning Hub</strong></p>
        <p>📞 0759635652 | 📧 jamboubunifuhub@gmail.com</p>
        <p>📍 Barut Centre, Chelnet Plaza, 1st Floor, Nakuru, Kenya</p>
        <p>© <?= date('Y') ?> Ubunifu Learning Hub — All Rights Reserved</p>
    </div>
</footer>


</body>
</html>
