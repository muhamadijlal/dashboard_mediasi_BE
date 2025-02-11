<?php

namespace App\Http\Controllers;

use App\Models\DigitalReceipt;
use App\Models\Mediasi;
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
            'type' => 'required|in:resi,mediasi'
        ]);

        // Get the appropriate data based on the 'type' parameter
        [$integrator, $mediasi] = $this->getIntegratorAndMediasi($request->type, $request->ruas_id, $request->gerbang_id);
    
        $ipMediasi = $mediasi->host;
        $ipIntegrator = $integrator->host;

        // Run the ping checks
        $pingMediasi = $this->pingHost($ipMediasi);
        $pingIntegrator = $this->pingHost($ipIntegrator);

        // Check if both pings are successful
        if ($this->isPingSuccessful($pingMediasi) && $this->isPingSuccessful($pingIntegrator)) {
            Log::info("Ping berhasil: Mediasi {$ipMediasi} dan Integrator {$ipIntegrator}");
    
            return response()->json([
                'success' => true,
                'message' => "Connected"
            ]);
        } else {
            Log::error("Ping gagal: Mediasi {$ipMediasi} atau Integrator {$ipIntegrator}");
    
            return response()->json([
                'success' => false,
                'message' => 'Request Time Out'
            ]);
        }
    }
    
    // Method to fetch the integrator and mediasi data based on type
    private function getIntegratorAndMediasi($type, $ruasId, $gerbangId)
    {
        if ($type === 'resi') {
            return [
                DigitalReceipt::getIPIntegrator($ruasId, $gerbangId),
                DigitalReceipt::getIPMediasi($ruasId, $gerbangId)
            ];
        } else {
            return [
                Mediasi::getIPIntegrator($ruasId, $gerbangId),
                Mediasi::getIPMediasi($ruasId, $gerbangId)
            ];
        }
    }
    
    // Method to execute the ping command for the given host
    private function pingHost($host)
    {
        $pingCommand = (stristr(PHP_OS, 'WIN')) ?
            "ping -n 2 -w 2 " . escapeshellarg($host) :
            "ping -c 2 -w 2 " . escapeshellarg($host);
    
        return shell_exec($pingCommand);
    }
    
    // Method to check if the ping was successful (receive = 2 should be in the output)
    private function isPingSuccessful($pingOutput)
    {
        return (stristr(PHP_OS, 'LINUX')) ?
            (strpos($pingOutput, '2 packets transmitted, 2 received') !== false) :
            (strpos($pingOutput, '2 received') !== false);
    }
}
