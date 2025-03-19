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
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'Office of the Barangay Captain', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, strtoupper($_POST['barangay_name'] ?? ''), 0, 1, 'C');
        $this->Cell(0, 5, ($_POST['barangay_address'] ?? '') . ', ' . ($_POST['barangay_city'] ?? ''), 0, 1, 'C');
        $this->Ln(0);

        // Report Title
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, 'SUMMARY OF FINANCIAL TRANSACTIONS', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'For the Month of ' . strtoupper($_POST['report_month'] ?? 'MONTH') . ' ' . ($_POST['report_year'] ?? 'YEAR'), 0, 1, 'C');
        $this->Ln(5);

        // Barangay Treasurer and Location Details
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(95, 5, 'Barangay Treasurer: ' . ($_POST['barangay_treasurer'] ?? 'N/A'), 0, 0, 'L');
        $this->Cell(95, 5, 'Province: ' . ($_POST['barangay_province'] ?? 'N/A'), 0, 1, 'R');

        $this->SetFont('Arial', '', 8);
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
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, 'Certification:', 0, 1, 'L');
        $this->MultiCell(0, 5, 'I hereby certify that the above information is correct. Check issued from ' . ($_POST['issued_from'] ?? 'N/A') . '.', 0, 'L');
        $this->Ln(2);

        // Signature Section
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(80, 5, '__________________________', 0, 1, 'L');
        $this->Cell(80, 5, strtoupper($_POST['barangay_encoder'] ?? 'N/A'), 0, 1, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(80, 5, 'Barangay Encoder', 0, 1, 'L');

        // Page Number
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 7);
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

            // Table Header
            $header = array('Date', 'Cheque No.', 'Voucher No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT', 'eVAT', 'VAT Amount', 'eVAT Amount', 'Net Amount');
            $widths = array(20, 20, 20, 25, 40, 25, 28, 10, 10, 25, 25, 25); // Adjusted widths
            $totalWidth = array_sum($widths);
            $pdf->SetX(($pdf->GetPageWidth() - $totalWidth) / 2); // Center the table

            foreach ($header as $key => $col) {
                $pdf->Cell($widths[$key], 7, $col, 1, 0, 'C');
            }
            $pdf->Ln();

            // Set font for table data
            $pdf->SetFont('Arial', '', 8);

            // Initialize total variables
            $totalGross = 0;
            $totalVAT = 0;
            $totaleVAT = 0;
            $totalNet = 0;

            // Table Data
            foreach ($data as $row) {
                $pdf->SetX(($pdf->GetPageWidth() - $totalWidth) / 2); // Center the table

                foreach ($row as $key => $col) {
                    if (array_key_exists($key, $widths)) { // Ensure key exists in width array
                        $pdf->Cell($widths[$key], 6, $col, 1, 0, 'C');
                    }
                }

                // Ensure numeric conversion to avoid string issues
                $grossAmount = isset($row[6]) ? floatval(str_replace(',', '', $row[6])) : 0;
                $vatAmount = isset($row[9]) ? floatval(str_replace(',', '', $row[9])) : 0;
                $eVatAmount = isset($row[10]) ? floatval(str_replace(',', '', $row[10])) : 0;
                $netAmount = isset($row[11]) ? floatval(str_replace(',', '', $row[11])) : 0;

                // Accumulate totalGross
                $totalGross += $grossAmount;
                $totalVAT += $vatAmount;
                $totaleVAT += $eVatAmount;
                $totalNet += $netAmount;

                $pdf->Ln();
            }

            // Display Totals below the table
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Ln(5); // Add some space before totals

            $pdf->SetX(($pdf->GetPageWidth() - $totalWidth) / 2); // Center totals row
            $pdf->Cell(150, 7, 'TOTAL:', 1, 0, 'R'); // Merging first few columns for total label
            $pdf->Cell(28, 7, number_format($totalGross, 2), 1, 0, 'C'); // Gross Amount
            $pdf->Cell(10, 7, '', 1, 0, 'C'); // VAT (Empty cell)
            $pdf->Cell(10, 7, '', 1, 0, 'C'); // eVAT (Empty cell)
            $pdf->Cell(25, 7, number_format($totalVAT, 2), 1, 0, 'C'); // VAT Amount
            $pdf->Cell(25, 7, number_format($totaleVAT, 2), 1, 0, 'C'); // eVAT Amount
            $pdf->Cell(25, 7, number_format($totalNet, 2), 1, 0, 'C'); // Net Amount

            // $pdf->Output('I', $filename . '.pdf');
            $exportPath = '../../../exports/pdf/' . $filename . '.pdf';
            $pdf->Output($exportPath, 'F');

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

        // Redirect back to the financial transaction page
        header("Location: ./financial_transactionv2.php");
        exit;
    }
}
