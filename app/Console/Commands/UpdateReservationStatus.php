<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservasi;
use Carbon\Carbon;

class UpdateReservationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update reservation status from PEMBAYARAN to CANCEL if created more than a day ago';
    //https://chatgpt.com/share/8b9aac42-5808-41cb-9ccd-1ecebc0e0102
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $reservasi = Reservasi::where('status', 'PEMBAYARAN')
            ->where('created_at', '<', Carbon::now()->subDay())
            ->get();
        
        foreach($reservasi as $res) {
            $res->update(['status' => 'CANCEL']);
        }

        $this->info('Reservation status updated successfully');
    }

}
