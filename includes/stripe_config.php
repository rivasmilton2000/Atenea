<?php
declare(strict_types=1);
require_once __DIR__.'/env.php';
function configuracionStripe():array{return ['publishable_key'=>entornoAtenea('STRIPE_PUBLISHABLE_KEY'),'secret_key'=>entornoAtenea('STRIPE_SECRET_KEY'),'webhook_secret'=>entornoAtenea('STRIPE_WEBHOOK_SECRET'),'currency'=>strtolower(entornoAtenea('STRIPE_CURRENCY','usd'))];}
function stripeConfigurado(array $c):bool{return str_starts_with((string)$c['secret_key'],'sk_')&&str_starts_with((string)$c['publishable_key'],'pk_')&&str_starts_with((string)$c['webhook_secret'],'whsec_')&&preg_match('/^[a-z]{3}$/',(string)$c['currency']);}
