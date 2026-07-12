<?php
include '../includes/connection.php';

$estudiante_id = $_POST['estudianteId'];
$doc_asi_id = $_POST['asignaturaId'];

// Consulta para obtener el ID del período
$query_periodo = "SELECT p.p_id
                  FROM periodo p
                  WHERE p.p_name = '" . $_POST['periodo'] . "'";

$resultado_periodo = mysqli_query($db, $query_periodo);

if (mysqli_num_rows($resultado_periodo) > 0) {
    $row = mysqli_fetch_assoc($resultado_periodo);
    $periodo_id = $row['p_id'];

    $query = "INSERT INTO estudiantes_docentes (estudiante_id, doc_asi_id, periodo_id) 
              VALUES ('$estudiante_id', '$doc_asi_id', '$periodo_id')";

    if (mysqli_query($db, $query)) {
        header('location: dis_asignaturas.php');
        exit;
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($db);
    }
} else {
    echo "No se encontró el período.";
}
?>