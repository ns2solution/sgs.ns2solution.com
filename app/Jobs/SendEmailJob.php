<?php
  
namespace App\Jobs;
   
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Mail\LoginMobile;
use App\Mail\RegisterMobile;
use App\Mail\VerificationEmail;


use Illuminate\Support\Facades\Mail;
   
class SendEmailJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
  
    protected $details;
  
    public function __construct($details)
    {
        $this->details = $details;
    }
   
    public function handle()
    {
        $data  = $this->details;
        $email = null;

        switch($data['type']){

            case 'login':
                $email = new LoginMobile($data);
                break;

            case 'register':
                $email = new RegisterMobile($data);
                break;

            case 'verification':
                $email = new VerificationEmail($data);
                break;
    

        }

        Mail::to($data['email'])->send($email);
    }
}