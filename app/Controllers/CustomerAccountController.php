<?php
// app/Controllers/CustomerAccountController.php
class CustomerAccountController extends Controller
{
    private OrderRepository $orders;
    private UserRepository $users;

    public function __construct()
    {
        $this->orders = new OrderRepository();
        $this->users = new UserRepository();
    }

    private function customer(): ?array
    {
        $user = Auth::user();
        if (!$user || strtolower((string)($user['role_name'] ?? '')) !== 'customer') {
            http_response_code(403);
            $this->view('layouts/public_layout', [
                'title' => 'Acceso no autorizado | Mi cuenta',
                'contentView' => __DIR__ . '/../Views/errors/403.php'
            ]);
            return null;
        }
        return $this->users->findWithRole((int)$user['id']);
    }

    private function render(string $view, string $title, array $data = []): void
    {
        $this->view('layouts/module_public_layout', array_merge($data, [
            'title' => $title . ' | Mi cuenta',
            'contentView' => __DIR__ . '/../Views/customer/' . $view . '.php',
            'script' => 'customer-account',
            'customerSection' => $view
        ]));
    }

    public function dashboard(): void
    {
        $customer = $this->customer();
        if (!$customer) return;
        $id = (int)$customer['id'];
        $this->render('dashboard', 'Mi cuenta', [
            'customer' => $customer,
            'stats' => $this->orders->getCustomerOrderStats($id),
            'orders' => $this->orders->getCustomerOrders($id, 5, 0),
            'products' => $this->orders->getCustomerRecentOrderItems($id, 4)
        ]);
    }

    public function orders(): void
    {
        $customer = $this->customer();
        if (!$customer) return;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $id = (int)$customer['id'];
        $total = $this->orders->countCustomerOrders($id);
        $this->render('orders', 'Mis pedidos', [
            'customer' => $customer,
            'orders' => $this->orders->getCustomerOrders($id, $perPage, ($page - 1) * $perPage),
            'page' => $page,
            'pages' => max(1, (int)ceil($total / $perPage)),
            'totalOrders' => $total
        ]);
    }

    public function orderDetail(): void
    {
        $customer = $this->customer();
        if (!$customer) return;
        $orderId = (int)($_GET['id'] ?? 0);
        $order = $this->orders->getOrderDetailsForCustomer($orderId, (int)$customer['id']);
        if (!$order) {
            http_response_code(404);
            $this->render('order-not-found', 'Pedido no encontrado', ['customer' => $customer]);
            return;
        }
        $this->render('order-detail', 'Detalle del pedido', [
            'customer' => $customer,
            'order' => $order
        ]);
    }

    public function products(): void
    {
        $customer = $this->customer();
        if (!$customer) return;
        $this->render('products', 'Mis productos', [
            'customer' => $customer,
            'products' => $this->orders->getCustomerPurchasedProducts((int)$customer['id'])
        ]);
    }

    public function profile(): void
    {
        $customer = $this->customer();
        if (!$customer) return;
        $this->render('profile', 'Mis datos personales', ['customer' => $customer]);
    }

    public function updateProfile(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $sessionUser = Auth::user();
        if (!$sessionUser || strtolower((string)($sessionUser['role_name'] ?? '')) !== 'customer') {
            $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
            return;
        }

        $input = $_POST;
        if (!$input) {
            $decoded = json_decode(file_get_contents('php://input') ?: '', true);
            $input = is_array($decoded) ? $decoded : [];
        }

        $first = trim(strip_tags((string)($input['first_name'] ?? '')));
        $last = trim(strip_tags((string)($input['last_name'] ?? '')));
        $phone = trim(strip_tags((string)($input['phone'] ?? '')));
        $documentType = (string)($input['document_type'] ?? 'CC');
        $document = trim(strip_tags((string)($input['document_number'] ?? '')));
        $allowedTypes = ['CC','CE','NIT','PP'];

        if ($first === '' || $last === '' || strlen($first) > 240 || strlen($last) > 240 || (function_exists('mb_strlen') && (mb_strlen($first) > 60 || mb_strlen($last) > 60))) {
            $this->json(['success' => false, 'message' => 'Ingresa un nombre y apellido válidos.'], 422);
            return;
        }
        if ($phone !== '' && !preg_match('/^[0-9+() .-]{7,30}$/', $phone)) {
            $this->json(['success' => false, 'message' => 'El teléfono no tiene un formato válido.'], 422);
            return;
        }
        if (!in_array($documentType, $allowedTypes, true) || mb_strlen($document) > 30) {
            $this->json(['success' => false, 'message' => 'El documento no tiene un formato válido.'], 422);
            return;
        }

        try {
            $stmt = Database::getConnection()->prepare("
                UPDATE users
                SET first_name = :first_name,
                    last_name = :last_name,
                    phone = :phone,
                    document_type = :document_type,
                    document_number = :document_number
                WHERE id = :id AND role_id = 4
            ");
            $stmt->execute([
                'first_name' => $first,
                'last_name' => $last,
                'phone' => $phone !== '' ? $phone : null,
                'document_type' => $documentType,
                'document_number' => $document !== '' ? $document : null,
                'id' => (int)$sessionUser['id']
            ]);
            $_SESSION['user_name'] = trim($first . ' ' . $last);
            $this->json(['success' => true, 'message' => 'Tus datos fueron actualizados.']);
        } catch (Throwable $e) {
            error_log('[CUSTOMER_PROFILE_UPDATE] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No fue posible actualizar tus datos.'], 500);
        }
    }
}
