<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Repository\SettingRepository;
use App\Traits\ResponseHandler;

class SettingController extends BaseController
{
    use ResponseHandler;

    public function __construct(protected SettingRepository $settingRepository) {}

    public function enums()
    {
        $enums = $this->settingRepository->enums();

        return $this->apiResponse($enums);
    }

    public function filters()
    {
        $filters = $this->settingRepository->filters();

        return $this->apiResponse($filters);
    }
}
