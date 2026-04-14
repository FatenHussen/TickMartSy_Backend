<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Admin\FlashSaleService;

class ExpireFlashSalesCommand extends Command
{
    protected $signature = 'flashsale:expire';

    protected $description = 'Deactivate flash sales whose end date already passed';

    public function __construct(private FlashSaleService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->service->expireFlashSales();
        $this->info("Expired {$count} flash sale(s).");
        return 0;
    }
}
