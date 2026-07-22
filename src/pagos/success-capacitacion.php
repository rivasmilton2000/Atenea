<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/capacitaciones.php';
require_once dirname(__DIR__,2).'/includes/stripe_config.php';
exigirRol(['usuario']);
$session=trim((string)($_GET['session_id']??''));
$pdo=obtenerConexion();
if(preg_match('/^cs_[A-Za-z0-9_]+$/',$session)){
    $propia=$pdo->prepare('SELECT id FROM capacitacion_pagos WHERE usuario_id=:u AND stripe_checkout_session_id=:s LIMIT 1');$propia->execute(['u'=>$_SESSION['usuario_id'],'s'=>$session]);
    if($propia->fetchColumn()){
        try{$config=configuracionStripe();$autoload=dirname(__DIR__,2).'/includes/stripe/vendor/autoload.php';if(stripeConfigurado($config)&&is_file($autoload)){require_once$autoload;$sesionStripe=(new Stripe\StripeClient($config['secret_key']))->checkout->sessions->retrieve($session,[]);if(($sesionStripe->payment_status??'')==='paid')procesarWebhookCapacitacion((object)['id'=>'server_sync_'.hash('sha256',$session),'type'=>'server.checkout.sync'],$sesionStripe);}}
        catch(Throwable $e){error_log('Sincronización retorno Stripe capacitación: '.preg_replace('/[\r\n\t]+/',' ',$e->getMessage()));}
    }
}
$q=$pdo->prepare('SELECT cp.estado,a.nombre,i.estado estado_inscripcion,s.codigo FROM capacitacion_pagos cp INNER JOIN asignaturas a ON a.id=cp.asignatura_id LEFT JOIN inscripciones_capacitacion i ON i.pago_id=cp.id LEFT JOIN capacitacion_secciones s ON s.id=i.seccion_id WHERE cp.usuario_id=:u AND cp.stripe_checkout_session_id=:session LIMIT 1');
$q->execute(['u'=>$_SESSION['usuario_id'],'session'=>$session]);$pago=$q->fetch();
$pageTitle='Estado del pago | Atenea';$pageDescription='Consulta el estado confirmado por Stripe.';require dirname(__DIR__,2).'/includes/header.php';
?><main class="main"><section class="section"><div class="container" style="max-width:760px"><div class="card shadow-sm"><div class="card-body p-5 text-center"><?php if(!$pago):?><h1 class="h3">Pago no localizado</h1><p>La referencia no pertenece a tu cuenta.</p><?php elseif($pago['estado']!=='pagado'):?><h1 class="h3">Pago en verificación</h1><p>El pago sigue pendiente. Puedes volver a consultar sin realizar otro pago.</p><a class="btn btn-outline-primary" href="?session_id=<?=rawurlencode($session)?>">Volver a consultar</a><?php elseif($pago['estado_inscripcion']==='pendiente_asignacion'):?><h1 class="h3">Pago completado</h1><p>Tu inscripción en <?=atenea_e($pago['nombre'])?> está pendiente de docente y sección. La asignación puede tardar un máximo de 3 días y no debes pagar nuevamente.</p><?php else:?><h1 class="h3">Pago e inscripción confirmados</h1><p>Ya estás inscrito en <?=atenea_e($pago['nombre'])?>, sección <?=atenea_e($pago['codigo'])?>.</p><?php endif;?><a class="btn-atenea d-inline-block mt-3" href="<?=atenea_url('src/estudiantes/cursos.php')?>">Mis capacitaciones</a></div></div></div></section></main><?php require dirname(__DIR__,2).'/includes/footer.php';
