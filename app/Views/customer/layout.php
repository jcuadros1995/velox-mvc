<?php
$caName = trim((string)($customer['first_name'] ?? '') . ' ' . (string)($customer['last_name'] ?? ''));
$caEmail = (string)($customer['email'] ?? '');
$caInitials = strtoupper(substr((string)($customer['first_name'] ?? 'C'),0,1) . substr((string)($customer['last_name'] ?? ''),0,1));
$caSections = [
 'dashboard' => ['Mi cuenta','fa-house'],
 'orders' => ['Mis pedidos','fa-box'],
 'products' => ['Mis productos','fa-bag-shopping'],
 'profile' => ['Datos personales','fa-user'],
];
$caStatus = function($status) {
 $labels=['pending'=>'Pendiente','processing'=>'En preparación','completed'=>'Entregado','cancelled'=>'Cancelado','refunded'=>'Reembolsado'];
 return $labels[$status] ?? ucfirst((string)$status);
};
$caMoney = fn($value) => '$' . number_format((float)$value,0,',','.');
?>
<section class="customer-area"><div class="ca-shell">
<div class="ca-heading"><div><div class="ca-eyebrow">Área de cliente</div><h1><?= htmlspecialchars($caSections[$customerSection][0] ?? 'Mi cuenta', ENT_QUOTES, 'UTF-8') ?></h1><p class="ca-subtitle">Administra tus compras y tu información personal.</p></div><a class="ca-button ca-button--light" href="<?= APP_URL ?>/tienda"><i class="fa-solid fa-store"></i> Ir a la tienda</a></div>
<div class="ca-grid"><aside class="ca-sidebar"><div class="ca-user"><div class="ca-avatar"><?= htmlspecialchars($caInitials,ENT_QUOTES,'UTF-8') ?></div><div><strong><?= htmlspecialchars($caName ?: 'Cliente',ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars($caEmail,ENT_QUOTES,'UTF-8') ?></small></div></div><nav class="ca-nav" aria-label="Mi cuenta"><?php foreach($caSections as $key=>$item): ?><a href="<?= APP_URL ?>/mi-cuenta<?= $key==='dashboard'?'':'/'.($key==='orders'?'pedidos':($key==='products'?'productos':'perfil')) ?>" class="<?= $customerSection===$key?'active':'' ?>"><i class="fa-solid <?= $item[1] ?>"></i><span><?= $item[0] ?></span></a><?php endforeach; ?></nav><div class="ca-logout"><a href="#" data-customer-logout><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Cerrar sesión</span></a></div></aside>
<main class="ca-content"><?php require __DIR__ . '/' . basename($customerSection) . '-content.php'; ?></main></div></div></section>