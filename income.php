<?php
$pageTitle = 'Income';
require_once 'config/database.php';
require_once 'includes/header.php';
requireLogin();

$userId = $_SESSION['user_id'];

// Fetch income
$stmt = $conn->prepare("SELECT * FROM income WHERE user_id = ? ORDER BY income_date DESC, created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$incomeList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Income</h2>

        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
            <i class="bi bi-plus-lg me-1"></i> Add Income
        </button>
    </div>

    <!-- Income List -->
    <div class="card">
        <div class="card-body p-0">

            <?php if (empty($incomeList)): ?>
                <div class="text-center py-5 text-secondary">
                    <p>No income added yet</p>
                </div>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($incomeList as $inc): ?>
                            <tr>
                                <td><?php echo date('M d', strtotime($inc['income_date'])); ?></td>
                                <td><?php echo htmlspecialchars($inc['source']); ?></td>
                                <td><?php echo htmlspecialchars($inc['description'] ?: '-'); ?></td>
                                <td class="text-end text-success fw-semibold">
                                    ₹<?php echo number_format($inc['amount'], 2); ?>
                                </td>
                                <td></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>

</div>

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Income</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="addIncomeForm">
                <div class="modal-body">

                    <div class="mb-3">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Source</label>
                        <input type="text" name="source" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" name="income_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Income</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!--
<script>
document.addEventListener("DOMContentLoaded", function () {
    console.log("JS LOADED");

    const form = document.getElementById("addIncomeForm");

    if (!form) {
        console.log("FORM NOT FOUND");
        return;
    }

    console.log("FORM FOUND");

    form.addEventListener("submit", async function(e) {
        e.preventDefault();
        console.log("FORM SUBMITTED");

        const formData = new FormData(this);

        try {
            let res = await fetch("ajax/add_income.php", {
                method: "POST",
                body: formData
            });

            let data = await res.json();
            console.log(data);

            if (data.success) {
                alert("Income added successfully");
                location.reload();
            } else {
                alert(data.error || "Failed");
            }
        } catch (err) {
            console.log(err);
            alert("Error occurred");
        }
    });
});
</script> -->



<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("addIncomeForm");

    if (!form) return;

    form.addEventListener("submit", async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            let res = await fetch("ajax/add_income.php", {
                method: "POST",
                body: formData
            });

            let data = await res.json();

            if (data.success) {
                // ✅ Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addIncomeModal'));
                modal.hide();

                // ✅ Reset form
                form.reset();

                // ✅ Reload page (simple way)
                location.reload();
            } else {
                console.log(data.error || "Failed");
            }
        } catch (err) {
            console.log(err);
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
<!--<script src="assets/js/main.js"></script>-->