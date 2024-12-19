<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\WablasService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;




class SendVerificationsOTP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $randomCode;


    /**     
     * Create a new job instance.    
     *
     * @return void
     */
    public function __construct($user, $randomCode)
    {
        $this->user = $user;
        $this->randomCode = $randomCode;
    }

    /**
     * Execute the job. 
     *          
     * @return void
     */
    public function handle()
    {
        $message = "*" . $this->randomCode . "* adalah kode verifikasi OTP Anda. Demi keamanan, jangan bagikan kode ini.";
        WablasService::sendOTP($this->user->phone, $message);

    }
}
    