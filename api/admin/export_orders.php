<?php
/**
 * UniMerch Admin API — Export Orders to PDF
 * GET /api/admin/export_orders.php
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/fpdf/fpdf.php';

// Ensure only merchants can export
requireMerchantAuth();

class OrderPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(30, 64, 175); // UniMerch Primary
        $this->Cell(0, 10, 'UNIMERCH - TRANSACTION REPORT', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(100);
        $this->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
        $this->Ln(5);
        
        // Table Header
        $this->SetFillColor(30, 64, 175);
        $this->SetTextColor(255);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(40, 10, 'Order #', 1, 0, 'C', true);
        $this->Cell(60, 10, 'Customer', 1, 0, 'C', true);
        $this->Cell(35, 10, 'Total', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Status', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Date', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdo = db();
$stmt = $pdo->query("SELECT order_number, customer_name, total_amount, status, created_at FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll();

$pdf = new OrderPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);

foreach ($orders as $order) {
    $pdf->Cell(40, 10, $order['order_number'], 1);
    $pdf->Cell(60, 10, substr($order['customer_name'], 0, 25), 1);
    $pdf->Cell(35, 10, 'P' . number_format($order['total_amount'], 2), 1, 0, 'R');
    $pdf->Cell(25, 10, ucfirst($order['status']), 1, 0, 'C');
    $pdf->Cell(30, 10, date('Y-m-d', strtotime($order['created_at'])), 1, 1, 'C');
}

$filename = "UniMerch_Report_" . date('Ymd_His') . ".pdf";
$pdf->Output('D', $filename);
exit;
