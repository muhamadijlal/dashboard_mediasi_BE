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
            'type' => 'required|in:resi,mediasi',
            'jenis' => 'required|in:transaction_detail,recap_at4,data_compare'
        ]);

        // Get the appropriate data based on the 'type' parametera
        if($request->jenis === 'data_compare') {
            return $this->pingIntegratorAndMediasi($request->type, $request->ruas_id, $request->gerbang_id);
        } else if($request->jenis === 'transaction_detail' || $request->jenis === 'recap_at4') {
            return $this->pingMediasi($request->type, $request->ruas_id, $request->gerbang_id);
        }
    }

    private function pingMediasi($type, $ruas_id, $gerbang_id)
    {
        $mediasi = $this->getMediasi($type, $ruas_id, $gerbang_id);
    
        $ipMediasi = $mediasi->host;
        $pingMediasi = $this->pingHost($ipMediasi);

        // Check if both pings are successful
        if($this->isPingSuccessful($pingMediasi)){
            Log::info("Ping berhasil: Mediasi {$ipMediasi}");
    
            return response()->json([
                'success' => true,
                'message' => "Connected"
            ]);
        } else {
            Log::error("Ping gagal: Mediasi {$ipMediasi}");
    
            return response()->json([
                'success' => false,
                'message' => 'Request Time Out'
            ]);
        }
    }
    
    private function pingIntegratorAndMediasi($type, $ruas_id, $gerbang_id)
    {
        [$integrator, $mediasi] = $this->getIntegratorAndMediasi($type, $ruas_id, $gerbang_id);
    
        $ipMediasi = $mediasi->host;
        $pingMediasi = $this->pingHost($ipMediasi);

        $ipIntegrator = $integrator->host;
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

    private function getMediasi($type, $ruasId, $gerbangId)
    {
        if ($type === 'resi') {
            return DigitalReceipt::getIPMediasi($ruasId, $gerbangId);
        } else {
            return Mediasi::getIPMediasi($ruasId, $gerbangId);
        }
    }
    
    // Method to execute the ping command for the given host
    private function pingHost($host)
    {
        $pingCommand = (stristr(PHP_OS, 'LINUX')) ?
            "ping -c 2 -w 2 " . escapeshellarg($host) :
            "ping -n 2 -w 2 " . escapeshellarg($host);
    
        return shell_exec($pingCommand);
    }
    
    // Method to check if the ping was successful (receive = 2 should be in the output)
    private function isPingSuccessful($pingOutput)
    {
        return (stristr(PHP_OS, 'LINUX')) ?
            (strpos($pingOutput, '2 packets transmitted, 2 received') !== false) :
            (strpos($pingOutput, 'Received = 2') !== false);
    }
}
