<?php
$pageTitle = 'Expenses';
require_once 'config/database.php';
require_once 'includes/header.php';
requireLogin();

$userId = $_SESSION['user_id'];
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Filters
$filterCategory = $_GET['category'] ?? '';
$filterFrom = $_GET['from'] ?? date('Y-m-01');
$filterTo = $_GET['to'] ?? date('Y-m-d');

$sql = "SELECT * FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?";
$params = [$userId, $filterFrom, $filterTo];
$types = "iss";

if ($filterCategory) {
    $sql .= " AND category = ?";
    $params[] = $filterCategory;
    $types .= "s";
}
$sql .= " ORDER BY expense_date DESC, created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$expenses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$total = array_sum(array_column($expenses, 'amount'));
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 fade-in-up">
        <h1 class="h3 fw-bold mb-1" style="letter-spacing:-0.02em;">Expenses</h1>
        <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="bi bi-plus-lg me-1"></i>Add Expense
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4 fade-in-up">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="<?php echo $filterFrom; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="<?php echo $filterTo; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $filterCategory === $cat['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-dark w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="d-flex justify-content-between align-items-center mb-3 fade-in-up">
        <span class="text-secondary" style="font-size:0.875rem;"><?php echo count($expenses); ?> expense(s) found</span>
        <span class="fw-semibold" style="font-family:'JetBrains Mono',monospace;">Total: ₹<?php echo number_format($total, 2); ?></span>
    </div>

    <!-- Table -->
    <div class="card fade-in-up">
        <div class="card-body p-0">
            <?php if (empty($expenses)): ?>
                <div class="text-center py-5 text-secondary">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                    <p class="mb-0">No expenses found for this period</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Date</th><th>Category</th><th>Description</th><th class="text-end">Amount</th><th class="text-center">Actions</th></tr>
                        </thead>
                        <tbody id="expensesList">
                            <?php foreach ($expenses as $exp): ?>
                            <tr data-id="<?php echo $exp['id']; ?>">
                                <td><?php echo date('M d, Y', strtotime($exp['expense_date'])); ?></td>
                                <td><span class="category-badge"><i class="bi bi-tag"></i><?php echo htmlspecialchars($exp['category']); ?></span></td>
                                <td><?php echo htmlspecialchars($exp['description'] ?: '-'); ?></td>
                                <td class="text-end amount-cell">₹<?php echo number_format($exp['amount'], 2); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link edit-expense p-1" data-id="<?php echo $exp['id']; ?>" data-amount="<?php echo $exp['amount']; ?>" data-category="<?php echo htmlspecialchars($exp['category']); ?>" data-date="<?php echo $exp['expense_date']; ?>" data-description="<?php echo htmlspecialchars($exp['description']); ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-link text-danger delete-expense p-1" data-id="<?php echo $exp['id']; ?>">
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
                    <!-- ✅ Category -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Category</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                + Add Category
                            </button>
                        </div>
                        <!--  Amount -->
                    <div class="mb-3">
                        <label class="form-label">Amount (₹)</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>

                            <select name="category" id="categoryDropdown" class="form-select mt-1" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="expense_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <small class="text-secondary">(optional)</small></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Lunch at cafe" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5>Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="text" id="newCategory" class="form-control" placeholder="Enter category name">
      </div>

      <div class="modal-footer">
        <button class="btn btn-success" onclick="addCategory()">Add</button>
      </div>

    </div>
  </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bi bi-pencil me-2" style="color:var(--accent);"></i>Edit Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editExpenseForm">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount (₹)</label>
                        <input type="number" name="amount" id="editAmount" class="form-control" step="0.01" min="0.01" required style="font-family:'JetBrains Mono',monospace;font-size:1.25rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" id="editCategory" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="expense_date" id="editDate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" id="editDescription" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addCategory() {
    let category = document.getElementById("newCategory").value;

    if (category.trim() === "") {
        alert("Enter category name");
        return;
    }

    fetch("add_category.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "category=" + encodeURIComponent(category)
    })
    .then(res => res.text())
    .then(data => {
        alert(data);

        // reload dropdown
        location.reload(); // simple way for now
    })
    .catch(err => {
        console.error(err);
        alert("Error adding category");
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
