<?php
// Credenciales de la base de datos
$dbuser = 'root';
$dbpass = ''; // Sin contraseña
$dbhost = 'auth-db1135.hstgr.io';
$dbname = 'u445672402_escuela';

// Nombre del archivo de respaldo
$backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$backupFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $backupFile;
$errorLogPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_error.log';

// Ruta completa a mysqldump
//$mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe'; // Actualiza esta ruta si es diferente 
$mysqldumpPath = 'D:\\Xampp\\mysql\\bin\\mysqldump.exe'; //Comentar luego
// Comando para realizar el respaldo de la base de datos
$command = "$mysqldumpPath --user=$dbuser --password=$dbpass --host=$dbhost $dbname > $backupFilePath 2> $errorLogPath";

// Ejecutar el comando y capturar la salida de error
exec($command, $output, $return_var);

// Verificar si el archivo de respaldo se ha creado y tiene contenido
if ($return_var === 0 && file_exists($backupFilePath) && filesize($backupFilePath) > 0) {
    // Descargar el archivo de respaldo
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($backupFilePath));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backupFilePath));
    readfile($backupFilePath);

    // Eliminar el archivo temporal
    unlink($backupFilePath);
    exit();
} else {
    // Enviar respuesta JSON para SweetAlert2
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: El archivo de respaldo está vacío o no se creó correctamente.'
    ]);
    exit();
}
?>
