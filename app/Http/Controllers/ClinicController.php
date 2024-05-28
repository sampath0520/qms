<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\ActivateDeactivateClinicRequest;
use App\Http\Requests\AddClinicRequest;
use App\Http\Requests\UpdateClinicRequest;
use App\Services\ClinicService;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    protected $clinicService;

    public function __construct(ClinicService $clinicService)
    {
        $this->clinicService = $clinicService;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * Add clinic
     */

    public function addClinic(AddClinicRequest $request)
    {
        $validated = $request->validated();
        $addClinic = $this->clinicService->addClinic($validated);
        if ($addClinic['status']) {
            return ResponseHelper::success($addClinic['message'], $addClinic['data']);
        } else {
            return ResponseHelper::error($addClinic['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * List clinic
     */

    public function listClinic()
    {
        $listClinic = $this->clinicService->listClinic();
        if ($listClinic['status']) {
            return ResponseHelper::success($listClinic['message'], $listClinic['data']);
        } else {
            return ResponseHelper::error($listClinic['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * Update clinic
     */

    public function updateClinic(UpdateClinicRequest $request)
    {
        $validated = $request->validated();
        $updateClinic = $this->clinicService->updateClinic($validated);
        if ($updateClinic['status']) {
            return ResponseHelper::success($updateClinic['message']);
        } else {
            return ResponseHelper::error($updateClinic['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * Delete clinic
     */

    public function deleteClinic($id)
    {
        $deleteClinic = $this->clinicService->deleteClinic($id);
        if ($deleteClinic['status']) {
            return ResponseHelper::success($deleteClinic['message']);
        } else {
            return ResponseHelper::error($deleteClinic['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * Activate/Deactivate clinic
     */

    public function activateDeactivateClinic(ActivateDeactivateClinicRequest $request)
    {
        $validated = $request->validated();
        $activateDeactivateClinic = $this->clinicService->activateDeactivateClinic($validated);
        if ($activateDeactivateClinic['status']) {
            return ResponseHelper::success($activateDeactivateClinic['message']);
        } else {
            return ResponseHelper::error($activateDeactivateClinic['message']);
        }
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * dropdown Clinic
     */

    public function dropdownClinic()
    {
        $dropdownClinic = $this->clinicService->dropdownClinic();
        if ($dropdownClinic['status']) {
            return ResponseHelper::success($dropdownClinic['message'], $dropdownClinic['data']);
        } else {
            return ResponseHelper::error($dropdownClinic['message']);
        }
    }
}
