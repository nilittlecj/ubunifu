    <?php
    include 'datas.php';


    if (isset($_POST['submit'])) {
        $user_id = 1; // Example: replace with $_SESSION['user']['id'] if users login
        $contact = trim($_POST['contact']);
        $course  = trim($_POST['course']);

        if (!empty($contact) && !empty($course)) {
            $stmt = $conn->prepare("INSERT INTO students (user_id, contact, course) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $contact, $course);

            if ($stmt->execute()) {
                echo "<p style='color:green; text-align:center;'>✅ Registration successful!</p>";
            } else {
                echo "<p style='color:red; text-align:center;'>❌ Error: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color:red; text-align:center;'>⚠️ All fields are required.</p>";
        }
    }