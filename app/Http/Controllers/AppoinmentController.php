<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\OngoingTokenRequest;
use App\Models\Schedule;
use App\Services\PatientService;
use Illuminate\Http\Request;

class AppoinmentController extends Controller
{
    protected $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * View Appoinments
     */

    public function viewAppoinments()
    {
        $viewAppoinments = $this->patientService->viewAppoinments();
        if ($viewAppoinments['status']) {
            return ResponseHelper::success($viewAppoinments['message'], $viewAppoinments['data']);
        } else {
            return ResponseHelper::error($viewAppoinments['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * ongoing Token
     */

    public function ongoingToken(OngoingTokenRequest $request)
    {
        $validated = $request->validated();

        $ongoingToken = $this->patientService->ongoingToken($validated);
        if ($ongoingToken['status']) {
            return ResponseHelper::success($ongoingToken['message'], $ongoingToken['data']);
        } else {
            return ResponseHelper::error($ongoingToken['message']);
        }
    }
}
