<?php

namespace App\Http\Middleware;

use App\Http\Models\VersionInfo;
use Closure;
use Illuminate\Http\Request;

class VersioningMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $clientVersion = $request->header('Version');

        if ((!$request->hasHeader('Version')) || $clientVersion == '' || !preg_match("/^[0-9].[0-9].[0-9]$/", $clientVersion))
            return $this->_errorResponse('Versi aplikasi tidak valid.');

        $lastVersion = VersionInfo::where('application_name', 'hrms')
            ->orderBy('version_number', 'DESC')
            ->limit(1)->get()->first();

        $lastVersionArray = explode('.', $lastVersion->version_number);
        $clientVersionArray = explode('.', $clientVersion);

        foreach ($lastVersionArray as $key => $value) {
            if ($clientVersionArray[$key] > $value) : break;
            endif;
            if ($clientVersionArray[$key] < $value) {
                if (date('Y-m-d') < $lastVersion->active_date) {
                    $request->request->add([
                        'versionExpiredMessage' => 'Tersedia versi ' . $lastVersion->version_number . '. Silahkan update di playstore sebelum tanggal ' . date('d F Y', strtotime($lastVersion->active_date))
                    ]);
                } else {
                    return $this->_errorResponse(
                        $lastVersion->version_info,
                        $lastVersion
                    );
                }
            }
        }

        return $next($request);
    }

    private function _errorResponse($message, $data = null)
    {
        $rows = $data === null ? [] : [$data];
        return response()->json([
            "status" => [
                "error" => true,
                "message" =>  $message,
                "code" => 702
            ],
            "rows" => $rows
        ]);
    }
}
