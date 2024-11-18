<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contactInfo;
    protected $message;

    public function __construct($contactInfo, $message)
    {
        $this->contactInfo = $contactInfo;
        $this->message = $message;
    }

    public function handle()
    {
        $ch = curl_init();
        $parameters = [
            'apikey' => env('SEMAPHORE_API_KEY'),
            'number' => $this->contactInfo,
            'message' => $this->message,
            'sendername' => 'Alwad'
        ];

        curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);
        curl_close($ch);


        \Log::info("{$output}");
    }
}