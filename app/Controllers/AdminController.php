<?php
// app/Controllers/AdminController.php

class AdminController extends Controller
{
    private BranchRepository $branchRepository;

    public function __construct()
    {
        $this->branchRepository = new BranchRepository();
    }

    public function dashboard(): void
    {
        $user = Auth::user() ?? [];
        $role = strtolower((string) ($user['role_name'] ?? ''));

        $branches = $role === 'superadmin'
            ? $this->branchRepository->getActiveBranches()
            : [];

        $this->view('admin/layout', [
            'contentView' => __DIR__ . '/../Views/admin/dashboard.php',
            'adminBranches' => $branches
        ]);
    }
}
