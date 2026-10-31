# Velox MVC — carrito, checkout, cupones y Wompi

## 1. Migración de base de datos

Antes de desplegar el código, ejecutar una sola vez:

\`\`\`sql
SOURCE database/migrations/20261031_store_cart_coupons_payments.sql;
\`\`\`

La migración crea:

- \`coupons\`: reglas y límites de los códigos promocionales.
- \`coupon_redemptions\`: registro de aplicación de cada cupón a un pedido.
- \`payment_transactions\`: ledger de intentos y resultados de Wompi.

Las tablas nuevas usan InnoDB. No se agregaron claves foráneas físicas hacia \`orders\` ni \`users\` para mantener compatibilidad con las instalaciones existentes; la aplicación realiza la limpieza de pagos y redenciones al eliminar una sede.

## 2. Variables de entorno

Configurar en el servidor, fuera del repositorio:

| Variable | Uso |
|---|---|
| \`WOMPI_PUBLIC_KEY\` | Llave pública del comercio |
| \`WOMPI_INTEGRITY_SECRET\` | Firma de integridad para Web Checkout |
| \`WOMPI_EVENTS_SECRET\` | Verificación de firmas del webhook |
| \`WOMPI_ENVIRONMENT\` | \`sandbox\` o \`production\` |

No guardar llaves reales en archivos versionados. El checkout actual usa Web Checkout alojado por Wompi; por eso la llave privada no es necesaria en este flujo.

Configurar primero las credenciales de pruebas de Wompi y validar el recorrido antes de pasar a producción.

## 3. Webhook de Wompi

Registrar en el panel de Wompi la URL pública HTTPS:

\`\`\`text
https://TU-DOMINIO/api/payments/webhook
\`\`\`

Suscribir el evento \`transaction.updated\`. Wompi debe poder alcanzar esta dirección desde Internet.

La redirección del navegador lleva al cliente a \`/checkout/resultado\`, pero no confirma el pago. El estado definitivo se actualiza únicamente después de validar la firma del webhook y contrastar la referencia, monto y moneda con el intento almacenado.

## 4. Recorrido de compra

1. El visitante agrega productos desde las tarjetas del catálogo. El carrito se conserva en el navegador mediante \`localStorage\`.
2. El cliente inicia sesión y abre \`/checkout\`.
3. Selecciona la sede para recoger el pedido, aplica opcionalmente un cupón y confirma.
4. El servidor vuelve a consultar los productos, opciones, combos, precios, impuestos e inventario.
5. El servidor valida el cupón de nuevo y lo consume dentro de la transacción que registra el pedido.
6. Se prepara un intento de pago único para Web Checkout de Wompi.
7. El webhook confirma el pago, rechaza/anula la transacción o la marca como reembolsada.
8. La pantalla de resultado consulta el estado persistido del pago.

El checkout implementado es de **recogida en sede**. No incluye cálculo de domicilios, direcciones de entrega ni tarifas de envío.

## 5. Gestión de cupones

Desde el panel administrativo, abrir **Módulos adicionales → Cupones**.

Cada cupón permite configurar:

- Código único.
- Descuento porcentual o valor fijo en COP.
- Compra mínima.
- Fecha y hora de inicio y vencimiento.
- Límite global de usos y límite por cliente.
- Activación y desactivación.

La validación de JavaScript solo muestra una estimación. La decisión definitiva se toma en el servidor al guardar el pedido.

## 6. Consideraciones de operación antes de producción

- Ejecutar la migración antes de habilitar el nuevo código.
- Probar en sandbox compra aprobada, rechazada y anulada; además, verificar que se restaure el inventario y se libere el uso del cupón cuando corresponde.
- Configurar y probar el webhook HTTPS desde el panel de Wompi.
- Confirmar que la tabla existente \`payment_gateways\` tenga una fila cuyo nombre corresponda a Wompi para sincronizar los pagos aprobados con la tabla histórica \`payments\`. El ledger principal de este flujo es \`payment_transactions\`.
- Un pedido pendiente reserva inventario y consume el cupón al registrarse. Los webhooks de rechazo, error o anulación liberan esos recursos. **Todavía no hay una tarea automática para liberar pedidos que el cliente abandone sin que Wompi emita un evento**; definir e implementar una política de expiración antes de operar con inventario real.
- No se han ejecutado pruebas con una base de datos real ni transacciones de sandbox desde este entorno. La configuración, los callbacks y los estados deben probarse en el ambiente de integración.
