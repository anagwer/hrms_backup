<?php

namespace App\Utilities;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use Illuminate\Support\Facades\DB;

class Utility
{

    public static function DEFAULT_LIMIT()
    {
        return 25;
    }

    public static function newID()
    {
        $stmt = DB::select("SELECT uuid() as id");
        return $stmt[0]->id;
    }

    public static function newToken($id, $off, $grp)
    {
        $token = JWT::encode([
            'id' => $id,
            'off' => $off,
            'grp' => $grp,
            'iat' => time(),
            'exp' => (time() + (3600 * 24 * 7))
        ], env('JWT_SECRET'), env('JWT_HASH'));
        return $token;
    }

    public static function exToken($vRequest, $obj)
    {
        $token = $vRequest->bearerToken();
        $credentials = JWT::decode($token, env('JWT_SECRET'), [env('JWT_HASH')]);
        return $credentials->$obj;
    }

    public static function setArgs($value)
    {
        $args = array(
            "src" => $value,
        );
        return json_encode($args);
    }

    public static function setArgsDate($value)
    {
        $args = array(
            "src" => $value,
            "startDate" => '',
            "endDate" => ''
        );
        return json_encode($args);
    }

    public static function setResponse($vRequest, $vCode, $vMessage, $vData, $vCurPage = 0, $vCountRec = 0, $vCountData = 0, $vLimit = 0)
    {
        $new_token = Utility::newToken(
            Utility::exToken($vRequest, "id"),
            Utility::exToken($vRequest, "off"),
            Utility::exToken($vRequest, "grp")
        );


        if (is_null($vCurPage)) {
            $vCurPage = 0;
        }
        if (is_null($vCountRec)) {
            $vCountRec = 0;
        }
        if (is_null($vCountData)) {
            $vCountData = 0;
        }
        if (is_null($vLimit)) {
            $vLimit = 0;
        }
        if ($vCode == 200) {
            $vError = false;
        } else {
            $vError = true;
        }
        $status = array(
            "error" => $vError,
            "message" =>  $vMessage,
            "code" => $vCode
        );
        // $result["total"] = $vCountRec;
        $result["status"] = $status;
        $result["rows"] = $vData;
        $result["total"] = $vCountRec;
        if ($vLimit == 0) {
            $vCountPage = 0;
        } else {
            $vCountPage = ceil($vCountRec / $vLimit);
        }
        if ($vCountRec == 0) {
            $vNextPage = 0;
            $vmore = false;
        } elseif ($vCurPage >= $vCountPage) {
            $vNextPage = 0;
            $vmore = false;
        } else {
            $vNextPage = $vCurPage + 1;
            $vmore = true;
        }
        $meta = array(
            "more" =>  $vmore,
            "token" =>  $new_token,
            "timestamp" =>  date("Y-m-d H:i:s", time()),
            "count_rec" => $vCountRec,
            "count_data" => $vCountData,
            "count_page" => $vCountPage,
            "curr_page" => $vCurPage,
            "next_page" => $vNextPage
        );
        $result["meta"] = $meta;
        return response()->json($result);
    }


    public static function setResponseExT($vCode, $vMessage, $vData, $vCurPage = 0, $vCountRec = 0, $vCountData = 0, $vLimit = 0)
    {
        if (is_null($vCurPage)) {
            $vCurPage = 0;
        }
        if (is_null($vCountRec)) {
            $vCountRec = 0;
        }
        if (is_null($vCountData)) {
            $vCountData = 0;
        }
        if (is_null($vLimit)) {
            $vLimit = 0;
        }
        if ($vCode == 200) {
            $vError = false;
        } else {
            $vError = true;
        }
        $status = array(
            "error" => $vError,
            "message" =>  $vMessage,
            "code" => $vCode
        );
        $result["status"] = $status;
        $result["rows"] = $vData;
        $result["total"] = $vCountRec;
        $result["sort"] = "asc";
        if ($vLimit == 0) {
            $vCountPage = 0;
        } else {
            $vCountPage = ceil($vCountRec / $vLimit);
        }
        if ($vCountRec == 0) {
            $vNextPage = 0;
            $vmore = false;
        } elseif ($vCurPage >= $vCountPage) {
            $vNextPage = 0;
            $vmore = false;
        } else {
            $vNextPage = $vCurPage + 1;
            $vmore = true;
        }
        $meta = array(
            "more" =>  $vmore,
            "timestamp" =>  date("Y-m-d H:i:s", time()),
            "count_rec" => $vCountRec,
            "count_data" => $vCountData,
            "count_page" => $vCountPage,
            "curr_page" => $vCurPage,
            "next_page" => $vNextPage
        );
        $result["meta"] = $meta;
        return response()->json($result);
    }

    public static  function setID($value)
    {
        $args = array(
            "id" => $value
        );
        return json_encode($args);
    }


    public static function Encryption($value)
    {
        if ($value != "") {
            $Key = 'b4nd1t3o2@01';
            return md5($value);
        } else {
            return;
        }
    }


    public static function Decryption($value)
    {
        if ($value != "") {
            $Key = 'b4nd1t3o2@01';
            $KeyLen = strlen($Key);
            $KeyPos = 0;
            $SrcAsc = 0;
            $offset = hexdec(substr($value, 0, 2));
            $SrcPos = 2;
            $dest = "";
            // $messages = [];
            do {
                $SrcAsc = hexdec(substr($value, $SrcPos, 2));
                if ($KeyPos < $KeyLen) {
                    $KeyPos = $KeyPos + 1;
                } else {
                    $KeyPos = 1;
                }
                $TmpSrcAsc = $SrcAsc ^ ord($Key[$KeyPos - 1]);
                if ($TmpSrcAsc <= $offset) {
                    $TmpSrcAsc = 255 + $TmpSrcAsc - $offset;
                } else {
                    $TmpSrcAsc = $TmpSrcAsc - $offset;
                }
                // $messages[] = $TmpSrcAsc;
                $dest = $dest . chr($TmpSrcAsc);
                $offset = $SrcAsc;
                $SrcPos = $SrcPos + 2;
            } while ($SrcPos < strlen($value));
            return $dest;
        } else {
            return;
        }
    }

    public static function MeterDistance($params)
    {
        $input = json_decode($params);
        $latfrom = $input->latform;
        $longfrom = $input->longfrom;
        $latto = $input->latto;
        $longto = $input->longto;

        if (($latfrom == $latto) && ($longfrom == $longto)) {
            return 0;
        }

        $theta = $longfrom - $longto;
        $dist = sin(deg2rad($latfrom)) * sin(deg2rad($latto)) +  cos(deg2rad($latfrom)) * cos(deg2rad($latto)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return $miles * 1609.344;
        // $unit = strtoupper($unit);

        // if ($unit == "K") {
        //     return ($miles * 1.609344);
        // } else if ($unit == "N") {
        //     return ($miles * 0.8684);
        // } else {
        //     return $miles;
        // }
    }

    public static function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
        
        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
        
        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }
}
