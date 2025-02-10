<?php

namespace App\Http\Controllers;

use App\Models\DigitalReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $integrator = DigitalReceipt::getIPIntegrator($request->ruas_id, $request->gerbang_id);
        $mediasi = DigitalReceipt::getIPMediasi($request->ruas_id, $request->gerbang_id);

        $ipMediasi = $mediasi->host;
        $ipIntegrator = $integrator->host;

        // Windows
        // $pingMediasi = shell_exec("ping -n 2 -w 2 " . escapeshellarg($ipMediasi));
        // $pingIntegrator = shell_exec("ping -n 2 -w 2 " . escapeshellarg($ipIntegrator));

        // Linux
        $pingMediasi = shell_exec("ping -c 2 -w 2 " . escapeshellarg($ipMediasi));
        $pingIntegrator = shell_exec("ping -c 2 -w 2 " . escapeshellarg($ipIntegrator));

        if ($pingMediasi && $pingIntegrator) {
            Log::info("Ping berhasil: Mediasi {$ipMediasi} dan Integrator {$ipIntegrator}");

            return response()->json([
                'success' => true,
                'message' => "Ping mediasi ip and ping integrator ip success!"
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
