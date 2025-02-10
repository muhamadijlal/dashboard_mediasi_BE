<?php

namespace App\Http\Controllers;

use App\Models\DigitalReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckConnectionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'ruas_id' => 'required',
            'gerbang_id' => 'required',
        ]);

        $credentials = DigitalReceipt::getIP($request->ruas_id, $request->gerbang_id);

        $ipMediasi = $credentials[0]->host;
        $ipIntegrator = $credentials[1]->host;

        $pingResult = function($ip){
            // menjalankan perintah ping
            return exec("ping -n 4 " . escapeshellarg($ip)); // untuk Windows
        };

       $pingMediasi = $pingResult($ipMediasi);
       $pingIntegrator = $pingResult($ipIntegrator);

        if ($pingMediasi && $pingIntegrator) {
            Log::info("Ping berhasil: Mediasi {$ipMediasi} dan Integrator {$ipIntegrator}");

            return response()->json([
                'success' => true,
                'message' => "Ping mediasi ip and ping integrator ip success! \nmediasi: {$pingMediasi}, \nintegrator: {$pingIntegrator}"
            ]);
        } else {
            Log::error("Ping gagal: Mediasi {$ipMediasi} atau Integrator {$ipIntegrator}");

            return response()->json([
                'success' => false,
                'message' => 'connection failed!'
            ]);
        }
    }
}
