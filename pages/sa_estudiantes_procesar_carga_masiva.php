<?php
require('../includes/connection.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $allowedExtensions = ['csv'];
    $filename = $_FILES["file"]["name"];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExtensions)) {
        echo json_encode([
            'success' => false,
            'message' => "Tipo de archivo no permitido. Por favor, suba un archivo CSV."
        ]);
        exit;
    }

    $inputFileName = $_FILES["file"]["tmp_name"];
    
    if (($handle = fopen($inputFileName, "r")) !== FALSE) {
        // Saltar la primera línea si contiene encabezados
        fgetcsv($handle, 1000, ";");

        $cantidad_regist_agregados = 0;
        $error_rows = [];

        while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
            // Asegúrate de que tienes suficientes campos
            if (count($row) < 21) {
                $error_rows[] = $row;
                continue;
            }

            $nombres_estudiante = !empty($row[0]) ? mysqli_real_escape_string($db, $row[0]) : '';
            $apellidos_estudiante = !empty($row[1]) ? mysqli_real_escape_string($db, $row[1]) : '';
            $direccion_estudiante = !empty($row[2]) ? mysqli_real_escape_string($db, $row[2]) : '';
            $correo_estudiante = !empty($row[3]) ? mysqli_real_escape_string($db, $row[3]) : '';
            $fecha_nac_estudiante = !empty($row[4]) ? mysqli_real_escape_string($db, $row[4]) : '';
            $edad_estudiante = !empty($row[5]) ? mysqli_real_escape_string($db, $row[5]) : '';
            $genero_estudiante = !empty($row[6]) ? mysqli_real_escape_string($db, $row[6]) : '';
            $grado_id_estudiante = !empty($row[7]) ? mysqli_real_escape_string($db, $row[7]) : '';
            $carnet_estudiante = !empty($row[8]) ? mysqli_real_escape_string($db, $row[8]) : '';
            $numero_lista_estudiante = !empty($row[9]) ? mysqli_real_escape_string($db, $row[9]) : '';
            $info_medica_estudiante = !empty($row[10]) ? mysqli_real_escape_string($db, $row[10]) : '';
            $fecha_reg_estudiante = date('d-m-Y');
            $nombres_encargado = !empty($row[11]) ? mysqli_real_escape_string($db, $row[11]) : '';
            $apellidos_encargado = !empty($row[12]) ? mysqli_real_escape_string($db, $row[12]) : '';
            $dui_encargado = !empty($row[13]) ? mysqli_real_escape_string($db, $row[13]) : '';
            $direccion_encargado = !empty($row[14]) ? mysqli_real_escape_string($db, $row[14]) : '';
            $correo_encargado = !empty($row[15]) ? mysqli_real_escape_string($db, $row[15]) : '';
            $trabajo_encargado = !empty($row[16]) ? mysqli_real_escape_string($db, $row[16]) : '';
            $numero_cel_encargado = !empty($row[17]) ? mysqli_real_escape_string($db, $row[17]) : '';
            $numero_tel_encargado = !empty($row[18]) ? mysqli_real_escape_string($db, $row[18]) : '';
            $genero_encargado = !empty($row[19]) ? mysqli_real_escape_string($db, $row[19]) : '';
            $fecha_nac_encargado = !empty($row[20]) ? mysqli_real_escape_string($db, $row[20]) : '';

            // Verificar si el registro ya existe
            $check_query = "SELECT * FROM estudiantes WHERE 
                nombres_estudiante = '$nombres_estudiante' AND
                apellidos_estudiante = '$apellidos_estudiante' AND
                carnet_estudiante = '$carnet_estudiante'";
            $result = mysqli_query($db, $check_query);

            if (mysqli_num_rows($result) == 0) {
                $insertar = "INSERT INTO estudiantes (
                    nombres_estudiante, apellidos_estudiante, direccion_estudiante, correo_estudiante,
                    fecha_nac_estudiante, edad_estudiante, genero_estudiante, grado_id_estudiante,
                    carnet_estudiante, numero_lista_estudiante, info_medica_estudiante, fecha_reg_estudiante,
                    nombres_encargado, apellidos_encargado, dui_encargado, direccion_encargado,
                    correo_encargado, trabajo_encargado, numero_cel_encargado, numero_tel_encargado,
                    genero_encargado, fecha_nac_encargado
                ) VALUES (
                    '$nombres_estudiante', '$apellidos_estudiante', '$direccion_estudiante', '$correo_estudiante',
                    '$fecha_nac_estudiante', '$edad_estudiante', '$genero_estudiante', '$grado_id_estudiante',
                    '$carnet_estudiante', '$numero_lista_estudiante', '$info_medica_estudiante', '$fecha_reg_estudiante',
                    '$nombres_encargado', '$apellidos_encargado', '$dui_encargado', '$direccion_encargado',
                    '$correo_encargado', '$trabajo_encargado', '$numero_cel_encargado', '$numero_tel_encargado',
                    '$genero_encargado', '$fecha_nac_encargado'
                )";
                
                if (mysqli_query($db, $insertar)) {
                    $cantidad_regist_agregados++;
                }
            } else {
                $error_rows[] = $row;
            }
        }
        fclose($handle);

        if ($cantidad_regist_agregados > 0) {
            echo json_encode([
                'success' => true,
                'message' => $cantidad_regist_agregados . " estudiante ha sido agregado."
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "No se han agregado registros. Por favor, verifica el archivo CSV."
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => "No se pudo abrir el archivo."
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => "No se ha subido ningún archivo."
    ]);
}
?>
