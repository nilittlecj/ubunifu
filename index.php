<?php  
session_start();
include 'datas.php';

$msg = "";

if (isset($_POST['register'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role  = trim($_POST['role']);

    if (!empty($name) && !empty($email) && !empty($password) && !empty($role)) {

        // Check for duplicate email
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            $msg = "❌ Email already registered!";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed, $role);

            if ($stmt->execute()) {
                $msg = "✅ Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $msg = "❌ Error: " . $stmt->error;
            }
        }

    } else {
        $msg = "⚠️ All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Registration - Ubunifu Learning Hub</title>

<style>
/* ===== RESET ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', Arial, sans-serif;
}

/* ===== BODY ===== */
body {
    background: linear-gradient(135deg, #d7f5d6, #a5d9ba);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding-top: 120px;
}

/* ===== HEADER ===== */
.myheader {
    width: 100%;
    background: #046307;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 999;
    box-shadow: 0 3px 10px rgba(0,0,0,0.15);
}
.myheader img {
    width: 60px;
}
.myheader h1 {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    letter-spacing: .5px;
}

/* ===== CENTER WRAPPER ===== */
.center-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 25px;
}

/* ===== CONTAINER ===== */
.container {
    width: 90%;
    max-width: 500px;
    background: #ffffff;
    padding: 40px 35px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,.18);
    animation: fadeInUp .7s ease forwards;
}
.container h2 {
    text-align: center;
    color: #046307;
    font-size: 28px;
    margin-bottom: 20px;
}

/* ===== FORM ELEMENTS ===== */
label {
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
    color: #034f09;
}

input, select {
    width: 100%;
    padding: 13px;
    border: 2px solid #cdeccc;
    border-radius: 10px;
    margin-bottom: 18px;
    outline: none;
    font-size: 15px;
    transition: .3s;
}

input:focus,
select:focus {
    border-color: #046307;
    box-shadow: 0 0 8px rgba(4, 99, 7, 0.2);
}

/* ===== BUTTON ===== */
button {
    width: 100%;
    background: #046307;
    color: white;
    border: none;
    padding: 14px;
    border-radius: 10px;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    transition: .3s;
}
button:hover {
    background: #058c15;
    transform: translateY(-3px);
}

/* ===== MESSAGE ===== */
.msg {
    text-align: center;
    font-size: 15px;
    margin-bottom: 15px;
    font-weight: 600;
}

/* ===== FOOTER ===== */
footer {
    text-align: center;
    padding: 20px 10px;
    font-size: 15px;
    color: #2e2e2e;
    margin-top: auto;
}

/* ===== ANIMATION ===== */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(25px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<header class="myheader">
    <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Ubunifu Logo">
    <h1>Learning Hub</h1>
</header>

<div class="center-wrapper">
    <div class="container">

        <?php if($msg): ?>
            <p class="msg" style="color:<?= strpos($msg,'✅')!==false ? '#00b860' : '#d60000'; ?>;">
                <?= $msg ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <h2>Register</h2>

            <label>Name:</label>
            <input type="text" name="name" placeholder="Enter full name" required>

            <label>Email:</label>
            <input type="email" name="email" placeholder="Enter email" required>

            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter password" required>

            <label>Role:</label>
            <select name="role" required>
                <option value="user">User (Student)</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit" name="register">Register</button>
        </form>

        <p style="text-align:center; margin-top:10px;">
            Already have an account? 
            <a href="login.php" style="color:#046307; font-weight:bold;">Login Here</a>
        </p>

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
