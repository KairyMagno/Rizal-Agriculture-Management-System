<?php
require '../includes/db.php';
require '../includes/logo.php';
require_once '../vendor/tcpdf/tcpdf.php'; // Adjust the path to your TCPDF library

// Start PDF creation
$pdf = new TCPDF();
$pdf->SetCreator('YourCompany');
$pdf->SetAuthor('YourName');
$pdf->SetTitle('Reports');
$pdf->SetSubject('Data Export');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Fetch and format data for each table
$tables = [
    'Announcements Clicks' => "SELECT title, click_count FROM announcement_clicks ORDER BY click_count DESC",
    'News & Articles Clicks' => "SELECT title, click_count FROM articles WHERE click_count > 0 ORDER BY click_count DESC",
    'Multimedia Clicks' => "SELECT title, click_count FROM multimedia WHERE click_count > 0 ORDER BY click_count DESC",
    'Activity Logs' => "SELECT username, activity, timestamp FROM activity_log ORDER BY timestamp DESC",
    'User Status' => "SELECT username, status FROM users ORDER BY username ASC",
    'User Roles' => "SELECT username, email, role FROM users ORDER BY username ASC",
    'Categories' => "SELECT name FROM categories ORDER BY name ASC"
];

// Loop through each table and add to the PDF
foreach ($tables as $title => $query) {
    $result = mysqli_query($conn, $query);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);

    if (mysqli_num_rows($result) > 0) {
        $header = [];
        $header = mysqli_fetch_fields($result);
        $headerText = [];
        foreach ($header as $column) {
            $headerText[] = $column->name;
        }
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 10, implode(' | ', $headerText), 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 10);
        while ($row = mysqli_fetch_assoc($result)) {
            $rowText = [];
            foreach ($headerText as $colName) {
                $rowText[] = $row[$colName];
            }
            $pdf->Cell(0, 10, implode(' | ', $rowText), 0, 1, 'L');
        }
    } else {
        $pdf->Cell(0, 10, 'No data available.', 0, 1, 'L');
    }
    
    $pdf->Ln(5); // Add space between sections
}

// Output PDF
$pdf->Output('report.pdf', 'D');
exit;
?>
