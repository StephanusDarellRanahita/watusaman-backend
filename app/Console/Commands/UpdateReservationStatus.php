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
    protected $description = 'Update reservation status from PEMBAYARAN to BATAL if created more than a day ago';
    
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
            $res->update(['status' => 'BATAL']);
        }

        $this->info('Reservation status updated successfully');
    }

}
