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
  table = document.getElementById("dataTable");
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

function filterByDate() {
  var startDate, endDate, table, tr, td, i, txtValue;
  startDate = new Date(document.getElementById("startDate").value);
  endDate = new Date(document.getElementById("endDate").value);
  table = document.getElementById("dataTable");
  tr = table.getElementsByTagName("tr");
  for (i = 1; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[0];
    if (td) {
      txtValue = new Date(td.textContent || td.innerText);
      if (txtValue >= startDate && txtValue <= endDate) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }
  }
}

function calculateTotals() {
  var selectedRows = document.querySelectorAll("#dataTable tbody tr.selected");
  var totalGrossAmount = 0;
  var totalVAT3 = 0;
  var totalVAT5 = 0;
  var totalVAT12 = 0;
  var totalEVAT1 = 0;
  var totalEVAT2 = 0;
  var totalNetAmount = 0;

  selectedRows.forEach(function (row) {
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

  document.getElementById("totalGrossAmount").innerText =
    totalGrossAmount.toFixed(2);
  document.getElementById("totalVAT3").innerText = totalVAT3.toFixed(2);
  document.getElementById("totalVAT5").innerText = totalVAT5.toFixed(2);
  document.getElementById("totalVAT12").innerText = totalVAT12.toFixed(2);
  document.getElementById("totalEVAT1").innerText = totalEVAT1.toFixed(2);
  document.getElementById("totalEVAT2").innerText = totalEVAT2.toFixed(2);
  document.getElementById("totalNetAmount").innerText =
    totalNetAmount.toFixed(2);
}

function rowClicked(row) {
  row.classList.toggle("selected");
  calculateTotals();
}

function editSelectedRow() {
  var selectedRow = document.querySelector(".selected");
  if (selectedRow) {
    var cells = selectedRow.getElementsByTagName("td");
    document.getElementById("editId").value =
      selectedRow.getAttribute("data-id");
    document.getElementById("date").value = cells[0].innerText;
    document.getElementById("cheque_no").value = cells[1].innerText;
    document.getElementById("dv_no").value = cells[2].innerText;
    document.getElementById("fund").value = cells[3].innerText;
    document.getElementById("payee").value = cells[4].innerText;
    document.getElementById("particulars").value = cells[5].innerText;
    document.getElementById("gross_amount").value = cells[6].innerText;
    document.getElementById("vat").value = cells[7].innerText;
    document.getElementById("evat").value = cells[8].innerText;
    document.getElementById("net_amount").value = cells[9].innerText;
    showForm();
  } else {
    alert("Please select a row to edit.");
  }
}

function deleteSelectedRow() {
  var selectedRows = document.querySelectorAll(".selected");
  if (selectedRows.length > 0) {
    if (confirm("Are you sure you want to delete the selected records?")) {
      selectedRows.forEach(function (row) {
        var id = row.getAttribute("data-id");
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_record.php", true);
        xhr.setRequestHeader(
          "Content-Type",
          "application/x-www-form-urlencoded"
        );
        xhr.onreadystatechange = function () {
          if (xhr.readyState === 4 && xhr.status === 200) {
            row.remove();
          }
        };
        xhr.send("id=" + id);
      });
    }
  } else {
    alert("Please select rows to delete.");
  }
}

function selectAllRows() {
  var rows = document.querySelectorAll("#dataTable tbody tr");
  rows.forEach(function (row) {
    row.classList.add("selected");
  });
}

function clearSelection() {
  var selectedRows = document.querySelectorAll(".selected");
  selectedRows.forEach(function (row) {
    row.classList.remove("selected");
  });
}

function showForm() {
  document.getElementById("financialForm").classList.toggle("hidden");
  document.getElementById("vat").disabled = true;
  document.getElementById("evat").disabled = true;
}

function showExportForm(exportUrl) {
  var selectedRows = document.querySelectorAll("#dataTable tr.selected");
  if (selectedRows.length > 0) {
    var data = [];
    selectedRows.forEach(function (row) {
      var cells = row.getElementsByTagName("td");
      var rowData = [];
      for (var i = 0; i < cells.length; i++) {
        rowData.push(cells[i].innerText);
      }
      data.push(rowData);
    });
    document.getElementById("exportData").value = JSON.stringify(data);
    document.getElementById("exportForm").action = exportUrl;
    $("#exportModal").modal("show");
  } else {
    alert("Please select rows to export.");
  }
}

function calculateAmounts() {
  var grossAmount =
    parseFloat(document.getElementById("gross_amount").value) || 0;
  var vat = parseFloat(document.getElementById("vat").value) || 0;
  var evat = parseFloat(document.getElementById("evat").value) || 0;

  var vatAmount = grossAmount * (vat / 100);
  var evatAmount = grossAmount * (evat / 100);
  var netAmount = grossAmount - vatAmount - evatAmount;

  document.getElementById("net_amount").value = netAmount.toFixed(2);
}

function toggleVatFields() {
  var isVatable = document.getElementById("vatable").checked;
  document.getElementById("vat").disabled = !isVatable;
  document.getElementById("evat").disabled = !isVatable;
  if (!isVatable) {
    document.getElementById("vat").value = 0;
    document.getElementById("evat").value = 0;
    calculateAmounts();
  }
}

document.getElementById("vatable").addEventListener("change", toggleVatFields);
document
  .getElementById("gross_amount")
  .addEventListener("input", calculateAmounts);
document.getElementById("vat").addEventListener("change", calculateAmounts);
document.getElementById("evat").addEventListener("change", calculateAmounts);
