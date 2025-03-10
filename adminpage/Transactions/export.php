<?php
// filepath: /C:/xampp/htdocs/BarangayWeb/adminpage/Transactions/export.php
include '../../includes/db_connect.php';

$search_query = "SELECT * FROM financial_transactions WHERE 1=1";

if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $search_query .= " AND date BETWEEN '$start_date' AND '$end_date'";
}
if (!empty($_POST['search_payee'])) {
    $search_payee = $_POST['search_payee'];
    $search_query .= " AND payee LIKE '%$search_payee%'";
}

$result = $conn->query($search_query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$export_type = $_POST['export_type'];
$file_name = $_POST['file_name'];

// Insert record into exported_files table
$insert_sql = "INSERT INTO exported_files (file_name, export_type) VALUES ('$file_name', '$export_type')";
$conn->query($insert_sql);

if ($export_type == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=$file_name.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "Date\tCheque No.\tDV No.\tFund\tPayee\tParticulars\tGross Amount\tVAT\tEVAT\tNet Amount\n";

    while ($row = $result->fetch_assoc()) {
        echo $row['date'] . "\t" . $row['cheque_no'] . "\t" . $row['dv_no'] . "\t" . $row['fund'] . "\t" . $row['payee'] . "\t" . $row['particulars'] . "\t" . $row['gross_amount'] . "\t" . $row['vat'] . "\t" . $row['evat'] . "\t" . $row['net_amount'] . "\n";
    }
} elseif ($export_type == 'csv') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$file_name.csv");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "Date,Cheque No.,DV No.,Fund,Payee,Particulars,Gross Amount,VAT,EVAT,Net Amount\n";

    while ($row = $result->fetch_assoc()) {
        echo $row['date'] . "," . $row['cheque_no'] . "," . $row['dv_no'] . "," . $row['fund'] . "," . $row['payee'] . "," . $row['particulars'] . "," . $row['gross_amount'] . "," . $row['vat'] . "," . $row['evat'] . "," . $row['net_amount'] . "\n";
    }
} elseif ($export_type == 'pdf') {
    require('../../fpdf.php');

    class PDF extends FPDF
    {
        function Header()
        {
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 10, 'Financial Transactions', 0, 1, 'C');
            $this->Ln(10);
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }

        function Table($header, $data)
        {
            $this->SetFont('Arial', 'B', 12);
            $widths = array(30, 30, 30, 20, 40, 50, 30, 20, 20, 30); // Adjusted widths
            foreach ($header as $i => $col) {
                $this->Cell($widths[$i], 10, $col, 1);
            }
            $this->Ln();
            $this->SetFont('Arial', '', 12);
            foreach ($data as $row) {
                foreach ($row as $i => $col) {
                    $this->Cell($widths[$i], 10, $col, 1);
                }
                $this->Ln();
            }
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $header = array('Date', 'Cheque No.', 'DV No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT', 'EVAT', 'Net Amount');
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = array($row['date'], $row['cheque_no'], $row['dv_no'], $row['fund'], $row['payee'], $row['particulars'], $row['gross_amount'], $row['vat'], $row['evat'], $row['net_amount']);
    }
    $pdf->Table($header, $data);
    $pdf->Output('D', "$file_name.pdf");
}

$conn->close();
