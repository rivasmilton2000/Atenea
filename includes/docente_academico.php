<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';

function docenteSupervisadoAtenea(PDO $pdo): int
{
    $actual=(int)($_SESSION['usuario_id']??0);if(($_SESSION['usuario_rol']??'')==='docente')return $actual;
    $id=filter_var($_GET['docente_id']??$_POST['docente_id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;
    if($id<1)return 0;$q=$pdo->prepare("SELECT id FROM usuarios WHERE id=:id AND rol='docente' AND estado='activo' AND deleted_at IS NULL");$q->execute(['id'=>$id]);return (int)($q->fetchColumn()?:0);
}

function docentePuedeCurso(PDO $pdo,int $docenteId,int $asignaturaId): bool
{
    if($docenteId<1||$asignaturaId<1)return false;$q=$pdo->prepare("SELECT 1 FROM docentes_asignaturas WHERE docente_id=:d AND asignatura_id=:a AND estado='activo'");$q->execute(['d'=>$docenteId,'a'=>$asignaturaId]);return (bool)$q->fetchColumn();
}

function exigirCursoDocente(PDO $pdo,int $docenteId,int $asignaturaId): void
{
    if(!docentePuedeCurso($pdo,$docenteId,$asignaturaId)){http_response_code(403);exit('No tienes acceso a este curso.');}
}

function docentePuedeEstudiante(PDO $pdo,int $docenteId,int $estudianteId,?int $asignaturaId=null): bool
{
    $sql="SELECT 1 FROM estudiantes_docentes WHERE docente_id=:d AND estudiante_id=:e AND estado='activo'";$pa=['d'=>$docenteId,'e'=>$estudianteId];if($asignaturaId){$sql.=' AND asignatura_id=:a';$pa['a']=$asignaturaId;}$q=$pdo->prepare($sql.' LIMIT 1');$q->execute($pa);return (bool)$q->fetchColumn();
}

function cursosDocenteAtenea(PDO $pdo,int $docenteId): array
{
    if($docenteId<1)return [];$q=$pdo->prepare("SELECT a.id,a.codigo,a.nombre,a.descripcion,a.estado,COUNT(DISTINCT ed.estudiante_id) estudiantes FROM docentes_asignaturas da JOIN asignaturas a ON a.id=da.asignatura_id LEFT JOIN estudiantes_docentes ed ON ed.docente_id=da.docente_id AND ed.asignatura_id=a.id AND ed.estado='activo' WHERE da.docente_id=:d AND da.estado='activo' GROUP BY a.id ORDER BY a.nombre");$q->execute(['d'=>$docenteId]);return $q->fetchAll();
}

function docenteUrl(string $ruta,array $params=[]): string
{
    if(($_SESSION['usuario_rol']??'')==='admin'&&!isset($params['docente_id'])&&isset($GLOBALS['docentePortalId']))$params['docente_id']=$GLOBALS['docentePortalId'];$url=atenea_url('src/docente/'.$ruta);return $params?$url.(str_contains($url,'?')?'&':'?').http_build_query($params):$url;
}
