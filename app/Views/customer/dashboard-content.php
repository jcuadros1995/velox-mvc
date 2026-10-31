<?php
$stats = $stats ?? [];
$orders = $orders ?? [];
$products = $products ?? [];
?>
<div class="ca-stats">
 <div class="ca-card ca-stat"><i class="fa-solid fa-receipt"></i><span>Pedidos realizados</span><strong><?= (int)($stats['total_orders'] ?? 0) ?></strong></div>
 <div class="ca-card ca-stat"><i class="fa-solid fa-box-open"></i><span>Pedidos activos</span><strong><?= (int)($stats['active_orders'] ?? 0) ?></strong></div>
 <div class="ca-card ca-stat"><i class="fa-solid fa-circle-check"></i><span>Pedidos entregados</span><strong><?= (int)($stats['completed_orders'] ?? 0) ?></strong></div>
 <div class="ca-card ca-stat"><i class="fa-solid fa-wallet"></i><span>Total comprado</span><strong style="font-size:20px"><?= $caMoney($stats['total_spent'] ?? 0) ?></strong></div>
</div>
<section class="ca-card">
 <div class="ca-card-head"><h2>Pedidos recientes</h2><a class="ca-link" href="<?= APP_URL ?>/mi-cuenta/pedidos">Ver todos <i class="fa-solid fa-arrow-right"></i></a></div>
 <?php if (!$orders): ?><div class="ca-empty"><i class="fa-solid fa-bag-shopping"></i><h3 class="h6 fw-bold text-dark">Todavía no tienes pedidos</h3><p>Cuando realices tu primera compra, podrás seguirla desde aquí.</p><a class="ca-button" href="<?= APP_URL ?>/tienda">Explorar tienda</a></div>
 <?php else: ?><div class="ca-table-wrap"><table><thead><tr><th>Pedido</th><th>Fecha</th><th>Artículos</th><th>Entrega</th><th>Total</th><th>Estado</th><th></th></tr></thead><tbody>
 <?php foreach($orders as $o): ?><tr><td><strong><?= htmlspecialchars($o['order_number'],ENT_QUOTES,'UTF-8') ?></strong></td><td><?= date('d/m/Y',strtotime($o['created_at'])) ?></td><td><?= (int)$o['item_quantity'] ?></td><td><?= ($o['fulfillment_type'] ?? 'pickup')==='delivery'?'Domicilio':'Recoger' ?></td><td><strong><?= $caMoney($o['total']) ?></strong></td><td><span class="ca-status ca-status--<?= htmlspecialchars($o['status'],ENT_QUOTES,'UTF-8') ?>"><?= htmlspecialchars($caStatus($o['status']),ENT_QUOTES,'UTF-8') ?></span></td><td><a class="ca-link" href="<?= APP_URL ?>/mi-cuenta/pedido?id=<?= (int)$o['id'] ?>">Detalle</a></td></tr><?php endforeach; ?>
 </tbody></table></div><?php endif; ?>
</section>
<section class="ca-card"><div class="ca-card-head"><h2>Productos que has comprado</h2><a class="ca-link" href="<?= APP_URL ?>/mi-cuenta/productos">Ver productos</a></div>
 <?php if (!$products): ?><p class="ca-note mb-0">Tus productos comprados aparecerán aquí.</p><?php else: ?><div class="ca-product-grid"><?php foreach($products as $p): $image=$p['image_path']??''; ?><article class="ca-product"><?php if($image!==''): ?><img src="<?= APP_URL ?>/public/<?= htmlspecialchars(ltrim($image,'/'),ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($p['item_name'],ENT_QUOTES,'UTF-8') ?>"><?php else: ?><div class="ca-avatar"><i class="fa-solid fa-burger"></i></div><?php endif; ?><div><strong><?= htmlspecialchars($p['item_name'],ENT_QUOTES,'UTF-8') ?></strong><small>Última compra: <?= date('d/m/Y',strtotime($p['created_at'])) ?><br>Cantidad: <?= (int)$p['quantity'] ?></small></div></article><?php endforeach; ?></div><?php endif; ?>
</section>