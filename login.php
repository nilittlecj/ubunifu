<?php  
session_start();
include("datas.php");

$msg = "";

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $msg = "⚠️ All fields are required!";
    } else {
        $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                $_SESSION['user'] = [
                    "id"    => $row['id'],
                    "email" => $row['email'],
                    "name"  => $row['name'],
                    "role"  => $row['role']
                ];

                header("Location: " . ($row['role'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php"));
                exit;
            } else {
                $msg = "❌ Incorrect password.";
            }
        } else {
            $msg = "❌ No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <style>
        /* BODY */
        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #d7f5d6, #a5d9ba);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 120px;
        }

        /* HEADER */
        header {
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

        header img {
            width: 55px;
        }

        header h1 {
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            letter-spacing: .5px;
            margin: 0;
        }

        /* CENTER PANEL */
        .center-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
        }

        .login-box {
            width: 90%;
            max-width: 430px;
            background: #ffffff;
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,.18);
            animation: fadeInUp .7s ease forwards;
        }

        .login-box h2 {
            text-align: center;
            color: #046307;
            font-size: 28px;
            margin-bottom: 20px;
        }

        /* INPUTS */
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            color: #034f09;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 2px solid #cdeccc;
            border-radius: 10px;
            margin-bottom: 18px;
            outline: none;
            font-size: 15px;
            transition: .3s;
        }

        input:focus {
            border-color: #046307;
            box-shadow: 0 0 8px rgba(4, 99, 7, 0.2);
        }

        /* BUTTON */
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

        /* SIGNUP LINK */
        .signup {
            text-align: center;
            margin-top: 18px;
            font-size: 15px;
        }

        .signup a {
            color: #046307;
            font-weight: 600;
            text-decoration: none;
        }

        .signup a:hover {
            text-decoration: underline;
        }

        /* FOOTER */
        footer {
            text-align: center;
            padding: 20px 10px;
            font-size: 14px;
            color: #2e2e2e;
            margin-top: auto;
        }

        /* ANIMATION */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

</head>
<body>

    <!-- HEADER -->
    <header>
        <img src="https://ubunifu.io/assets/img/Ubunifu.png" alt="Ubunifu Logo">
        <h1>Learning Hub</h1>
    </header>

    <!-- LOGIN BOX -->
    <div class="center-wrapper">
        <div class="login-box">

            <h2>Log In</h2>

            <?php if ($msg): ?>
                <p style="color:<?= (strpos($msg,'❌')!==false || strpos($msg,'⚠️')!==false) ? 'red' : 'green' ?>;
                           text-align:center; margin-bottom:15px; font-weight:600;">
                    <?= $msg ?>
                </p>
            <?php endif; ?>

            <form method="POST">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter email" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>

                <button type="submit" name="submit">Log In</button>
            </form>

            <p class="signup">
                Don’t have an account?
                <a href="index.php">Sign Up Here</a>
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
