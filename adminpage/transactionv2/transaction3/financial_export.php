<?php
include '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['export'])) {
    $filename = $_POST['filename'];
    $exportType = $_POST['exportType'];
    $data = json_decode($_POST['data'], true);

    if ($exportType == 'PDF') {
        require('fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $header = array('Date', 'Cheque No.', 'DV No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT', 'EVAT', 'Net Amount');
        foreach ($header as $col) {
            $pdf->Cell(20, 7, $col, 1);
        }
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            foreach ($row as $col) {
                $pdf->Cell(20, 6, $col, 1);
            }
            $pdf->Ln();
        }
        $pdf->Output('D', $filename . '.pdf');
    } elseif ($exportType == 'CSV') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Date', 'Cheque No.', 'DV No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT', 'EVAT', 'Net Amount'));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    } elseif ($exportType == 'EXCEL') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $filename . '.xls');
        echo '<table border="1">';
        echo '<tr><th>Date</th><th>Cheque No.</th><th>DV No.</th><th>Fund</th><th>Payee</th><th>Particulars</th><th>Gross Amount</th><th>VAT</th><th>EVAT</th><th>Net Amount</th></tr>';
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . $cell . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }

    // Log the export in the export_history table
    $stmt = $conn->prepare("INSERT INTO export_history (filename, export_type, transaction_type) VALUES (?, ?, 'Financial')");
    $stmt->bind_param("ss", $filename, $exportType);
    $stmt->execute();
    $stmt->close();

    exit;
}
