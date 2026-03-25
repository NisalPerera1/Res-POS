<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Order;
use App\Services\KOTPrinterService;
use Illuminate\Support\Collection;

class PrintKOTJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public int $kotRound,
        public Collection $items
    ) {
        $this->onQueue('printing');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $printer = new KOTPrinterService();
            $success = $printer->printKOT($this->order, $this->kotRound, $this->items);
            
            if ($success) {
                \Log::info('KOT printed successfully for Order #' . $this->order->order_number . ' Round ' . $this->kotRound);
            } else {
                \Log::warning('KOT print failed for Order #' . $this->order->order_number . ' Round ' . $this->kotRound);
            }
        } catch (\Exception $e) {
            \Log::error('KOT printing error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('KOT Print Job failed for Order #' . $this->order->order_number . ': ' . $exception->getMessage());
    }
}
