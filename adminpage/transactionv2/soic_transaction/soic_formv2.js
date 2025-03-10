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

function rowClicked(row) {
  row.classList.toggle("selected");
}

function showForm() {
  document.getElementById("soicForm").classList.toggle("hidden");
  document.getElementById("editId").value = "";
  document.getElementById("soicForm").reset();
}

function showExportForm() {
  var selectedRows = document.querySelectorAll("tr.selected");
  if (selectedRows.length === 0) {
    alert("Please select data in the table");
    return;
  }

  var data = [];
  selectedRows.forEach(function (row) {
    var rowData = [];
    row.querySelectorAll("td").forEach(function (cell) {
      rowData.push(cell.innerText);
    });
    data.push(rowData);
  });

  document.getElementById("exportData").value = JSON.stringify(data);
  var previewBody = document.getElementById("previewBody");
  previewBody.innerHTML = "";
  data.forEach(function (row) {
    var rowHtml = "<tr>";
    row.forEach(function (cell) {
      rowHtml += "<td>" + cell + "</td>";
    });
    rowHtml += "</tr>";
    previewBody.innerHTML += rowHtml;
  });

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
  document.getElementById("checkNumber").value = cells[1].innerText;
  document.getElementById("voucherNo").value = cells[2].innerText;
  document.getElementById("fund").value = cells[3].innerText;
  document.getElementById("payee").value = cells[4].innerText;
  document.getElementById("particulars").value = cells[5].innerText;
  document.getElementById("grossAmount").value = cells[6].innerText;
  document.getElementById("vat").value = cells[7].innerText.replace("%", ""); // Remove the '%' character
  document.getElementById("soicForm").classList.remove("hidden");
}

function deleteSelectedRow() {
  var selectedRows = document.querySelectorAll("tr.selected");
  if (selectedRows.length === 0) {
    alert("Please select rows to delete.");
    return;
  }
  if (confirm("Are you sure you want to delete the selected records?")) {
    var idsToDelete = [];
    selectedRows.forEach(function (row) {
      var id = row.getAttribute("data-id"); // Assuming each row has a data-id attribute with the record ID
      idsToDelete.push(id);
    });

    // AJAX call to delete records from the database
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "soic_delete.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        // Remove rows from the table after successful deletion
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

$("#exportModal").on("show.bs.modal", function () {
  $(this).removeAttr("aria-hidden");
});

$("#exportModal").on("hide.bs.modal", function () {
  $(this).attr("aria-hidden", "true");
});
