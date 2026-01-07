<?php
session_start();
include 'datas.php';

// ✅ Only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Example DB queries (replace with your real ones)
$total_students = 120;
$total_collected = 180000;
$total_balance = 35000;

// Example payment data
$payments = [
    ["John Doe", "Web Development", 1700, 0, "Cleared", "2025-10-20"],
    ["Jane Smith", "Data Science", 1300, 400, "Pending", "2025-10-22"],
    ["Mike Brown", "Graphic Design", 1700, 0, "Cleared", "2025-10-25"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Reports</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="design.css">

<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f5f6fa;
    margin: 0;
    overflow-x: hidden;
}

.container {
    width: 90%;
    margin: 30px auto;
}

h2 {
    color: #2e7d32;
    font-weight: 600;
    text-align: center;
    margin-bottom: 30px;
}

/* Summary cards */
.summary {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}

.card {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    padding: 20px;
    width: 250px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    transition: 0.3s;
}
.card:hover {
    transform: translateY(-5px);
}
.card i {
    font-size: 40px;
    margin-bottom: 10px;
}

/* Table styling */
.table-container {
    background: white;
    padding: 20px;
    margin-top: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
}

th, td {
    padding: 12px 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background: #f1f1f1;
    font-weight: 600;
}

.status-cleared {
    color: green;
    font-weight: bold;
}
.status-pending {
    color: red;
    font-weight: bold;
}

/* Charts section */
.chart-section {
    background: white;
    margin-top: 30px;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Buttons */
.btn-bar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 15px;
}
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: 0.3s;
}
.action-btn:hover {
    transform: scale(1.05);
}

.logout {
    background: linear-gradient(135deg, #c62828, #e53935);
}

@media (max-width: 768px) {
    .summary { flex-direction: column; align-items: center; }
}
</style>
</head>

<body>
<div class="container">
    <h2>📊 Admin Reports Dashboard</h2>

    <!-- Summary Cards -->
    <div class="summary">
        <div class="card"><i class='bx bxs-user'></i><h3><?= $total_students; ?></h3><p>Total Students</p></div>
        <div class="card"><i class='bx bx-money'></i><h3>KSh <?= number_format($total_collected); ?></h3><p>Total Collected</p></div>
        <div class="card"><i class='bx bx-error-circle'></i><h3>KSh <?= number_format($total_balance); ?></h3><p>Outstanding Balance</p></div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <h3>💰 Payment Records</h3>
        <div class="btn-bar">
            <a href="#" class="action-btn"><i class='bx bx-printer'></i> Print</a>
            <a href="#" class="action-btn"><i class='bx bx-download'></i> Export PDF</a>
            <a href="#" class="action-btn"><i class='bx bx-download'></i> Export Excel</a>
            <a href="logout.php" class="action-btn logout"><i class='bx bx-log-out'></i> Logout</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Paid (KSh)</th>
                    <th>Balance (KSh)</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $count=1; foreach($payments as $p): ?>
                <tr>
                    <td><?= $count++; ?></td>
                    <td><?= htmlspecialchars($p[0]); ?></td>
                    <td><?= htmlspecialchars($p[1]); ?></td>
                    <td><?= number_format($p[2]); ?></td>
                    <td><?= number_format($p[3]); ?></td>
                    <td class="<?= $p[4] === 'Cleared' ? 'status-cleared' : 'status-pending'; ?>">
                        <?= htmlspecialchars($p[4]); ?>
                    </td>
                    <td><?= htmlspecialchars($p[5]); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Charts -->
    <div class="chart-section">
        <h3>📈 Payment Overview</h3>
        <canvas id="paymentChart" height="100"></canvas>
        <h3 style="margin-top:30px;">👨‍🎓 Students Per Course</h3>
        <canvas id="studentChart" height="100"></canvas>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx1 = document.getElementById('paymentChart');
const ctx2 = document.getElementById('studentChart');

new Chart(ctx1, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Monthly Payments (KSh)',
            data: [12000, 18000, 25000, 30000, 28000, 32000],
            borderColor: '#2e7d32',
            backgroundColor: 'rgba(46,125,50,0.2)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: { y: { beginAtZero: true } }
    }
});

new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: ['Web Dev', 'Data Science', 'Graphic Design', 'Cyber Security'],
        datasets: [{
            label: 'Students',
            data: [30, 20, 25, 15],
            backgroundColor: ['#2e7d32', '#43a047', '#66bb6a', '#81c784']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
