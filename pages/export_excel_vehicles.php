<?php
require '../../../vendor/autoload.php';
include '../includes/connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$query = 'SELECT v.id, v.vehicle_license, b.BNAME, v.vehicle_model, v.vehicle_year, e.FIRST_NAME, e.LAST_NAME
        FROM vehicles v
        JOIN brand b ON v.vehicle_brand = b.BRAND_ID
        LEFT JOIN employee e ON v.vehicle_attendant = e.EMPLOYEE_ID
        GROUP BY v.id';
$result = mysqli_query($db, $query) or die(mysqli_error($db));


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();


$sheet->setCellValue('A1', 'Placa de Vehiculo');
$sheet->setCellValue('B1', 'Marca de Vehiculo');
$sheet->setCellValue('C1', 'Modelo de Vehiculo');
$sheet->setCellValue('D1', 'Año del Vehiculo');
$sheet->setCellValue('E1', 'Encargado del Vehiculo');


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
$sheet->getStyle('A1:E1')->applyFromArray($styleArray);


$rowCount = 2; 
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowCount, $row['vehicle_license']);
    $sheet->setCellValue('B' . $rowCount, $row['BNAME']);
    $sheet->setCellValue('C' . $rowCount, $row['vehicle_model']);
    $sheet->setCellValue('D' . $rowCount, $row['vehicle_year']);
    $attendant = ($row['FIRST_NAME'] && $row['LAST_NAME']) ? $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'] : 'Not assigned';
    $sheet->setCellValue('E' . $rowCount, $attendant);
    $rowCount++;
}


$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(25);


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Reporte Vehiculos.xlsx"');
header('Cache-Control: max-age=0');


$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
