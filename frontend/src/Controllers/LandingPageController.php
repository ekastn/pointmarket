<?php

namespace App\Controllers;

use App\Core\ApiClient;

class LandingPageController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function showLandingPage(): void
    {
        $this->render('landingpage/landing', [], 'landing_layout');
    }

    public function showSahabatBelajar(): void
    {
        $this->render('landingpage/sahabat-belajar', [
            'bodyClass' => 'bg-slate-900 text-slate-50 font-sans selection:bg-cyan-500 selection:text-white overflow-x-hidden'
        ], 'landing_layout');
    }

    public function showAlurKerja(): void
    {
        $this->render('landingpage/alur-kerja', [
            'bodyClass' => 'bg-slate-950 text-slate-200 overflow-x-hidden'
        ], 'landing_layout');
    }

    public function showStudiKasus(): void
    {
        $this->render('landingpage/studi-kasus', [
            'bodyClass' => 'font-sans text-slate-800 bg-slate-50 selection:bg-blue-200'
        ], 'landing_layout');
    }

    public function showRiset(): void
    {
        $this->render('landingpage/riset', [
            'bodyClass' => 'bg-gray-50 text-slate-800 antialiased selection:bg-primary selection:text-white'
        ], 'landing_layout');
    }
}
