<?php    
session_start();
include 'datas.php';

// Restrict access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Fetch latest receipt including receipt_id
$sql = "
    SELECT r.receipt_id, r.*, s.course, u.name, u.email 
    FROM receipts r
    JOIN students s ON r.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE u.id = ?
    ORDER BY r.created_at DESC
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$receipt = $result->fetch_assoc();

if (!$receipt) {
    die("<h3 style='text-align:center;color:red;'>❌ No receipt available yet. Please wait for admin to generate it.</h3>");
}

// Totals
$total_paid = 
    $receipt['registration_fee'] + 
    $receipt['first_installment'] + 
    $receipt['second_installment'] + 
    $receipt['third_installment'];

$expected_fee = 1700;
$balance = $expected_fee - $total_paid;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ubunifu Learning Hub | Receipt</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #e8f5e9;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 750px;
    margin: 40px auto;
    background: #fff;
    border-radius: 16px;
    padding: 40px 50px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== HEADER ===== */
.header {
    text-align: center;
    margin-bottom: 25px;
}
.header img {
    width: 100px;
    height: auto;
    margin-bottom: 10px;
}
.header h2 {
    font-size: 22px;
    color: #0f5132;
    font-weight: 600;
    margin-bottom: 3px;
}
.header small {
    font-size: 13px;
    color: #555;
}

/* ===== TITLE ===== */
.title {
    text-align: center;
    margin-bottom: 20px;
}
.title h3 {
    margin: 0;
    font-size: 20px;
    color: #0f5132;
}
.title p {
    margin: 2px 0;
    color: #444;
    font-size: 14px;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 14px;
}
th, td {
    padding: 12px 10px;
    border-bottom: 1px solid #e3e3e3;
}
th {
    text-align: left;
    background: #f1f8f4;
    color: #0f5132;
    font-size: 13px;
    font-weight: 600;
}
td {
    color: #444;
    font-weight: 500;
}

/* ===== TOTALS ===== */
.totals {
    margin-top: 25px;
    padding: 18px;
    background: #f7fcf8;
    border: 1px solid #d9eadf;
    border-radius: 10px;
    font-size: 15px;
}
.totals strong {
    color: #0f5132;
}

/* ===== PRINT BUTTON ===== */
.print-btn {
    display: block;
    margin: 35px auto 0;
    background: #0f5132;
    color: white;
    border: none;
    padding: 12px 35px;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}
.print-btn:hover {
    background: #144d30;
    transform: scale(1.05);
}

/* ===== FOOTER ===== */
.footer {
    text-align: center;
    font-size: 12px;
    color: #777;
    margin-top: 30px;
}

@media print {
    .print-btn { display: none; }
    body { background: #fff; }
    .container { box-shadow: none; border-radius: 0; margin: 0; padding: 20px; }
}
</style>
</head>

<body>

<div class="container" id="receiptArea">

    <div class="header">
        <!-- Ubunifu Logo -->
        <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Ubunifu Logo">
        <h2>UBUNIFU LEARNING HUB</h2>
        <small>Your Pathway to Digital Skills Excellence</small>
    </div>

    <div class="title">
        <h3>Payment Receipt</h3>

        <p><strong><?= htmlspecialchars($receipt['name']) ?></strong></p>
        <p>Course: <?= htmlspecialchars($receipt['course']) ?></p>

        <!-- UNIQUE RECEIPT ID -->
        <p style="margin-top:6px; font-size:14px; color:#0f5132;">
            <strong>Receipt ID:</strong> <?= htmlspecialchars($receipt['receipt_id']) ?>
        </p>
    </div>

    <table>
        <tr><th>Registration Fee</th><td>KSh <?= number_format($receipt['registration_fee'], 2) ?></td></tr>
        <tr><th>1st Installment</th><td>KSh <?= number_format($receipt['first_installment'], 2) ?></td></tr>
        <tr><th>2nd Installment</th><td>KSh <?= number_format($receipt['second_installment'], 2) ?></td></tr>
        <tr><th>3rd Installment</th><td>KSh <?= number_format($receipt['third_installment'], 2) ?></td></tr>
        <tr><th>M-Pesa Code</th><td><?= htmlspecialchars($receipt['mpesa_code']) ?></td></tr>
        <tr><th>Date Issued</th><td><?= htmlspecialchars($receipt['created_at']) ?></td></tr>
    </table>

    <div class="totals">
        Expected Fee: KSh <?= number_format($expected_fee, 2) ?><br>
        Total Paid: <strong>KSh <?= number_format($total_paid, 2) ?></strong><br>
        Balance: 
        <strong style="color: <?= $balance <= 0 ? '#0f5132' : 'red' ?>">
            KSh <?= number_format($balance, 2) ?>
        </strong>
    </div>

    <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>

    <div class="footer">
        <strong>Business Contacts</strong><br>
        📞 0759635652 | 📧 jamboubunifuhub@gmail.com<br>
        📍 Barut Centre, Chelnet Plaza, 1st Floor, Nakuru Kenya<br><br>
        © <?= date('Y') ?> Ubunifu Learning Hub — All Rights Reserved
    </div>

</div>

</body>
</html>
