<?php
session_start();
include 'datas.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* ===== CSRF TOKEN ===== */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ===== HANDLE ACTIONS ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['csrf_token'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $id = intval($_POST['id']);

    if (isset($_POST['approve'])) {
        $stmt = $conn->prepare("UPDATE students SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    if (isset($_POST['reject'])) {
        $stmt = $conn->prepare("UPDATE students SET status='rejected' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: view_students.php");
    exit;
}

/* ===== FETCH STUDENTS ===== */
$sql = "SELECT s.id, u.name, u.email, s.course, s.contact, s.status, s.created_at
        FROM students s
        JOIN users u ON s.user_id = u.id
        ORDER BY s.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Student Approvals</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Poppins',Arial,sans-serif;
    background:linear-gradient(135deg,#d7f5d6,#a5d9ba);
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

/* HEADER */
header{
    background:linear-gradient(135deg,#046307,#0a8f3c);
    color:#fff;
    padding:20px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:18px;
    position:fixed;
    width:100%;
    top:0;
    z-index:1000;
    box-shadow:0 4px 15px rgba(0,0,0,.25);
}
.header-logo img{width:90px;}
header h1{
    font-size:26px;
    line-height:1.2;
    text-align:center;
}
header small{font-size:14px;opacity:.9}

/* MAIN */
.main-wrapper{
    flex:1;
    padding-top:150px;
    display:flex;
    justify-content:center;
}
.container{
    width:92%;
    max-width:1000px;
    background:#fff;
    padding:35px 25px;
    border-radius:18px;
    box-shadow:0 12px 30px rgba(0,0,0,.18);
    animation:fadeIn .6s ease;
}
h2{
    color:#046307;
    text-align:center;
    margin-bottom:20px;
}

/* TABLE */
.table-wrapper{overflow-x:auto;margin-top:15px;}
table{
    width:100%;
    min-width:700px;
    border-collapse:collapse;
}
th,td{
    padding:12px;
    border-bottom:1px solid #ddd;
}
th{
    background:#e3f6e5;
    color:#034f09;
    position:sticky;
    top:0;
}
tr:hover{background:#f4fff4}

/* STATUS */
.status{
    padding:6px 14px;
    border-radius:20px;
    font-weight:600;
    font-size:13px;
}
.pending{background:#fff4cc;color:#d89b00;}
.approved{background:#e6ffed;color:#0d7a25;}
.rejected{background:#ffe5e5;color:#b30000;}

/* BUTTONS */
button,a.action-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:9px 16px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-size:14px;
    text-decoration:none;
    transition:.3s;
}
button:hover,a.action-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(0,0,0,.15);
}
.approve{background:#2e7d32;color:#fff;}
.reject{background:#c62828;color:#fff;}
.receipt{background:#1b5e20;color:#fff;}
.back-btn{
    background:#046307;
    color:#fff;
    margin-top:25px;
    display:inline-flex;
}

/* FOOTER */
footer{
    margin-top:auto;
    text-align:center;
    padding:18px;
    font-size:14px;
    border-top:1px solid #ccc;
}

/* ANIMATION */
@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px);}
    to{opacity:1;transform:translateY(0);}
}

@media(max-width:768px){
    header h1{font-size:20px;}
    .header-logo img{width:65px;}
}
</style>
</head>

<body>

<header>
    <div class="header-logo">
        <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Ubunifu Logo">
    </div>
    <h1>Ubunifu Learning Hub<br><small>Admin Panel</small></h1>
</header>

<div class="main-wrapper">
<div class="container">

<h2>Course Registration Approvals</h2>

<?php if($result && $result->num_rows>0): ?>
<div class="table-wrapper">
<table>
<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>Course</th>
<th>Contact</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= htmlspecialchars($row['course']) ?></td>
<td><?= htmlspecialchars($row['contact']) ?></td>
<td><span class="status <?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></span></td>
<td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
<td>
<?php if($row['status']==='pending'): ?>
<form method="POST" style="display:flex;gap:8px;flex-wrap:wrap;">
<input type="hidden" name="id" value="<?= $row['id'] ?>">
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<button name="approve" class="approve" onclick="return confirm('Approve this student?')">
<i class='bx bx-check'></i>Approve
</button>
<button name="reject" class="reject" onclick="return confirm('Reject this student?')">
<i class='bx bx-x'></i>Reject
</button>
</form>
<?php elseif($row['status']==='approved'): ?>
<a href="generate_receipt.php?id=<?= $row['id'] ?>" class="receipt">
<i class='bx bx-receipt'></i>Receipt
</a>
<?php else: ?>
<strong style="color:#b30000;">No action</strong>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>
<?php else: ?>
<p style="color:red;font-weight:bold;">No registrations found.</p>
<?php endif; ?>

<a href="admin_dashboard.php" class="action-btn back-btn">
<i class='bx bx-arrow-back'></i> Back to Dashboard
</a>

</div>
</div>

<footer>
<strong>Business Contacts</strong><br>
📞 0759635652 | 📧 jamboubunifuhub@gmail.com<br>
📍 Barut Centre, Chelnet Plaza, 1st Floor, Nakuru Kenya<br><br>
© <?= date('Y') ?> Ubunifu Learning Hub — All Rights Reserved
</footer>

</body>
</html>
