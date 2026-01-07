<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body style="margin:0; font-family:'Poppins', Arial, sans-serif; background: linear-gradient(135deg, #d4f1d2, #a9d6b9); display:flex; flex-direction:column; min-height:100vh;">

<!-- HEADER -->
<header style="width:100%; background:#046307; color:white; display:flex; align-items:center; justify-content:center; gap:15px; padding:15px 20px; position:fixed; top:0; z-index:1000; flex-wrap:wrap;">
    <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Ubunifu Logo" style="width:80px; height:auto; object-fit:contain;">
    <h1 style="font-size:26px; font-weight:700; margin:0;">Learning Hub - Admin Panel</h1>
</header>

<!-- MAIN WRAPPER -->
<div style="flex:1; display:flex; justify-content:center; align-items:flex-start; padding-top:130px; width:100%;">
    <div style="width:90%; max-width:900px; background:rgba(255,255,255,0.95); padding:40px 30px; border-radius:18px; box-shadow:0 5px 25px rgba(0,0,0,0.2); display:flex; flex-direction:column; align-items:center; text-align:center;">

        <h2 style="color:#2e7d32; font-weight:600; margin-bottom:10px; font-size:24px;">Welcome Admin, <?= htmlspecialchars($_SESSION['user']['name']); ?> 🎉</h2>
        <p style="margin-bottom:20px; font-size:16px; color:#333;">Manage student registrations, receipts, and course approvals.</p>

        <!-- TOP ACTION BUTTONS -->
        <div style="display:flex; gap:15px; flex-wrap:wrap; justify-content:center; margin-bottom:25px;">
            <a href="view_students.php" style="display:flex; align-items:center; gap:8px; background:linear-gradient(135deg, #2e7d32, #43a047); color:white; padding:12px 22px; border-radius:8px; text-decoration:none; font-weight:500; font-size:16px; box-shadow:0 2px 6px rgba(0,0,0,0.2); transition:0.3s; cursor:pointer;"><i class='bx bx-user-check'></i> View Students</a>
            <a href="admin_receipts.php" style="display:flex; align-items:center; gap:8px; background:linear-gradient(135deg, #2e7d32, #43a047); color:white; padding:12px 22px; border-radius:8px; text-decoration:none; font-weight:500; font-size:16px; box-shadow:0 2px 6px rgba(0,0,0,0.2); transition:0.3s; cursor:pointer;"><i class='bx bx-receipt'></i> View Receipts</a>
            <a href="view_all.php" style="display:flex; align-items:center; gap:8px; background:linear-gradient(135deg, #2e7d32, #43a047); color:white; padding:12px 22px; border-radius:8px; text-decoration:none; font-weight:500; font-size:16px; box-shadow:0 2px 6px rgba(0,0,0,0.2); transition:0.3s; cursor:pointer;"><i class='bx bx-user'></i> View All Users</a>
            <a href="logout.php" style="display:flex; align-items:center; gap:8px; background:linear-gradient(135deg, #c62828, #e53935); color:white; padding:12px 22px; border-radius:8px; text-decoration:none; font-weight:500; font-size:16px; box-shadow:0 2px 6px rgba(0,0,0,0.2); transition:0.3s; cursor:pointer;"><i class='bx bx-log-out'></i> Logout</a>
        </div>

        <!-- DASHBOARD CARD -->
        <div style="width:100%; background:white; border-radius:15px; padding:25px 20px; box-shadow:0 4px 20px rgba(0,0,0,0.1); text-align:left;">
            <h3 style="color:#046307; font-weight:600; margin-bottom:15px;">Quick Actions</h3>
            <ul style="list-style:none; padding-left:0; font-size:16px; color:#333;">
                <li style="margin-bottom:10px;"><i class='bx bx-user-plus' style="color:#2e7d32; margin-right:8px;"></i> Approve or reject course registrations</li>
                <li style="margin-bottom:10px;"><i class='bx bx-receipt' style="color:#2e7d32; margin-right:8px;"></i> Generate and review student receipts</li>
                <li style="margin-bottom:10px;"><i class='bx bx-file' style="color:#2e7d32; margin-right:8px;"></i> Manage student records</li>
            </ul>
        </div>

    </div>
</div>

<!-- FOOTER -->
<footer style="text-align:center; padding:20px 15px; font-size:15px; color:#444; margin-top:auto;">
    <strong>Business Contacts</strong><br>
    📞 0759635652 | 📧 jamboubunifuhub@gmail.com<br>
    📍 Barut Centre, Chelnet Plaza, 1st Floor, Nakuru Kenya<br><br>
    © <?= date('Y') ?> Ubunifu Learning Hub — All Rights Reserved
</footer>

</body>
</html>
