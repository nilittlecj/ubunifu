<?php    
session_start();
include 'datas.php';

// Ensure only logged-in students can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$msg = "";

// Handle course registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $user_id = $_SESSION['user']['id'];
    $course  = trim($_POST['course']);
    $contact = trim($_POST['contact']);
    $module  = trim($_POST['module']);
    $period  = trim($_POST['period']);

    if (!empty($course) && !empty($contact) && !empty($module) && !empty($period)) {
        $check = $conn->prepare("SELECT id FROM students WHERE user_id=? AND course=? AND module=? AND period=?");
        $check->bind_param("isss", $user_id, $course, $module, $period);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $msg = "⚠️ You have already registered for this course and module.";
        } else {
            $stmt = $conn->prepare("INSERT INTO students (user_id, course, contact, module, period, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issss", $user_id, $course, $contact, $module, $period);

            if ($stmt->execute()) {
                $msg = "✅ Registration submitted successfully! Please wait for admin approval.";
            } else {
                $msg = "❌ Error: " . $stmt->error;
            }
        }
    } else {
        $msg = "⚠️ All fields are required.";
    }
}

// Fetch student's registered courses
$stmt = $conn->prepare("SELECT id, course, module, period, contact, status, created_at FROM students WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user']['id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Course Registration</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="design.css"> 
<style>
/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", Tahoma, sans-serif;
}

/* BODY */
body {
    background: #f4f7fb;
    color: #333;
}

/* HEADER */
.myheader {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    background: linear-gradient(135deg, #046307, #0a8f3c);
    color: #fff;
    padding: 18px;
}

.myheader img {
    width: 60px;
    background: transparent;
    padding: 0;
}

.myheader h1 {
    font-size: 26px;
}

/* CENTER WRAPPER */
.center-wrapper {
    display: flex;
    justify-content: center;
    padding: 30px 15px;
}

/* MAIN CONTAINER */
.container {
    width: 100%;
    max-width: 900px;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

/* HEADINGS */
.container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #046307;
}

.container h3 {
    margin-bottom: 15px;
    color: #046307;
}

/* FORM */
form label {
    font-weight: 600;
    margin-top: 12px;
    display: block;
}

form input,
form select {
    width: 100%;
    padding: 11px;
    margin-top: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 15px;
}

form input:disabled {
    background: #f1f1f1;
    cursor: not-allowed;
}

/* BUTTONS */
.action-btn {
    background: #046307;
    color: #fff;
    text-decoration: none;
    padding: 12px 18px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: 0.3s;
    display: inline-block;
    text-align: center;
}

.action-btn:hover {
    background: #034d05;
}

.logout {
    background: #c0392b;
}

.logout:hover {
    background: #992d22;
}

/* ALERT MESSAGES */
.view-btn {
    background: #e6f7ec;
    color: #046307;
    padding: 12px 15px;
    border-radius: 6px;
    border-left: 5px solid #046307;
}

.no-receipt {
    background: #fdecea;
    color: #b71c1c;
    padding: 12px 15px;
    border-radius: 6px;
    border-left: 5px solid #b71c1c;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

table th {
    background: #046307;
    color: #fff;
    padding: 12px;
    text-align: left;
}

table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

table tr:nth-child(even) {
    background: #f9f9f9;
}

/* STATUS COLORS */
.pending {
    color: #f39c12;
    font-weight: 600;
}

.approved {
    color: #046307;
    font-weight: 600;
}

.rejected {
    color: #c0392b;
    font-weight: 600;
}

/* TOP BUTTONS */
.top-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

/* FOOTER (BACKGROUND REMOVED) */
footer {
    background: transparent;
    color: #333;
    text-align: center;
    padding: 18px;
    margin-top: 40px;
    font-size: 14px;
    border-top: 1px solid #ddd; /* optional clean divider */
}

/* RESPONSIVE */
@media (max-width: 600px) {
    .container {
        padding: 20px;
    }

    table {
        font-size: 14px;
    }

    .top-buttons {
        flex-direction: column;
    }
}

</style>
</head>
<body>

<!-- HEADER -->
<header class="myheader">
    <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Ubunifu Logo">
    <h1>Learning Hub</h1>
</header>

<!-- MAIN CONTENT -->
<div class="center-wrapper">
    <div style="display: flex; flex-direction: column; " class="container">
        <h2>Student Course Registration</h2>

        <?php if(!empty($msg)): ?>
            <p class="<?= strpos($msg,'✅')!==false ? 'view-btn' : 'no-receipt' ?>" style="margin-bottom:20px; display:inline-block;">
                <?= $msg ?>
            </p>
        <?php endif; ?>

        <form method="POST" style="text-align:left;">
            <label>Full Name:</label>
            <input type="text" value="<?= htmlspecialchars($_SESSION['user']['name']) ?>" disabled>

            <label>Email:</label>
            <input type="email" value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" disabled>
<br>
<br>
            <label>Course:</label>
            <select name="course" required>
                <option value="">-- Select a Course --</option>
                <option>Computer Packages</option>
                <option>Web Design</option>
                <option>Graphics Design</option>
                <option>CBC Classes</option>
                <option>Programming Languages</option>
                <option>3D Design</option>
                <option>Cyber Services</option>
                <option>Library Services</option>
            </select>

            <label style=>Module:</label>
            <select name="module" required>
                <option value="">-- Select Module --</option>
                <option>Module 1 - Beginner</option>
                <option>Module 2 - Intermediate</option>
                <option>Module 3 - Advanced</option>
            </select>

            <label>Period:</label>
            <select name="period" required>
                <option value="">-- Select Period --</option>
                <option>3 Months</option>
                <option>6 Months</option>
                <option>1 Year</option>
            </select>
<br>
<br>
            <label>Contact:</label>
            <input type="text" name="contact" placeholder="Enter contact" required>

            <button type="submit" name="submit" class="action-btn" style="width:100%; margin-top:20px;">Submit Registration</button>
        </form>

        <h3 style="margin-top:35px; color:#046307;">Your Registered Courses</h3>

        <?php if($result->num_rows>0): ?>
        <table>
            <tr>
                <th>Course</th>
                <th>Module</th>
                <th>Period</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            <?php while($row=$result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['course']) ?></td>
                    <td><?= htmlspecialchars($row['module']) ?></td>
                    <td><?= htmlspecialchars($row['period']) ?></td>
                    <td><?= htmlspecialchars($row['contact']) ?></td>
                    <td class="<?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
            <p class="no-receipt">You have not registered for any courses yet.</p>
        <?php endif; ?>

        <div class="top-buttons" style="margin-top:25px;">
            <a href="user_dashboard.php" class="action-btn">⬅ Back</a>
            <a href="logout.php" class="action-btn logout">Logout</a>
        </div>

    </div>
</div>

<!-- FOOTER -->
<footer>
    <strong>Business Contacts</strong><br>
    📞 0759635652 | 📧 jamboubunifuhub@gmail.com<br>
    📍 Barut Centre, Chelnet Plaza, Nakuru Kenya<br><br>
    © <?= date('Y') ?> Ubunifu Learning Hub — All Rights Reserved
</footer>

</body>
</html>
