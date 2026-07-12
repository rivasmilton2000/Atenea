<?php
require '../../../vendor/autoload.php';
include '../includes/connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$query = 'SELECT PRODUCT_CODE, NAME, COUNT(`QTY_STOCK`) AS "QTY_STOCK", COUNT(`ON_HAND`) AS "ON_HAND", CNAME, DATE_STOCK_IN
            FROM product p
            JOIN category c ON p.CATEGORY_ID=c.CATEGORY_ID
            GROUP BY PRODUCT_CODE';
$result = mysqli_query($db, $query) or die(mysqli_error($db));

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Código');
$sheet->setCellValue('B1', 'Nombre');
$sheet->setCellValue('C1', 'Cantidad');
$sheet->setCellValue('D1', 'Disponible');
$sheet->setCellValue('E1', 'Categoría');
$sheet->setCellValue('F1', 'Fecha de Entrada');

// Estilos para la primera fila (encabezados)
$styleArray = [
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => '98FB98', 
        ],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
];
$sheet->getStyle('A1:F1')->applyFromArray($styleArray);

$rowCount = 2; // Empezamos en la fila 2
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowCount, $row['PRODUCT_CODE']);
    $sheet->setCellValue('B' . $rowCount, $row['NAME']);
    $sheet->setCellValue('C' . $rowCount, $row['QTY_STOCK']);
    $sheet->setCellValue('D' . $rowCount, $row['ON_HAND']);
    $sheet->setCellValue('E' . $rowCount, $row['CNAME']);
    $sheet->setCellValue('F' . $rowCount, $row['DATE_STOCK_IN']);
    $rowCount++;
}

// Ajustar el ancho de las columnas
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(25);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(20);

// Configuración del encabezado para la descarga del archivo Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="inventario.xlsx"');
header('Cache-Control: max-age=0');

// Guardar el archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
