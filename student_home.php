<?php
session_start();
include 'datas.php';

// Example admission number from login
$adm_no = $_SESSION['user']['adm_no'];

$sql = "SELECT * FROM receipts WHERE adm_no = '$adm_no' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard - Ubunifu Learning Hub</title>
<style>
body { font-family: Arial; background: #f5f5f5; padding: 20px; }
.container { background: white; padding: 20px; border-radius: 10px; max-width: 800px; margin: auto; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
a.btn { background: #1f4e79; color: white; padding: 6px 12px; text-decoration: none; border-radius: 5px; }
a.btn:hover { background: #163a5a; }
</style>
</head>
<body>
<div class="container">
<h2>Welcome, <?= $_SESSION['user']['name'] ?></h2>
<h3>Your Fee Receipts</h3>

<table>
<tr><th>Receipt No</th><th>Course</th><th>Date</th><th>Action</th></tr>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?= $row['receipt_no'] ?></td>
    <td><?= $row['course'] ?></td>
    <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
    <td><a href="view_receipt.php?id=<?= $row['id'] ?>" class="btn">View & Print</a></td>
</tr>
<?php } ?>
</table>
</div>
<div class="footer">
        <p>© <?= date('Y') ?> Ubunifu Learning Hub — All Rights Reserved.</p>
    </div>
</body>
</html>
