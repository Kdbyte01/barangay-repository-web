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
  switching = true;
  dir = "asc";
  while (switching) {
    switching = false;
    rows = table.rows;
    for (i = 1; i < rows.length - 1; i++) {
      shouldSwitch = false;
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      if (dir == "asc") {
        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
          shouldSwitch = true;
          break;
        }
      } else if (dir == "desc") {
        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
          shouldSwitch = true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      switchcount++;
    } else {
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
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
  var totalVAT3 = 0;
  var totalVAT5 = 0;
  var totalVAT12 = 0;
  var totalEVAT1 = 0;
  var totalEVAT2 = 0;
  var totalNetAmount = 0;

  selectedRows.forEach(function (row) {
    var rowData = [];
    for (var i = 0; i < row.cells.length; i++) {
      rowData.push(row.cells[i].innerText);
    }
    data.push(rowData);

    var grossAmount = parseFloat(row.cells[6].innerText) || 0;
    var vat3 = parseFloat(row.cells[7].innerText) || 0;
    var vat5 = parseFloat(row.cells[8].innerText) || 0;
    var vat12 = parseFloat(row.cells[9].innerText) || 0;
    var evat1 = parseFloat(row.cells[10].innerText) || 0;
    var evat2 = parseFloat(row.cells[11].innerText) || 0;
    var netAmount = parseFloat(row.cells[12].innerText) || 0;

    totalGrossAmount += grossAmount;
    totalVAT3 += vat3;
    totalVAT5 += vat5;
    totalVAT12 += vat12;
    totalEVAT1 += evat1;
    totalEVAT2 += evat2;
    totalNetAmount += netAmount;
  });

  document.getElementById("exportData").value = JSON.stringify(data);
  document.getElementById("transactionType").value = transactionType;

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

  // Add totals row to preview
  var totalsRow = document.createElement("tr");
  totalsRow.innerHTML = `
        <td colspan="6">Totals</td>
        <td>${totalGrossAmount.toFixed(2)}</td>
        <td>${totalVAT3.toFixed(2)}</td>
        <td>${totalVAT5.toFixed(2)}</td>
        <td>${totalVAT12.toFixed(2)}</td>
        <td>${totalEVAT1.toFixed(2)}</td>
        <td>${totalEVAT2.toFixed(2)}</td>
        <td>${totalNetAmount.toFixed(2)}</td>
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
  var selectedRows = document.querySelectorAll("tr.selected");
  if (selectedRows.length === 0) {
    alert("Please select rows to delete.");
    return;
  }
  if (confirm("Are you sure you want to delete the selected records?")) {
    var idsToDelete = [];
    selectedRows.forEach(function (row) {
      var id = row.getAttribute("data-id");
      idsToDelete.push(id);
    });

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "financial_delete.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        selectedRows.forEach(function (row) {
          row.remove();
        });
        alert("Selected records deleted successfully.");
      }
    };
    xhr.send("ids=" + JSON.stringify(idsToDelete));
  }
}

function filterByDate() {
  var startDate = document.getElementById("startDate").value;
  var endDate = document.getElementById("endDate").value;
  var table = document.getElementById("dataTable");
  var rows = table.getElementsByTagName("tr");
  for (var i = 1; i < rows.length; i++) {
    var date = rows[i].getElementsByTagName("td")[0].innerText;
    if (date >= startDate && date <= endDate) {
      rows[i].style.display = "";
    } else {
      rows[i].style.display = "none";
    }
  }
}

function filterTable() {
  var input, filter, table, tr, td, i, j, txtValue;
  input = document.getElementById("searchInput");
  filter = input.value.toLowerCase();
  table = document.getElementById("dataTable");
  tr = table.getElementsByTagName("tr");
  for (i = 1; i < tr.length; i++) {
    tr[i].style.display = "none";
    td = tr[i].getElementsByTagName("td");
    for (j = 0; j < td.length; j++) {
      if (td[j]) {
        txtValue = td[j].textContent || td[j].innerText;
        if (txtValue.toLowerCase().indexOf(filter) > -1) {
          tr[i].style.display = "";
          break;
        }
      }
    }
  }
}

function toggleVatEvatFields() {
  var vatable = document.getElementById("vatable").checked;
  var evatable = document.getElementById("evatable").checked;
  document.getElementById("vat").disabled = !vatable;
  document.getElementById("evat").disabled = !evatable;
}
$("#exportModal").on("show.bs.modal", function () {
  $(this).removeAttr("aria-hidden");
});

$("#exportModal").on("hide.bs.modal", function () {
  $(this).attr("aria-hidden", "true");
});
