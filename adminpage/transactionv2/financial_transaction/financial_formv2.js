function sortTable(n) {
  var table,
    rows,
    switching,
    i,
    x,
    y,
    shouldSwitch,
    dir,
    switchcount = 0;
  table = document.querySelector("table");
  while (switching) {
    // Sorting logic...
  }
}

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
    totalGrossAmount.toFixed(2);
  document.getElementById("totalVAT").innerText = totalVAT.toFixed(2);
  document.getElementById("totalEVAT").innerText = totalEVAT.toFixed(2);
  document.getElementById("totalNetAmount").innerText =
    totalNetAmount.toFixed(2);

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
  document.getElementById("financialForm").classList.toggle("hidden");
  document.getElementById("editId").value = "";
  document.getElementById("financialForm").reset();
}

function showExportForm(transactionType) {
  var selectedRows = document.querySelectorAll("tr.selected");
  if (selectedRows.length === 0) {
    alert("Please select at least one row to export.");
    return;
  }

  var data = [];
  var totalGrossAmount = 0;
  var totalVAT = 0;
  var totalEVAT = 0;
  var totalNetAmount = 0;

  selectedRows.forEach(function (row) {
    var rowData = [];
    for (var i = 0; i < row.cells.length; i++) {
      rowData.push(row.cells[i].innerText);
    }
    data.push(rowData);

    var grossAmount = parseFloat(row.cells[6].innerText) || 0;
    var vat = parseFloat(row.cells[7].innerText) || 0;
    var evat = parseFloat(row.cells[8].innerText) || 0;
    var netAmount = parseFloat(row.cells[9].innerText) || 0;

    totalGrossAmount += grossAmount;
    totalVAT += grossAmount * (vat / 100);
    totalEVAT += grossAmount * (evat / 100);
    totalNetAmount += netAmount;
  });

  document.getElementById("exportData").value = JSON.stringify(data);
  document.getElementById("transactionType").value = transactionType;

  var currentDateTime = new Date();
  var formattedDate =
    currentDateTime.getMonth() +
    1 +
    "/" +
    currentDateTime.getDate() +
    "/" +
    currentDateTime.getFullYear();
  document.getElementById("exportDateTime").value = formattedDate;

  var previewBody = document.getElementById("previewBody");
  previewBody.innerHTML = "";

  data.forEach(function (row) {
    var tr = document.createElement("tr");
    row.forEach(function (cell) {
      var td = document.createElement("td");
      td.innerText = cell;
      tr.appendChild(td);
    });
    previewBody.appendChild(tr);
  });

  var totalsRow = document.createElement("tr");
  totalsRow.innerHTML = `
        <td colspan="6">Totals</td>
        <td>₱${totalGrossAmount.toFixed(2)}</td>
        <td>₱${totalVAT.toFixed(2)}</td>
        <td>₱${totalEVAT.toFixed(2)}</td>
        <td>₱${totalNetAmount.toFixed(2)}</td>
    `;
  previewBody.appendChild(totalsRow);

  if (transactionType === "SOIC") {
    document.getElementById("exportForm").action = "financial_export_soic.php";
  } else {
    document.getElementById("exportForm").action = "";
  }

  $("#exportModal").modal("show");
}

function editSelectedRow() {
  var selectedRow = document.querySelector("tr.selected");
  if (!selectedRow) {
    alert("Please select a row to edit.");
    return;
  }
  var cells = selectedRow.getElementsByTagName("td");
  document.getElementById("editId").value = selectedRow.getAttribute("data-id");
  document.getElementById("date").value = cells[0].innerText;
  document.getElementById("chequeNumber").value = cells[1].innerText;
  document.getElementById("voucherNo").value = cells[2].innerText;
  document.getElementById("fund").value = cells[3].innerText;
  document.getElementById("payee").value = cells[4].innerText;
  document.getElementById("particulars").value = cells[5].innerText;
  document.getElementById("grossAmount").value = cells[6].innerText;
  document.getElementById("vat").value = cells[7].innerText.replace("%", ""); // Remove the '%' character
  document.getElementById("evat").value = cells[8].innerText.replace("%", ""); // Remove the '%' character
  document.getElementById("financialForm").classList.remove("hidden");
}

document
  .getElementById("financialForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        alert("Record saved successfully.");
        location.reload();
      }
    };
    xhr.send(formData);
  });

function deleteSelectedRow() {
  var selectedRow = document.querySelector("tr.selected");
  if (!selectedRow) {
    alert("Please select a row to delete.");
    return;
  }
  var id = selectedRow.getAttribute("data-id");
  if (confirm("Are you sure you want to delete this record?")) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "financial_delete.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        alert("Record deleted successfully.");
        location.reload();
      }
    };
    xhr.send("id=" + id);
  }
}

function filterByDate() {
  var startDate = document.getElementById("startDate").value;
  var endDate = document.getElementById("endDate").value;
  var rows = document.querySelectorAll("#dataTable tbody tr");
  rows.forEach(function (row) {
    var date = row.cells[0].innerText;
    if (date >= startDate && date <= endDate) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
  updateTotalRowsCount();
}

function filterTable() {
  var input = document.getElementById("searchInput");
  var filter = input.value.toLowerCase();
  var rows = document.querySelectorAll("#dataTable tbody tr");
  rows.forEach(function (row) {
    var cells = row.getElementsByTagName("td");
    var match = false;
    for (var i = 0; i < cells.length; i++) {
      if (cells[i].innerText.toLowerCase().indexOf(filter) > -1) {
        match = true;
        break;
      }
    }
    if (match) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
  updateTotalRowsCount();
}

function toggleVatEvatFields() {
  var vatable = document.getElementById("vatable").checked;
  var evatable = document.getElementById("evatable").checked;
  document.getElementById("vat").disabled = !vatable;
  document.getElementById("evat").disabled = !evatable;
}

$("#exportModal").on("show.bs.modal", function () {
  var selectedRows = document.querySelectorAll("tr.selected");
  if (selectedRows.length === 0) {
    alert("Please select at least one row to export.");
    return false;
  }
});

$("#exportModal").on("hide.bs.modal", function () {
  document.getElementById("exportForm").reset();
});
