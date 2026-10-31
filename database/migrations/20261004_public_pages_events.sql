-- Burger Town: páginas públicas, contacto y eventos
CREATE TABLE IF NOT EXISTS site_pages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(180) NOT NULL,
  page_type ENUM('general','legal','contact','event') NOT NULL DEFAULT 'general',
  title VARCHAR(180) NOT NULL,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description VARCHAR(500) DEFAULT NULL,
  h1 VARCHAR(255) DEFAULT NULL,
  content_html LONGTEXT DEFAULT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_site_pages_slug (slug),
  KEY idx_site_pages_type (page_type),
  KEY idx_site_pages_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(40) DEFAULT NULL,
  subject VARCHAR(180) DEFAULT NULL,
  message TEXT NOT NULL,
  status ENUM('new','read','replied','closed') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_status (status),
  KEY idx_contact_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_requests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  branch_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  event_type VARCHAR(100) NOT NULL,
  event_date DATE NOT NULL,
  event_time TIME NOT NULL,
  guests SMALLINT UNSIGNED NOT NULL,
  message TEXT DEFAULT NULL,
  status ENUM('new','contacted','confirmed','cancelled') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_event_branch (branch_id),
  KEY idx_event_status (status),
  KEY idx_event_date (event_date),
  CONSTRAINT fk_event_requests_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO site_pages (slug,page_type,title,meta_title,meta_description,h1,content_html,is_published)
VALUES
('politicas-de-cookies','legal','Política de Cookies','Política de Cookies | Burger Town','Política de cookies y tecnologías similares de Burger Town.','Política de Cookies',
'<p>El sitio web de Burger Town utiliza cookies y tecnologías similares para mejorar la experiencia de navegación de los usuarios.</p><h2>1. ¿Qué son las Cookies?</h2><p>Las cookies son pequeños archivos de texto que se almacenan en su dispositivo cuando visita un sitio web. Estas permiten recordar información sobre su visita para mejorar la funcionalidad del sitio.</p><h2>2. Tipos de Cookies que Utilizamos</h2><p>Nuestro sitio web puede utilizar los siguientes tipos de cookies:</p><ul><li><strong>Cookies Necesarias:</strong> son esenciales para el funcionamiento del sitio web y permiten funciones básicas como navegación y acceso a áreas seguras.</li><li><strong>Cookies de Rendimiento:</strong> permiten analizar cómo los usuarios interactúan con el sitio para mejorar su funcionamiento.</li><li><strong>Cookies de Funcionalidad:</strong> permiten recordar preferencias del usuario como idioma o ubicación.</li><li><strong>Cookies de Marketing:</strong> se utilizan para mostrar publicidad relevante o medir la efectividad de campañas publicitarias.</li></ul><h2>3. Cookies de Terceros</h2><p>El sitio puede utilizar servicios externos como herramientas de analítica, plataformas de pago y servicios de mapas, sujetos a sus propias políticas.</p><h2>4. Gestión de Cookies</h2><p>Los usuarios pueden aceptar, rechazar o eliminar cookies a través de la configuración de su navegador. Deshabilitar algunas cookies puede afectar determinadas funcionalidades.</p><h2>5. Cambios en la Política de Cookies</h2><p>Burger Town puede actualizar esta política en cualquier momento para reflejar cambios en el uso de cookies o en la legislación aplicable.</p>',1),
('politica-de-privacidad','legal','Política de Privacidad','Política de Privacidad | Burger Town Bogotá','Conoce cómo Burger Town recopila, utiliza y protege la información personal.','Política de Privacidad',
'<p>En Burger Town valoramos y respetamos la privacidad de nuestros usuarios y clientes. Esta Política de Privacidad describe cómo recopilamos, usamos y protegemos la información personal que usted proporciona al utilizar nuestro sitio web.</p><h2>1. Información que Recopilamos</h2><p>Podemos recopilar nombre y apellido, correo electrónico, teléfono, ubicación, información relacionada con pedidos e información de navegación.</p><h2>2. Uso de la Información</h2><p>La información puede utilizarse para procesar pedidos y servicios, mejorar la experiencia, enviar confirmaciones, responder solicitudes, comunicar novedades y analizar la navegación.</p><h2>3. Protección de la Información</h2><p>Burger Town implementa medidas técnicas y administrativas para proteger la información personal contra acceso no autorizado, pérdida o uso indebido.</p><h2>4. Compartir Información con Terceros</h2><p>Burger Town no vende ni comercializa información personal. Podrá compartirla únicamente con plataformas de pago, servicios de entrega, proveedores tecnológicos o autoridades cuando la ley lo requiera.</p><h2>5. Derechos del Titular</h2><p>De acuerdo con la Ley 1581 de 2012 en Colombia, los usuarios pueden conocer, actualizar o rectificar sus datos, solicitar prueba de autorización, solicitar eliminación cuando sea aplicable y revocar la autorización para el tratamiento.</p><h2>6. Conservación de los Datos</h2><p>La información se conservará durante el tiempo necesario para las finalidades descritas o durante el período exigido por la legislación aplicable.</p><h2>7. Cambios</h2><p>Burger Town se reserva el derecho de actualizar esta política. Las modificaciones serán publicadas en esta misma página.</p>',1),
('terminos-y-condiciones','legal','Términos y Condiciones','Términos y Condiciones | Burger Town Bogotá','Términos y condiciones de uso del sitio web de Burger Town.','Términos y Condiciones',
'<p>Bienvenido al sitio web de Burger Town. Al acceder y utilizar este sitio web, usted acepta cumplir con los siguientes términos y condiciones de uso.</p><h2>1. Información General</h2><p>Este sitio web es operado por Burger Town. El uso del sitio y la compra de productos o servicios están sujetos a estos términos y a nuestra política de privacidad.</p><h2>2. Uso del Sitio Web</h2><ul><li>El usuario es mayor de edad o cuenta con autorización de un adulto responsable.</li><li>Utilizará el sitio únicamente con fines legales.</li><li>No realizará actividades que puedan afectar el funcionamiento del sitio.</li></ul><h2>3. Productos y Servicios</h2><p>Los productos pueden variar según disponibilidad, sede, temporada o promociones. Burger Town podrá modificar o descontinuar productos.</p><h2>4. Precios</h2><p>Los precios publicados están expresados en pesos colombianos e incluyen los impuestos aplicables salvo indicación contraria.</p><h2>5. Pedidos en Línea</h2><p>Los pedidos están sujetos a confirmación de disponibilidad y los tiempos pueden variar según sede, demanda y factores externos.</p><h2>6. Cancelaciones y Modificaciones</h2><p>Una vez confirmado un pedido, puede no ser posible modificarlo o cancelarlo si la preparación ya comenzó.</p><h2>7. Propiedad Intelectual</h2><p>Logotipos, fotografías, diseños, textos y la marca Burger Town son propiedad de Burger Town o se utilizan con autorización y están protegidos por las leyes aplicables.</p><h2>8. Responsabilidad</h2><p>Burger Town no se hace responsable por problemas técnicos, interrupciones del servicio o daños derivados del uso incorrecto del sitio.</p><h2>9. Enlaces a Terceros</h2><p>El sitio puede contener enlaces a plataformas externas. Burger Town no controla ni se responsabiliza por el contenido o políticas de esos sitios.</p><h2>10. Protección de Datos</h2><p>El tratamiento de datos personales se realiza conforme a la legislación colombiana de protección de datos.</p><h2>11. Modificaciones</h2><p>Burger Town podrá actualizar estos términos en cualquier momento. Los cambios tendrán efecto desde su publicación.</p>',1)
ON DUPLICATE KEY UPDATE title=VALUES(title), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), h1=VALUES(h1), content_html=VALUES(content_html), is_published=1;

INSERT INTO site_pages (slug,page_type,title,meta_title,meta_description,h1,content_html,is_published)
VALUES ('contact','contact','Contacto','Contacto | Burger Town','Contáctate con Burger Town para reservas, sedes, eventos y atención al cliente.','Estamos para ayudarte','',1),
('eventos-burger-town-bogota','event','Eventos Burger Town Bogotá','Eventos en Burger Town Bogotá | Grupos y Celebraciones','Solicita tu evento en Burger Town Bogotá y selecciona la sede que prefieras.','Celebra como se debe: con mesa lista y buen parche','',1)
ON DUPLICATE KEY UPDATE title=VALUES(title), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), h1=VALUES(h1), is_published=1;
