<?php

class CartController extends Controller
{
    public function index(): void
    {
        $this->view('layouts/module_public_layout', [
            'contentView' => __DIR__ . '/../Views/store/cart.php',
            'title' => 'Mi carrito | ' . SiteConfig::siteName()
        ]);
    }

    public function checkout(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirect('/login');
        }

        if (strtolower((string) ($user['role_name'] ?? '')) !== 'customer') {
            $this->redirect('/');
        }

        $branches = (new BranchRepository())->getActiveBranches();

        $this->view('layouts/module_public_layout', [
            'contentView' => __DIR__ . '/../Views/store/checkout.php',
            'title' => 'Finalizar compra | ' . SiteConfig::siteName(),
            'storeCustomer' => $user,
            'storeBranches' => $branches,
            'script' => 'checkout'
        ]);
    }

    public function paymentResult(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirect('/login');
        }

        if (strtolower((string) ($user['role_name'] ?? '')) !== 'customer') {
            $this->redirect('/');
        }

        $reference = trim((string) ($_GET['reference'] ?? ''));
        $payment = $reference !== ''
            ? (new PaymentRepository())->findForCustomer($reference, (int) $user['id'])
            : null;

        $this->view('layouts/module_public_layout', [
            'contentView' => __DIR__ . '/../Views/store/payment-result.php',
            'title' => 'Estado de tu pago | ' . SiteConfig::siteName(),
            'paymentResult' => $payment,
            'script' => 'payment-result'
        ]);
    }
}
