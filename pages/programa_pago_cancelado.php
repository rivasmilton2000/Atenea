<?php
require 'session.php';
require_once '../includes/connection.php';
require_once '../includes/atenea_auth.php';

$orderId=max(0,(int)($_GET['order_id']??0));
$publicUserId=(int)($_SESSION['PUBLIC_USER_ID']??0);
$programId=0;
if(logged_in() && atenea_session_is_public_user() && $orderId>0){
    $stmt=$db->prepare("SELECT programa_id FROM course_payment_requests WHERE order_id=? AND public_user_id=? AND status='pendiente' LIMIT 1");
    if($stmt){$stmt->bind_param('ii',$orderId,$publicUserId);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();$programId=(int)($row['programa_id']??0);}
    if($programId>0){
        $db->begin_transaction();
        $stmt=$db->prepare("UPDATE ordenes SET estado='cancelled' WHERE id=? AND estado='pending_payment'");$stmt->bind_param('i',$orderId);$stmt->execute();$stmt->close();
        $stmt=$db->prepare("UPDATE course_payment_requests SET status='cancelado' WHERE order_id=? AND public_user_id=? AND status='pendiente'");$stmt->bind_param('ii',$orderId,$publicUserId);$stmt->execute();$stmt->close();
        $db->commit();
    }
}
header('Location: programa_cotizar.php?id='.$programId.'&payment=cancelled');
exit;
