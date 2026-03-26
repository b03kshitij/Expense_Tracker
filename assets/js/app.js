$(document).ready(function() {

    // Toast notification
    function showToast(message, type) {
        var bg = type === 'success' ? '#22C55E' : '#EF4444';
        var toast = $('<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">' +
            '<div class="toast show align-items-center border-0 text-white" style="background:' + bg + ';border-radius:10px;">' +
            '<div class="d-flex"><div class="toast-body fw-medium">' +
            '<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' + message +
            '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div></div>');
        $('body').append(toast);
        setTimeout(function() { toast.fadeOut(300, function() { $(this).remove(); }); }, 3000);
    }

    // Add Expense (AJAX)
    $('#addExpenseForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: 'ajax/add_expense.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    $('#addExpenseModal').modal('hide');
                    form[0].reset();
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: function() { showToast('Something went wrong', 'error'); },
            complete: function() { btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Save'); }
        });
    });

    // Edit Expense - populate modal
    $(document).on('click', '.edit-expense', function() {
        var btn = $(this);
        $('#editId').val(btn.data('id'));
        $('#editAmount').val(btn.data('amount'));
        $('#editCategory').val(btn.data('category'));
        $('#editDate').val(btn.data('date'));
        $('#editDescription').val(btn.data('description'));
        $('#editExpenseModal').modal('show');
    });

    // Update Expense (AJAX)
    $('#editExpenseForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');

        $.ajax({
            url: 'ajax/update_expense.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    $('#editExpenseModal').modal('hide');
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: function() { showToast('Something went wrong', 'error'); },
            complete: function() { btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Update'); }
        });
    });

    // Delete Expense (AJAX)
    $(document).on('click', '.delete-expense', function() {
        var id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this expense?')) return;

        $.ajax({
            url: 'ajax/delete_expense.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    $('tr[data-id="' + id + '"]').fadeOut(300, function() { $(this).remove(); });
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: function() { showToast('Something went wrong', 'error'); }
        });
    });

    // Category Doughnut Chart (Dashboard)
    if (typeof categoryData !== 'undefined' && categoryData.length > 0 && document.getElementById('categoryChart')) {
        var colors = ['#22C55E','#3B82F6','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316','#6366F1','#64748B'];
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryData.map(function(c) { return c.category; }),
                datasets: [{
                    data: categoryData.map(function(c) { return parseFloat(c.total); }),
                    backgroundColor: colors.slice(0, categoryData.length),
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyleWidth: 10, font: { family: 'Inter', size: 12 } } }
                }
            }
        });
    }

    // Monthly Bar Chart (Reports)
    if (typeof monthlyTotals !== 'undefined' && document.getElementById('monthlyChart')) {
        var monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: monthNames,
                datasets: [{
                    label: 'Spending',
                    data: monthlyTotals,
                    backgroundColor: '#22C55E',
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: '#E2E8F0' }, ticks: { font: { family: 'JetBrains Mono', size: 11 }, callback: function(v) { return '₹' + v.toLocaleString(); } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 12 } } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(ctx) { return '₹' + ctx.parsed.y.toLocaleString('en-IN', {minimumFractionDigits:2}); } } }
                }
            }
        });
    }
});
