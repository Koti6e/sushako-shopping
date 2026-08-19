<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminControlCenterService;
use Illuminate\View\View;

class ApprovalCenterController extends Controller
{
    public function __invoke(AdminControlCenterService $controlCenter): View
    {
        return view('admin.approvals.index', [
            'queues' => $controlCenter->approvalQueues(),
        ]);
    }
}
