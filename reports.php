<?php
$pageTitle = 'Reports';
require_once 'config/database.php';
require_once 'includes/header.php';
requireLogin();

$userId = $_SESSION['user_id'];
$year = $_GET['year'] ?? date('Y');

// Monthly totals for the year
$stmt = $conn->prepare("SELECT MONTH(expense_date) as month, SUM(amount) as total FROM expenses WHERE user_id = ? AND YEAR(expense_date) = ? GROUP BY MONTH(expense_date) ORDER BY month");
$stmt->bind_param("is", $userId, $year);
$stmt->execute();
$monthlyData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$months = array_fill(1, 12, 0);
foreach ($monthlyData as $m) { $months[(int)$m['month']] = (float)$m['total']; }

// Category totals for year
$stmt = $conn->prepare("SELECT category, SUM(amount) as total, COUNT(*) as count FROM expenses WHERE user_id = ? AND YEAR(expense_date) = ? GROUP BY category ORDER BY total DESC");
$stmt->bind_param("is", $userId, $year);
$stmt->execute();
$catData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$yearTotal = array_sum(array_column($catData, 'total'));

// Income vs Expense data (monthly)
$incomeData = array_fill(1, 12, 0);
$expenseData = array_fill(1, 12, 0);

// Income
$stmt = $conn->prepare("SELECT MONTH(income_date) as month, SUM(amount) as total FROM income WHERE user_id = ? AND YEAR(income_date) = ? GROUP BY MONTH(income_date)");
$stmt->bind_param("is", $userId, $year);
$stmt->execute();
$res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($res as $row) {
    $incomeData[(int)$row['month']] = (float)$row['total'];
}

// Expense (reuse existing months array)
foreach ($months as $m => $val) {
    $expenseData[$m] = $val;
}
?>


<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 fade-in-up">
        <h1 class="h3 fw-bold" style="letter-spacing:-0.02em;">Reports</h1>
        <form method="GET" class="d-flex gap-2">
            <select name="year" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <!-- Year Total -->
    <div class="card mb-4 fade-in-up">
        <div class="card-body text-center py-4">
            <div class="stat-label mb-1">Total Spent in <?php echo $year; ?></div>
            <div class="stat-value" style="font-size:2.5rem;">₹<?php echo number_format($yearTotal, 2); ?></div>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Monthly Bar Chart -->
        <div class="col-lg-8">
    <div class="card fade-in-up">
        <div class="card-header py-3">
            <span class="fw-semibold">Income vs Expense</span>
        </div>
        <div class="card-body">
            <div class="chart-container" style="height:350px;">
                <canvas id="comparisonChart"></canvas>
            </div>
        </div>
    </div>
</div>

        
        
        <!-- Category List -->
        <div class="col-lg-4">
            <div class="card fade-in-up">
                <div class="card-header py-3"><span class="fw-semibold">By Category</span></div>
                <div class="card-body p-0">
                    <div style="max-height: 380px; overflow-y: auto;">
                    <?php if (empty($catData)): ?>
                        <div class="text-center py-5 text-secondary"><p class="mb-0">No data for <?php echo $year; ?></p></div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($catData as $cat):
                                $pct = $yearTotal > 0 ? ($cat['total'] / $yearTotal * 100) : 0;
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-3">
                                <div>
                                    <div class="fw-medium"><?php echo htmlspecialchars($cat['category']); ?></div>
                                    <small class="text-secondary"><?php echo $cat['count']; ?> transactions</small>
                                </div>
                                <div class="text-end">
                                    <div class="amount-cell fw-medium">₹<?php echo number_format($cat['total'], 2); ?></div>
                                    <small class="text-secondary"><?php echo number_format($pct, 1); ?>%</small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div><!-- list-group -->
                    </div>   <!-- scroll container -->
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!--
<div class="col-lg-8">
    <div class="card fade-in-up">
        <h5>Income vs Expense</h5>
        <canvas id="comparisonChart"></canvas>
    </div>
                            -->
    
</div>
</div>

<script>
var monthlyTotals = <?php echo json_encode(array_values($months)); ?>;
</script>




<!-- ✅ ADD YOUR NEW JS HERE -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx2 = document.getElementById('comparisonChart');

new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [
            {
                label: 'Income',
                data: <?php echo json_encode(array_values($incomeData)); ?>,
                backgroundColor: '#28a745'
            },
            {
                label: 'Expense',
                data: <?php echo json_encode(array_values($expenseData)); ?>,
                backgroundColor: '#dc3545'
            }
        ]
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
<?php require_once 'includes/footer.php'; ?>
