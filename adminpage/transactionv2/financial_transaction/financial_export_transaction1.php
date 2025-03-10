<?php
include '../../../includes/db_connect.php';
require('../../../fpdf.php'); // Ensure the correct path to fpdf.php

class PDF extends FPDF
{
    function Header()
    {
        // Logo
        $this->Image('../../../uploads/logos/brgylogo-removebg-preview.png', 10, 10, 25);

        // Title Section
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, 'Office of the Barangay Captain', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 5, strtoupper($_POST['barangay_name'] ?? ''), 0, 1, 'C');
        $this->Cell(0, 5, ($_POST['barangay_address'] ?? '') . ', ' . ($_POST['barangay_city'] ?? ''), 0, 1, 'C');
        $this->Ln(8);

        // Report Title
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, 'SUMMARY OF FINANCIAL TRANSACTIONS', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'For the Month of ' . strtoupper($_POST['report_month'] ?? 'MONTH') . ' ' . ($_POST['report_year'] ?? 'YEAR'), 0, 1, 'C');
        $this->Ln(5);

        // Barangay Treasurer and Location Details
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(95, 5, 'Barangay Treasurer: ' . ($_POST['barangay_treasurer'] ?? 'N/A'), 0, 0, 'L');
        $this->Cell(95, 5, 'Province: ' . ($_POST['barangay_province'] ?? 'N/A'), 0, 1, 'R');

        $this->SetFont('Arial', '', 10);
        $this->Cell(95, 5, 'City / Municipality: ' . ($_POST['barangay_city'] ?? 'N/A'), 0, 0, 'L');
        $this->Cell(95, 5, 'Province No: ' . ($_POST['province_no'] ?? 'N/A'), 0, 1, 'R');
        $this->Cell(95, 5, 'SCKI No: ' . ($_POST['scki_no'] ?? 'N/A'), 0, 1, 'L');

        $this->Ln(5);
        $this->Cell(0, 0, '', 'T', 1, 'C');
        $this->Ln(2);
    }

    function Footer()
    {
        $this->SetY(-35);

        // Certification Statement
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Certification:', 0, 1, 'L');
        $this->MultiCell(0, 5, 'I hereby certify that the above information is correct. Check issued from ' . ($_POST['issued_from'] ?? 'N/A') . '.', 0, 'L');
        $this->Ln(8);

        // Signature Section
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(80, 5, '__________________________', 0, 1, 'L');
        $this->Cell(80, 5, strtoupper($_POST['barangay_encoder'] ?? 'N/A'), 0, 1, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(80, 5, 'Barangay Encoder', 0, 1, 'L');

        // Page Number
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['export'])) {
    $filename = $_POST['filename'];
    $exportType = $_POST['exportType'];
    $data = json_decode($_POST['data'], true);
    $transactionType = $_POST['transactionType'];

    if ($transactionType == 'TRANSACTION1') {
        if ($exportType == 'PDF') {
            $pdf = new PDF('L'); // Set orientation to landscape
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 8);
            $header = ['Date', 'Cheque No.', 'Voucher No.', 'Payee', 'Gross Amount'];
            $widths = [38, 38, 38, 38, 38];
            $totalWidth = array_sum($widths);
            $pdf->SetX(($pdf->GetPageWidth() - $totalWidth) / 2); // Center the table
            foreach ($header as $key => $col) {
                $pdf->Cell($widths[$key], 7, $col, 1, 0, 'C');
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 8);
            foreach ($data as $row) {
                $pdf->SetX(($pdf->GetPageWidth() - $totalWidth) / 2); // Center the table
                foreach ($row as $key => $col) {
                    if (in_array($key, [0, 1, 2, 4, 6])) { // Only include relevant columns
                        $pdf->Cell($widths[array_search($key, [0, 1, 2, 4, 6])], 6, $col, 1, 0, 'C');
                    }
                }
                $pdf->Ln();
            }
            $pdf->Output('D', $filename . '.pdf');
        } elseif ($exportType == 'CSV') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=' . $filename . '.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, array('Date', 'Cheque No.', 'Voucher No.', 'Payee', 'Gross Amount'));
            foreach ($data as $row) {
                fputcsv($output, array($row[0], $row[1], $row[2], $row[4], $row[6])); // Only include relevant columns
            }
            fclose($output);
        } elseif ($exportType == 'EXCEL') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=' . $filename . '.xls');
            echo '<table border="1">';
            echo '<tr><th>Date</th><th>Cheque No.</th><th>Voucher No.</th><th>Payee</th><th>Gross Amount</th></tr>';
            foreach ($data as $row) {
                echo '<tr>';
                echo '<td>' . $row[0] . '</td>';
                echo '<td>' . $row[1] . '</td>';
                echo '<td>' . $row[2] . '</td>';
                echo '<td>' . $row[4] . '</td>';
                echo '<td>' . $row[6] . '</td>'; // Only include relevant columns
                echo '</tr>';
            }
            echo '</table>';
        }

        // Log the export in the export_history table
        $stmt = $conn->prepare("INSERT INTO export_history (filename, export_type, transaction_type) VALUES (?, ?, 'TRANSACTION1')");
        $stmt->bind_param("ss", $filename, $exportType);
        $stmt->execute();
        $stmt->close();
    }

    exit;
}
