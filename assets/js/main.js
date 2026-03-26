document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("addIncomeForm");

    if (!form) return;

    form.addEventListener("submit", async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        let res = await fetch("ajax/add_income.php", {
            method: "POST",
            body: formData
        });

        let data = await res.json();

        if (data.success) {
            location.reload();
        } else {
            alert("Failed");
        }
    });

});