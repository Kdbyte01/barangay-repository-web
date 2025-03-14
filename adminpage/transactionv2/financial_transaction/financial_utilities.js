function calculateTotals() {
  var selectedRows = document.querySelectorAll("#dataTable tbody tr.selected");
  var totalGrossAmount = 0;
  var totalVAT = 0;
  var totalEVAT = 0;
  var totalNetAmount = 0;

  selectedRows.forEach(function (row) {
    var grossAmount = parseFloat(row.cells[6].innerText) || 0;
    var vat = parseFloat(row.cells[7].innerText) || 0;
    var evat = parseFloat(row.cells[8].innerText) || 0;
    var netAmount = parseFloat(row.cells[9].innerText) || 0;

    totalGrossAmount += grossAmount;
    totalVAT += grossAmount * (vat / 100);
    totalEVAT += grossAmount * (evat / 100);
    totalNetAmount += netAmount;
  });

  document.getElementById("totalGrossAmount").innerText =
    "₱" + totalGrossAmount.toFixed(2);
  document.getElementById("totalVAT").innerText = "₱" + totalVAT.toFixed(2);
  document.getElementById("totalEVAT").innerText = "₱" + totalEVAT.toFixed(2);
  document.getElementById("totalNetAmount").innerText =
    "₱" + totalNetAmount.toFixed(2);

  // Update selected rows count
  document.getElementById("selectedRows").innerText = selectedRows.length;
}

function rowClicked(row) {
  row.classList.toggle("selected");
  calculateTotals();
}

function selectAllRows() {
  var rows = document.querySelectorAll("#dataTable tbody tr");
  rows.forEach(function (row) {
    if (row.style.display !== "none") {
      row.classList.add("selected");
    }
  });
  calculateTotals();
}

function clearAllRows() {
  var rows = document.querySelectorAll("#dataTable tbody tr");
  rows.forEach(function (row) {
    row.classList.remove("selected");
  });
  calculateTotals();
}

function updateTotalRowsCount() {
  var rows = document.querySelectorAll("#dataTable tbody tr");
  document.getElementById("totalRows").innerText = rows.length;
}

// Call this function after the table is populated
updateTotalRowsCount();

function showForm() {
  document.getElementById("financialForm").classList.remove("hidden");
}

function showExportForm(transactionType) {
  document.getElementById("transactionType").value = transactionType;
  $("#exportModal").modal("show");
}

function editSelectedRow() {
  var selectedRows = document.querySelectorAll("#dataTable tbody tr.selected");
  if (selectedRows.length !== 1) {
    alert("Please select exactly one row to edit.");
    return;
  }
  var row = selectedRows[0];
  document.getElementById("editId").value = row.getAttribute("data-id");
  document.getElementById("date").value = row.cells[0].innerText;
  document.getElementById("chequeNumber").value = row.cells[1].innerText;
  document.getElementById("voucherNo").value = row.cells[2].innerText;
  document.getElementById("fund").value = row.cells[3].innerText;
  document.getElementById("payee").value = row.cells[4].innerText;
  document.getElementById("particulars").value = row.cells[5].innerText;
  document.getElementById("grossAmount").value = row.cells[6].innerText;
  document.getElementById("vat").value = row.cells[7].innerText;
  document.getElementById("evat").value = row.cells[8].innerText;
  document.getElementById("netAmount").value = row.cells[9].innerText;
  showForm();
}

function deleteSelectedRow() {
  var selectedRows = document.querySelectorAll("#dataTable tbody tr.selected");
  if (selectedRows.length === 0) {
    alert("Please select at least one row to delete.");
    return;
  }
  var ids = Array.from(selectedRows).map((row) => row.getAttribute("data-id"));
  if (confirm("Are you sure you want to delete the selected rows?")) {
    $.ajax({
      url: "financial_delete.php",
      type: "POST",
      data: { ids: JSON.stringify(ids) },
      success: function (response) {
        alert(response);
        location.reload();
      },
    });
  }
}
