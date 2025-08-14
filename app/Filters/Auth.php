<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Ziishaned\PhpLicense\PhpLicense;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request)
    {
        
    }
    // public function before(RequestInterface $request)
    // {
    //     try {
    //         $requestURI = uri_string();
    //         $licensePath = APPPATH . "License/license.key";
    //         $publicKeyPath = APPPATH . "License/public_key.pem";
    //         $license = file_get_contents($licensePath);
    //         $publicKey = file_get_contents($publicKeyPath);
    //         $parsedLicense = PhpLicense::parse($license, $publicKey);
    //         $validityResult = (object) $this->checkLicenseValidity($parsedLicense["validityDate"]);

    //         if ($validityResult->valid) {
    //             $daysRemaining = $validityResult->daysRemaining;
    //             if ($daysRemaining < 15 || $requestURI == "admin/settings") {
    //                 if ($daysRemaining <= 1) {
    //                     $daysRemaining .= " day.";
    //                 } else {
    //                     $daysRemaining .= " days.";
    //                 }
    //                 $session = session();
    //                 $session->setFlashdata('validityMessage', "License valid for another $daysRemaining");
    //             }
    //             if (!session()->get('isLoggedIn')) {
    //                 return redirect()->to('/');
    //             }
    //         } else {
    //             return $this->showLicenseExpired();
    //         }
    //     } catch (\Exception $e) {
    //         return $this->showLicenseExpired();
    //     }
    // }

    //--------------------------------------------------------------------

    public function after(RequestInterface $request, ResponseInterface $response)
    {
        // Do something here
    }

    private function checkLicenseValidity($validityDate)
    {
        date_default_timezone_set('Asia/Kolkata');
        $today = date("Y-m-d");
        $todayTimestamp = strtotime($today);
        $validityTimestamp = strtotime($validityDate);
        $response = array();

        if ($todayTimestamp > $validityTimestamp) {
            $response["valid"] = false;
        } else {
            $secondsRemaining = $validityTimestamp - $todayTimestamp;
            $daysRemaining = round($secondsRemaining / (60 * 60 * 24));
            $response["valid"] = true;
            $response["validityDate"] = $validityDate;
            $response["daysRemaining"] = $daysRemaining;
        }

        return $response;
    }

    private function showLicenseExpired()
    {
        session()->destroy();
        return redirect()->to('/license-expired');
    }
}
