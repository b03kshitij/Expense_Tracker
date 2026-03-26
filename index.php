<?php
$pageTitle = 'Dashboard';
require_once 'config/database.php';
require_once 'includes/header.php';
requireLogin();

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');

// Total Income (this month)
$incomeQuery = $conn->prepare("
    SELECT SUM(amount) as total_income 
    FROM income 
    WHERE user_id = ? 
    AND MONTH(income_date) = MONTH(CURRENT_DATE())
");

$incomeQuery->bind_param("i", $_SESSION['user_id']);
$incomeQuery->execute();
$incomeResult = $incomeQuery->get_result()->fetch_assoc();

$totalIncome = $incomeResult['total_income'] ?? 0;

// Stats
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?");
$stmt->bind_param("iss", $userId, $monthStart, $monthEnd);
$stmt->execute();
$monthlyTotal = $stmt->get_result()->fetch_row()[0];

$balance = $totalIncome - $monthlyTotal;


$stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id = ? AND expense_date = ?");
$stmt->bind_param("is", $userId, $today);
$stmt->execute();
$todayTotal = $stmt->get_result()->fetch_row()[0];

$stmt = $conn->prepare("SELECT COUNT(*) FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?");
$stmt->bind_param("iss", $userId, $monthStart, $monthEnd);
$stmt->execute();
$txCount = $stmt->get_result()->fetch_row()[0];

$avgDaily = $txCount > 0 ? $monthlyTotal / date('j') : 0;

// Recent expenses
$stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY expense_date DESC, created_at DESC LIMIT 10");
$stmt->bind_param("i", $userId);
$stmt->execute();
$recentExpenses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Category breakdown
$stmt = $conn->prepare("SELECT category, SUM(amount) as total FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ? GROUP BY category ORDER BY total DESC");
$stmt->bind_param("iss", $userId, $monthStart, $monthEnd);
$stmt->execute();
$categoryData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get categories for modal
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-4">
    <!-- Welcome -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 fade-in-up">
        <div>
            <h1 class="h3 fw-bold mb-1" style="letter-spacing:-0.02em;">Dashboard</h1>
            <p class="mb-0 text-secondary" style="font-size:0.9rem;">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Here's your overview for <?php echo date('F Y'); ?>.</p>
        </div>
       <div class="d-flex gap-2 mt-2 mt-md-0">
    
        <!--
            <button class="btn btn-accent">
                <i class="bi bi-plus-lg me-1"></i> Add Expense
            </button>

            <button class="btn btn-accent">
                <i class="bi bi-arrow-down-circle me-1"></i> Add Income
            </button>
        -->
</div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-card fade-in-up">
                <div class="stat-icon green"><i class="bi bi-calendar-month"></i></div>
                <div class="stat-value">₹<?php echo number_format($monthlyTotal, 2); ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card fade-in-up">
                <div class="stat-icon blue"><i class="bi bi-calendar-day"></i></div>
                <div class="stat-value">₹<?php echo number_format($todayTotal, 2); ?></div>
                <div class="stat-label">Today</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card fade-in-up">
                <div class="stat-icon amber"><i class="bi bi-graph-up"></i></div>
                <div class="stat-value">₹<?php echo number_format($avgDaily, 2); ?></div>
                <div class="stat-label">Daily Average</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card fade-in-up">
                <div class="stat-icon red"><i class="bi bi-receipt"></i></div>
                <div class="stat-value"><?php echo $txCount; ?></div>
                <div class="stat-label">Transactions</div>
            </div>
        </div>
    </div>

<div class="row g-3 mb-4">

    <!-- Income -->
    <div class="col-6 col-lg-3">
        <div class="card stat-card fade-in-up">
            <div class="stat-icon green">
                <i class="bi bi-arrow-down-circle"></i>
            </div>
            <div class="stat-value">₹<?php echo number_format($totalIncome, 2); ?></div>
            <div class="stat-label">Income</div>
        </div>
    </div>

    <!-- Balance -->
    <div class="col-6 col-lg-3">
        <div class="card stat-card fade-in-up">
            <div class="stat-icon blue">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="stat-value">₹<?php echo number_format($balance, 2); ?></div>
            <div class="stat-label">Balance</div>
        </div>
    </div>

</div>

<div class="row g-4">
        <!-- Chart -->
        <div class="col-lg-5">
            <div class="card fade-in-up">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <span class="fw-semibold">Category Breakdown</span>
                </div>
                <div class="card-body">
                    <?php if (empty($categoryData)): ?>
                        <div class="text-center py-5 text-secondary">
                            <i class="bi bi-pie-chart fs-1 d-block mb-2 opacity-25"></i>
                            <p class="mb-0">No expenses yet this month</p>
                        </div>
                    <?php else: ?>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="col-lg-7">
            <div class="card fade-in-up">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <span class="fw-semibold">Recent Expenses</span>
                    <a href="expenses.php" class="btn btn-sm btn-outline-dark">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentExpenses)): ?>
                        <div class="text-center py-5 text-secondary">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                            <p class="mb-0">No expenses recorded yet</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr><th>Date</th><th>Category</th><th>Description</th><th class="text-end">Amount</th><th></th></tr>
                                </thead>
                                <tbody id="recentExpensesList">
                                    <?php foreach ($recentExpenses as $exp): ?>
                                    <tr data-id="<?php echo $exp['id']; ?>">
                                        <td><?php echo date('M d', strtotime($exp['expense_date'])); ?></td>
                                        <td><span class="category-badge"><i class="bi bi-tag"></i><?php echo htmlspecialchars($exp['category']); ?></span></td>
                                        <td><?php echo htmlspecialchars($exp['description'] ?: '-'); ?></td>
                                        <td class="text-end amount-cell">₹<?php echo number_format($exp['amount'], 2); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-link text-danger delete-expense p-0" data-id="<?php echo $exp['id']; ?>" title="Delete">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bi bi-plus-circle me-2" style="color:var(--accent);"></i>Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addExpenseForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount (₹)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00" style="font-family:'JetBrains Mono',monospace;font-size:1.25rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="expense_date" class="form-control" required value="<?php echo $today; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <small class="text-secondary">(optional)</small></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Lunch at cafe" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent" id="saveExpenseBtn">
                        <i class="bi bi-check-lg me-1"></i>Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-arrow-down-circle me-2 text-success"></i>Add Income
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="addIncomeForm">
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Amount (₹)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Source</label>
                        <input type="text" name="source" class="form-control" placeholder="Salary, Freelance..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="income_date" class="form-control" required value="<?php echo $today; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <small class="text-secondary">(optional)</small></label>
                        <input type="text" name="description" class="form-control" maxlength="255">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>Save Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
var categoryData = <?php echo json_encode($categoryData); ?>;
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("addIncomeForm");

    if (form) {
        form.addEventListener("submit", async function(e) {
            e.preventDefault();

            const formData = new FormData(form); // ✅ FIX

            try {
                let res = await fetch("ajax/add_income.php", {
                    method: "POST",
                    body: formData
                });

                let data = await res.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert("Failed to add income");
                }

            } catch (err) {
                console.error(err);
                alert("Error occurred");
            }
        });
    }

});
</script>
<?php require_once 'includes/footer.php'; ?>
