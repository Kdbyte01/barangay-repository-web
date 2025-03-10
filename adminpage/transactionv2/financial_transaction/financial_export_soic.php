<?php
include '../../../includes/db_connect.php';
require('../../../fpdf.php'); // Ensure the correct path to fpdf.php

function getDateRange($data)
{
    $dates = array_column($data, 0); // Assuming the date is in the first column
    $dates = array_map('strtotime', $dates);
    sort($dates);
    $startDate = date('F', reset($dates));
    $endDate = date('F', end($dates));
    $startYear = date('Y', reset($dates));
    $endYear = date('Y', end($dates));

    if ($startDate === $endDate && $startYear === $endYear) {
        return "For the Month of $startDate $startYear";
    } else {
        return "For the Months of $startDate - $endDate $startYear";
    }
}

// Fetch settings from the database
$sql = "SELECT setting_key, setting_value FROM settings";
$result = $conn->query($sql);
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

class PDF extends FPDF
{
    function Header()
    {
        global $settings, $dateRange;

        // Logo
        $this->Image('../../../uploads/logos/brgylogo-removebg-preview.png', 10, 10, 25);

        // Title Section
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, 'City/Municipality: ' . ($settings['barangay_city'] ?? 'N/A'), 0, 1, 'C');
        $this->Cell(0, 5, 'Province: ' . ($settings['barangay_province'] ?? 'N/A'), 0, 1, 'C');
        $this->Ln(8);

        // Report Title
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, 'SUMMARY OF CHECKS ISSUED', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, $dateRange, 0, 1, 'C');
        $this->Ln(5);

        // Barangay Treasurer and Location Details
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(95, 5, 'Barangay: ' . ($settings['barangay_name'] ?? 'N/A'), 0, 0, 'L');
        $this->Cell(95, 5, 'Province No: ' . ($settings['province_no'] ?? 'N/A'), 0, 1, 'R');

        $this->SetFont('Arial', '', 10);
        $this->Cell(95, 5, 'Barangay Treasurer: ' . ($settings['barangay_treasurer'] ?? 'N/A'), 0, 0, 'L');
        $this->Cell(95, 5, 'SCKI No: ' . ($settings['scki_no'] ?? 'N/A'), 0, 1, 'R');

        $this->Ln(5);
        $this->Cell(0, 0, '', 'T', 1, 'C');
        $this->Ln(2);
    }

    function Footer()
    {
        global $settings;

        $this->SetY(-60); // Adjusted Y position for footer

        // Certification and Acknowledgement Section
        $this->SetFont('Arial', '', 7);

        // Left half: Certification
        $this->Cell(95, 5, 'Certification:', 0, 0, 'L');
        $this->SetX(190); // Move to the right half
        $this->Cell(95, 5, 'Acknowledgement:', 0, 1, 'L');

        $this->MultiCell(95, 5, 'I hereby certify that the above information is correct. Check issued from_____________ The carbon copies of checks issued and, originals of all DVs/Payrolls are hereto attached.', 0, 'L');
        $this->SetXY(195, $this->GetY() - 15); // Move to the right half
        $this->MultiCell(95, 5, 'I hereby acknowledge receipt of the certified SCKI complete with carbon copies of all checks issued and originals of all DVs/Payrolls and supporting documents.', 0, 'L');
        $this->Ln(8);

        // Signature Section
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(95, 5, '__________________________', 0, 0, 'L');
        $this->SetX(195); // Move to the right half
        $this->Cell(95, 5, '__________________________', 0, 1, 'L');

        $this->Cell(95, 5, strtoupper($settings['barangay_encoder'] ?? 'N/A'), 0, 0, 'L');
        $this->SetX(195); // Move to the right half
        $this->Cell(95, 5, 'Joemarie M. Delgado', 0, 1, 'L');

        $this->SetFont('Arial', '', 7);
        $this->Cell(95, 5, 'Barangay Encoder', 0, 0, 'L');
        $this->SetX(195); // Move to the right half
        $this->Cell(95, 5, 'Barangay Treasurer', 0, 1, 'L');

        // Add current date
        $currentDate = $_POST['exportDateTime'];
        $this->SetY(-30); // Adjust Y position for date
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(95, 5, 'Date: ' . $currentDate, 0, 0, 'L');
        $this->SetX(195); // Move to the right half
        $this->Cell(95, 5, 'Date: ' . $currentDate, 0, 1, 'L');

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
    $exportDateTime = $_POST['exportDateTime']; // Get the export date and time

    // Calculate totals
    $totalGrossAmount = 0;
    $totalVAT3 = 0;
    $totalVAT5 = 0;
    $totalVAT12 = 0;
    $totalEVAT1 = 0;
    $totalEVAT2 = 0;
    $totalNetAmount = 0;

    foreach ($data as $row) {
        $grossAmount = floatval($row[6]);
        $vat3 = floatval($row[7]);
        $vat5 = floatval($row[8]);
        $vat12 = floatval($row[9]);
        $evat1 = floatval($row[10]);
        $evat2 = floatval($row[11]);
        $netAmount = floatval($row[12]);

        $totalGrossAmount += $grossAmount;
        $totalVAT3 += $vat3;
        $totalVAT5 += $vat5;
        $totalVAT12 += $vat12;
        $totalEVAT1 += $evat1;
        $totalEVAT2 += $evat2;
        $totalNetAmount += $netAmount;
    }

    $dateRange = getDateRange($data);

    if ($transactionType == 'SOIC') {
        if ($exportType == 'PDF') {
            $pdf = new PDF('L'); // Set orientation to landscape
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 8); // Reduced font size
            $header = array('Date', 'Cheque No.', 'Voucher No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT 3%', 'VAT 5%', 'VAT 12%', 'EVAT 1%', 'EVAT 2%', 'Net Amount');
            $widths = array(25, 25, 25, 25, 33, 33, 27, 13, 13, 13, 13, 13, 27); // Adjusted widths
            foreach ($header as $key => $col) {
                $pdf->Cell($widths[$key], 7, $col, 1, 0, 'C');
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 8); // Reduced font size
            foreach ($data as $row) {
                foreach ($row as $key => $col) {
                    $pdf->Cell($widths[$key], 6, $col, 1, 0, 'C');
                }
                $pdf->Ln();
            }
            // Add a clear row above the totals row
            $pdf->Cell(array_sum($widths), 6, '', 0, 1);
            // Add totals
            $pdf->Cell($widths[0], 6, '', 1);
            $pdf->Cell($widths[1], 6, '', 1);
            $pdf->Cell($widths[2], 6, '', 1);
            $pdf->Cell($widths[3], 6, '', 1);
            $pdf->Cell($widths[4], 6, '', 1);
            $pdf->Cell($widths[5], 6, 'Totals:', 1, 0, 'C');
            $pdf->Cell($widths[6], 6, '₱' . number_format($totalGrossAmount, 2), 1, 0, 'C');
            $pdf->Cell($widths[7], 6, '₱' . number_format($totalVAT3, 2), 1, 0, 'C');
            $pdf->Cell($widths[8], 6, '₱' . number_format($totalVAT5, 2), 1, 0, 'C');
            $pdf->Cell($widths[9], 6, '₱' . number_format($totalVAT12, 2), 1, 0, 'C');
            $pdf->Cell($widths[10], 6, '₱' . number_format($totalEVAT1, 2), 1, 0, 'C');
            $pdf->Cell($widths[11], 6, '₱' . number_format($totalEVAT2, 2), 1, 0, 'C');
            $pdf->Cell($widths[12], 6, '₱' . number_format($totalNetAmount, 2), 1, 0, 'C');
            $pdf->Output('D', $filename . '.pdf');
        } elseif ($exportType == 'CSV') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=' . $filename . '.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, array('Date', 'Cheque No.', 'Voucher No.', 'Fund', 'Payee', 'Particulars', 'Gross Amount', 'VAT 3%', 'VAT 5%', 'VAT 12%', 'EVAT 1%', 'EVAT 2%', 'Net Amount'));
            fputcsv($output, array($dateRange));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            // Add a clear row above the totals row
            fputcsv($output, array());
            // Add totals
            fputcsv($output, array('', '', '', '', '', 'Totals', '₱' . number_format($totalGrossAmount, 2), '₱' . number_format($totalVAT3, 2), '₱' . number_format($totalVAT5, 2), '₱' . number_format($totalVAT12, 2), '₱' . number_format($totalEVAT1, 2), '₱' . number_format($totalEVAT2, 2), '₱' . number_format($totalNetAmount, 2)));
            fclose($output);
        } elseif ($exportType == 'EXCEL') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=' . $filename . '.xls');
            echo '<table border="1">';
            echo '<tr><th>Date</th><th>Cheque No.</th><th>Voucher No.</th><th>Fund</th><th>Payee</th><th>Particulars</th><th>Gross Amount</th><th>VAT 3%</th><th>VAT 5%</th><th>VAT 12%</th><th>EVAT 1%</th><th>EVAT 2%</th><th>Net Amount</th></tr>';
            echo '<tr><td colspan="13">' . $dateRange . '</td></tr>';
            foreach ($data as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . $cell . '</td>';
                }
                echo '</tr>';
            }
            // Add a clear row above the totals row
            echo '<tr><td colspan="13"></td></tr>';
            // Add totals
            echo '<tr><td></td><td></td><td></td><td></td><td></td><td>Totals</td><td>₱' . number_format($totalGrossAmount, 2) . '</td><td>₱' . number_format($totalVAT3, 2) . '</td><td>₱' . number_format($totalVAT5, 2) . '</td><td>₱' . number_format($totalVAT12, 2) . '</td><td>₱' . number_format($totalEVAT1, 2) . '</td><td>₱' . number_format($totalEVAT2, 2) . '</td><td>₱' . number_format($totalNetAmount, 2) . '</td></tr>';
            echo '</table>';
        }

        // Log the export in the export_history table
        $stmt = $conn->prepare("INSERT INTO export_history (filename, export_type, transaction_type, export_date, file_path) VALUES (?, ?, 'SOIC', NOW(), ?)");
        $filePath = realpath("../../../exports") . "/$filename.$exportType";
        $stmt->bind_param("sss", $filename, $exportType, $filePath);
        $stmt->execute();
        $stmt->close();
    }

    exit;
}
