<?php

class AdminService
{
    private AdminRepository $adminRepository;

    public function __construct()
    {
        $this->adminRepository = new AdminRepository();
    }

    public function getSalesMetrics(
        string $from,
        string $to,
        ?int $branchId = null
    ): array {

        return [
            'total_sales' => $this->adminRepository->getTotalSales($from, $to, $branchId),
            'order_count' => $this->adminRepository->getOrderCount($from, $to, $branchId),
            'average_order_value' => $this->adminRepository->getAverageOrderValue($from, $to, $branchId),
            'customer_count' => $this->adminRepository->getCustomerCount($branchId),
            'new_customer_count' => $this->adminRepository->getNewCustomerCount($from, $to, $branchId),
            'product_count' => $this->adminRepository->getProductCount($branchId),
            'branch_count' => $this->adminRepository->getActiveBranchCount($branchId),
            'reservation_count' => $this->adminRepository->getReservationCount($from, $to, $branchId),
            'pending_reservations' => $this->adminRepository->getPendingReservationCount($branchId),
            'cancelled_order_count' => $this->adminRepository->getCancelledOrderCount($from, $to, $branchId),
            'low_stock_count' => $this->adminRepository->getLowStockCount($branchId),

            'sales_by_day' => $this->adminRepository->getSalesByDay($from, $to, $branchId),
            'sales_by_category' => $this->adminRepository->getSalesByCategory($from, $to, $branchId),
            'top_products' => $this->adminRepository->getTopProducts($from, $to, $branchId),
            'recent_orders' => $this->adminRepository->getRecentOrders($from, $to, $branchId),
            'low_stock' => $this->adminRepository->getLowStock($from, $to, $branchId),
            'sales_by_status' => $this->adminRepository->getSalesByStatus($from, $to, $branchId),
            'sales_by_order_type' => $this->adminRepository->getSalesByOrderType($from, $to, $branchId),
            'sales_by_payment' => $this->adminRepository->getSalesByPayment($from, $to, $branchId),
            'sales_by_hour' => $this->adminRepository->getSalesByHour($from, $to, $branchId),
            'reservation_status' => $this->adminRepository->getReservationStatus($from, $to, $branchId),
            'branch_performance' => $branchId === null
                ? $this->adminRepository->getBranchPerformance($from, $to)
                : [],
        ];
    }
}
