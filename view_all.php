<?php
session_start();
include 'datas.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* ===== SEARCH ===== */
$search = trim($_GET['search'] ?? '');

/* ===== QUERY ===== */
$sql = "SELECT id, name, email, role, created_at FROM users";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " WHERE name LIKE ? OR email LIKE ? OR role LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = "sss";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | All Users</title>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family:'Poppins',Arial,sans-serif;
    background:linear-gradient(135deg,#d7f5d6,#a5d9ba);
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

/* ===== HEADER ===== */
header{
    background:linear-gradient(135deg,#046307,#0a8f3c);
    color:#fff;
    padding:20px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:15px;
    position:fixed;
    width:100%;
    top:0;
    z-index:1000;
    box-shadow:0 4px 15px rgba(0,0,0,.25);
}
header img{width:70px}
header h1{font-size:26px}

/* ===== MAIN ===== */
.main-wrapper{
    flex:1;
    padding-top:140px;
    display:flex;
    justify-content:center;
}
.container{
    width:92%;
    max-width:900px;
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

/* ===== SEARCH ===== */
.search-box{
    display:flex;
    gap:10px;
    justify-content:center;
    margin-bottom:20px;
}
.search-box input{
    flex:1;
    max-width:350px;
    padding:10px 14px;
    border-radius:10px;
    border:1px solid #ccc;
}
.search-box button{
    background:#046307;
    color:#fff;
    border:none;
    padding:10px 18px;
    border-radius:10px;
    cursor:pointer;
    transition:.3s;
}
.search-box button:hover{background:#058c15}

/* ===== TABLE ===== */
.table-wrapper{overflow-x:auto}
table{
    width:100%;
    min-width:650px;
    border-collapse:collapse;
    text-align:center;
}
th,td{
    padding:12px;
    border-bottom:1px solid #ddd;
}
th{
    background:#e3f6e5;
    color:#034f09;
}
tr:hover{background:#f4fff4}

/* ===== ROLE BADGE ===== */
.role{
    padding:6px 14px;
    border-radius:20px;
    font-weight:600;
    font-size:13px;
    color:#fff;
}
.role.admin{background:#c62828}
.role.user{background:#2e7d32}

/* ===== BACK BUTTON ===== */
.back-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-top:25px;
    padding:10px 18px;
    background:#046307;
    color:#fff;
    text-decoration:none;
    border-radius:10px;
    transition:.3s;
}
.back-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(0,0,0,.15);
}

/* ===== FOOTER ===== */
footer{
    text-align:center;
    padding:18px;
    font-size:14px;
    border-top:1px solid #ccc;
}

/* ===== ANIMATION ===== */
@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px)}
    to{opacity:1;transform:translateY(0)}
}

@media(max-width:768px){
    header h1{font-size:20px}
    header img{width:55px}
}
</style>
</head>

<body>

<header>
    <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Ubunifu Logo">
    <h1>Admin Panel — Users</h1>
</header>

<div class="main-wrapper">
<div class="container">

<h2>All Registered Users</h2>

<form class="search-box" method="GET">
    <input type="text" name="search" placeholder="Search by name, email or role"
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><i class='bx bx-search'></i> Search</button>
</form>

<div class="table-wrapper">
<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Registered</th>
</tr>
</thead>
<tbody>

<?php if($result->num_rows): ?>
<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><span class="role <?= $row['role'] ?>"><?= ucfirst($row['role']) ?></span></td>
<td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5" style="color:red;">No users found</td></tr>
<?php endif; ?>

</tbody>
</table>
</div>

<a href="admin_dashboard.php" class="back-btn">
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

<?php
$stmt->close();
$conn->close();
?>
