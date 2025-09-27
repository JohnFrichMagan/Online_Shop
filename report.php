<?php
session_start();
include 'db.php';

// --- Check if admin is logged in ---
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// --- Get order status counts for pie chart ---
$statusCounts = ['Pending'=>0,'Processing'=>0,'Shipped'=>0,'Delivered'=>0,'Cancelled'=>0];
$result = $conn->query("SELECT status, COUNT(*) AS count FROM orders GROUP BY status");
while($row = $result->fetch_assoc()){
    $statusCounts[$row['status']] = intval($row['count']);
}

// --- Sales summaries ---
$salesMonthly = $conn->query("SELECT MONTH(created_at) AS month, SUM(total) AS total FROM orders GROUP BY MONTH(created_at) ORDER BY month");
$salesMidYear = $conn->query("SELECT QUARTER(created_at) AS quarter, SUM(total) AS total FROM orders GROUP BY QUARTER(created_at) ORDER BY quarter");
$salesYearly = $conn->query("SELECT YEAR(created_at) AS year, SUM(total) AS total FROM orders GROUP BY YEAR(created_at) ORDER BY year");

// --- Simple prediction logic ---
$currentYearSales = $conn->query("SELECT SUM(total) AS total FROM orders WHERE YEAR(created_at)=YEAR(CURDATE())")->fetch_assoc()['total'];
$predictedNextYear = $currentYearSales * 1.10;

// --- Total users ---
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports | Admin</title>
<style>
/* Global */
html, body {
    margin:0;
    padding:0;
    overflow-x:hidden; /* remove horizontal scrollbar */
    overflow-y:auto;   /* allow vertical scrolling */
    height:100%;
    font-family:"Poppins",sans-serif;
    background:#1f293a;
    color:#fff;
}
h2,h3,h4 { margin:0; }

/* Header */
header { background:#3b82f6; padding:15px 40px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 8px rgba(0,0,0,0.15);}
header h2 { color:#fff; font-weight:600; }
nav { flex:1; display:flex; justify-content:center; gap:20px;}
nav a { text-decoration:none; color:#fff; font-weight:500; padding:10px 20px; border-radius:10px; background:linear-gradient(135deg,#2563eb,#3b82f6); box-shadow:0 4px 12px rgba(59,130,246,0.4); transition:0.3s;}
nav a:hover { background:linear-gradient(135deg,#3b82f6,#2563eb); transform:translateY(-2px);}
nav a.active { background:#1e40af; }
.logout { text-decoration:none; background:#ef4444; color:#fff; padding:10px 20px; border-radius:10px; font-weight:500; transition:0.3s;}
.logout:hover { background:#dc2626; transform:scale(1.05); }

/* Page title */
.page-title { text-align:center; margin:25px 0; font-size:1.8rem; font-weight:600; }

/* Flex container */
.flex-container { display:flex; max-width:1200px; margin:0 auto; gap:20px; flex-wrap:wrap; }

/* Boxes */
.chart-box, .prediction-box {
    flex: 1;
    min-width: 250px;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

.chart-box {
    background: #fff;
    color: #000;
    display: flex;
    flex-direction: column;
    align-items: center;      /* center horizontally */
    justify-content: center;  /* center vertically */
    text-align: center;       /* center text inside */
    height: 280px;
}

.prediction-box { background:#10b981; color:#fff; text-align:center; display:flex; flex-direction:column; justify-content:center; align-items:center; height:280px; }

.prediction-box h3 { margin-bottom:10px; font-size:1rem; font-weight:500; }
.prediction-value { font-size:1.5rem; font-weight:700; margin-bottom:6px; }
.prediction-box p { font-size:0.85rem; opacity:0.9; margin:0; }

.total-users-box { background:#2563eb; color:#fff; margin-top:15px; padding:12px; border-radius:10px; width:80%; text-align:center; box-shadow:0 3px 10px rgba(0,0,0,0.2);}
.total-users-box h4 { font-size:0.9rem; font-weight:600; margin-bottom:6px; }
.total-users-value { font-size:1.2rem; font-weight:700; }

/* Chart */
.chart-container canvas { max-width:300px; margin-left:auto; margin-right:0; display:block; height:180px !important; }

/* Modal */
.modal {
    display:none;
    position:fixed;
    top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
    z-index:1000;
}
.modal-content {
    background:#fff;
    color:#000;
    padding:25px;
    border-radius:15px;
    width:90%;
    max-width:900px;
    box-shadow:0 6px 20px rgba(0,0,0,0.25);
    transform:scale(0.85);
    opacity:0;
    transition: all 0.3s ease;
}
.modal.show .modal-content {
    transform:scale(1);
    opacity:1;
}

/* Table inside modal */
table { width:100%; border-collapse:collapse; margin-top:10px; font-size:0.95rem; }
th, td { border:1px solid #ddd; padding:10px; text-align:center; }
th { background:#2563eb; color:#fff; }
tbody tr:nth-child(odd) { background:#f9fafb; }
tbody tr:nth-child(even) { background:#f3f4f6; }
tbody tr:hover { background:#e5e7eb; cursor:pointer; }

/* Buttons */
button.filter-btn {
    padding:8px 15px; background:#2563eb; color:#fff; border:none; border-radius:8px;
    margin:5px; cursor:pointer; font-weight:500; transition:0.3s;
}
button.filter-btn:hover { background:#1e40af; transform:scale(1.05); }

.close-btn {
    background:#ef4444;
    color:#fff;
    border:none;
    border-radius:8px;
    padding:8px 15px;
    cursor:pointer;
    font-weight:500;
    float:right;
}
.close-btn:hover { background:#dc2626; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header>
    <h2>Admin Dashboard</h2>
    <nav>
        <a href="home.php" class="<?= basename($_SERVER['PHP_SELF'])=='home.php'?'active':'' ?>">🏠 Dashboard</a>
        <a href="products.php" class="<?= basename($_SERVER['PHP_SELF'])=='products.php'?'active':'' ?>">📦 Products</a>
        <a href="order.php" class="<?= basename($_SERVER['PHP_SELF'])=='order.php'?'active':'' ?>">🛒 Orders</a>
        <a href="report.php" class="<?= basename($_SERVER['PHP_SELF'])=='report.php'?'active':'' ?>">📊 Reports</a>
        <a href="manage.php" class="<?= basename($_SERVER['PHP_SELF'])=='manage.php'?'active':'' ?>">📋 Manage</a>
        <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF'])=='settings.php'?'active':'' ?>">⚙️ Settings</a>
    </nav>
    <a href="logout.php" class="logout">🚪 Logout</a>
</header>

<h2 class="page-title">Reports Dashboard</h2>

<div class="flex-container">
    <!-- Prediction Box -->
    <div class="prediction-box">
        <h3>Predicted Sales Next Year</h3>
        <div class="prediction-value">$<?= number_format($predictedNextYear,2) ?></div>
        <p>Based on current trends (+10%)</p>

        <!-- Total Users Box -->
        <div class="total-users-box">
            <h4>Total Users</h4>
            <div class="total-users-value"><?= $totalUsers ?></div>
        </div>
    </div>

    <!-- Pie Chart Box -->
    <div class="chart-box">
        <h3 style="text-align:center; margin-bottom:10px;">Order Status Distribution</h3>
        <canvas id="statusChart"></canvas>
    </div>
</div>

<!-- Sales Button -->
<div style="text-align:center; margin:30px;">
    <button class="filter-btn" onclick="openModal()">📊 Show Sales Report</button>
</div>

<!-- Sales Modal -->
<div class="modal" id="salesModal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal()">✖ Close</button>
        <h3 style="text-align:center;">Sales Reports</h3>
        <div style="text-align:center; margin-bottom:15px;">
            <button class="filter-btn" onclick="showTable('monthly')">Monthly</button>
            <button class="filter-btn" onclick="showTable('midyear')">Mid-Year</button>
            <button class="filter-btn" onclick="showTable('yearly')">Yearly</button>
        </div>

        <table id="salesTable">
            <thead>
                <tr id="tableHeader">
                    <th>Month</th>
                    <th>Total Sales ($)</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                $salesMonthly = $conn->query("SELECT MONTH(created_at) AS month, SUM(total) AS total FROM orders GROUP BY MONTH(created_at) ORDER BY month");
                while($row = $salesMonthly->fetch_assoc()): ?>
                <tr onclick="window.location='order.php?month=<?= $row['month'] ?>'">
                    <td><?= htmlspecialchars($row['month']) ?></td>
                    <td><?= number_format($row['total'],2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Print Button -->
        <div style="text-align:center; margin-top:20px;">
            <button class="filter-btn" onclick="printAllReports()">🖨️ Print All Reports</button>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(ctx, {
    type:'pie',
    data:{
        labels: <?= json_encode(array_keys($statusCounts)) ?>,
        datasets:[{ 
            data: <?= json_encode(array_values($statusCounts)) ?>, 
            backgroundColor:['#fbbf24','#3b82f6','#6366f1','#10b981','#ef4444'],
            borderWidth:1 
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{ position:'bottom', align:'end', labels:{ usePointStyle:true, padding:15 } }
        }
    }
});

// Modal functions
function openModal(){
    document.getElementById('salesModal').style.display = 'flex';
    setTimeout(()=>document.getElementById('salesModal').classList.add('show'),10);
}
function closeModal(){
    const modal=document.getElementById('salesModal');
    modal.classList.remove('show');
    setTimeout(()=>modal.style.display='none',300);
}

// Sales table data
let monthlyData = <?php
$monthlyData=[]; $salesMonthly=$conn->query("SELECT MONTH(created_at) AS month,SUM(total) AS total FROM orders GROUP BY MONTH(created_at) ORDER BY month");
while($r=$salesMonthly->fetch_assoc()){$monthlyData[]=$r;}
echo json_encode($monthlyData);
?>;
let midYearData = <?php
$midData=[]; $salesMidYear=$conn->query("SELECT QUARTER(created_at) AS quarter,SUM(total) AS total FROM orders GROUP BY QUARTER(created_at) ORDER BY quarter");
while($r=$salesMidYear->fetch_assoc()){$midData[]=$r;}
echo json_encode($midData);
?>;
let yearlyData = <?php
$yearData=[]; $salesYearly=$conn->query("SELECT YEAR(created_at) AS year,SUM(total) AS total FROM orders GROUP BY YEAR(created_at) ORDER BY year");
while($r=$salesYearly->fetch_assoc()){$yearData[]=$r;}
echo json_encode($yearData);
?>;

function showTable(type){
    let tbody=document.getElementById('tableBody');
    let header=document.getElementById('tableHeader');
    tbody.innerHTML='';
    if(type==='monthly'){
        header.innerHTML='<th>Month</th><th>Total Sales ($)</th>';
        monthlyData.forEach(r=>{
            tbody.innerHTML+=`<tr onclick="window.location='order.php?month=${r.month}'"><td>${r.month}</td><td>${parseFloat(r.total).toFixed(2)}</td></tr>`;
        });
    } else if(type==='midyear'){
        header.innerHTML='<th>Quarter</th><th>Total Sales ($)</th>';
        midYearData.forEach(r=>{
            tbody.innerHTML+=`<tr onclick="window.location='order.php?quarter=${r.quarter}'"><td>Q${r.quarter}</td><td>${parseFloat(r.total).toFixed(2)}</td></tr>`;
        });
    } else if(type==='yearly'){
        header.innerHTML='<th>Year</th><th>Total Sales ($)</th>';
        yearlyData.forEach(r=>{
            tbody.innerHTML+=`<tr onclick="window.location='order.php?year=${r.year}'"><td>${r.year}</td><td>${parseFloat(r.total).toFixed(2)}</td></tr>`;
        });
    }
}

function printAllReports(){
    let printWindow = window.open('', '', 'width=900,height=650');
    let html = `
    <html>
    <head>
        <title>Sales Reports</title>
        <style>
            body { font-family: Poppins, sans-serif; color:#000; padding:20px; }
            h2 { text-align:center; margin-bottom:20px; }
            table { width:100%; border-collapse: collapse; margin-bottom:30px; }
            th, td { border:1px solid #333; padding:8px; text-align:center; }
            th { background:#2563eb; color:#fff; }
            tr:nth-child(odd){ background:#f9fafb; }
            tr:nth-child(even){ background:#f3f4f6; }
        </style>
    </head>
    <body>
        <h2>Complete Sales Reports</h2>
        
        <h3>Monthly Sales</h3>
        <table>
            <tr><th>Month</th><th>Total Sales ($)</th></tr>
            ${monthlyData.map(r => `<tr><td>${r.month}</td><td>${parseFloat(r.total).toFixed(2)}</td></tr>`).join('')}
        </table>

        <h3>Mid-Year Sales</h3>
        <table>
            <tr><th>Quarter</th><th>Total Sales ($)</th></tr>
            ${midYearData.map(r => `<tr><td>Q${r.quarter}</td><td>${parseFloat(r.total).toFixed(2)}</td></tr>`).join('')}
        </table>

        <h3>Yearly Sales</h3>
        <table>
            <tr><th>Year</th><th>Total Sales ($)</th></tr>
            ${yearlyData.map(r => `<tr><td>${r.year}</td><td>${parseFloat(r.total).toFixed(2)}</td></tr>`).join('')}
        </table>
    </body>
    </html>
    `;
    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.print();
}
</script>

</body>
</html>
