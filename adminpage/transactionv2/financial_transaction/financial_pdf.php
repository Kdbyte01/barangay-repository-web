<?php
require('../../../fpdf.php'); // Ensure the path to fpdf.php is correct

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
        $this->Ln(5);

        // Report Title
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, 'SUMMARY OF FINANCIAL TRANSACTIONS', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'For the Month of ' . strtoupper($_POST['report_month'] ?? 'MONTH') . ' ' . ($_POST['report_year'] ?? 'YEAR'), 0, 1, 'C');
        $this->Ln(5);

        // Barangay Treasurer Details
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'Barangay Treasurer: ' . ($_POST['barangay_treasurer'] ?? 'N/A'), 0, 1, 'L');

        // Other Details
        $this->SetFont('Arial', '', 10);
        $this->Cell(95, 5, 'City / Municipality: ' . ($_POST['barangay_city'] ?? 'N/A'), 0, 0, 'L');
        $this->Cell(95, 5, 'Province: ' . ($_POST['barangay_province'] ?? 'N/A'), 0, 1, 'R');
        $this->Cell(95, 5, 'SCKI No: ' . ($_POST['scki_no'] ?? 'N/A'), 0, 0, 'L');
        $this->Cell(95, 5, 'Province No: ' . ($_POST['province_no'] ?? 'N/A'), 0, 1, 'R');
        $this->Ln(5);

        // Line Separator
        $this->Cell(0, 0, '', 'T', 1, 'C');
        $this->Ln(2);
    }

    function Footer()
    {
        $this->SetY(-40);

        // Certification Statement
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Certification:', 0, 1, 'L');
        $this->Cell(0, 5, 'I hereby certify ..... check issued from ' . ($_POST['issued_from'] ?? 'N/A') . '.', 0, 1, 'L');
        $this->Ln(10);

        // Signature Line
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
