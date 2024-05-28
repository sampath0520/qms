<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Helpers\ResponseHelper;
use App\Http\Requests\ActivateDeactivateUser;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * Create user
     */

    public function createUser(CreateUserRequest $request)
    {
        $validated = $request->validated();
        $validated['auth_type'] = AppConstants::CREDENTIALS;
        $validated['role'] = AppConstants::RECEPTIONIST;
        $addUser = $this->userService->addUser($validated);
        if ($addUser['status']) {
            return ResponseHelper::success($addUser['message'], $addUser['data']);
        } else {
            return ResponseHelper::error($addUser['message']);
        }
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * Update user
     */

    public function updateUser(UpdateUserRequest $request)
    {
        $validated = $request->validated();
        $updateUser = $this->userService->updateUser($validated);
        if ($updateUser['status']) {
            return ResponseHelper::success($updateUser['message'], $updateUser['data']);
        } else {
            return ResponseHelper::error($updateUser['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *
     * delete a user
     */

    public function deleteUser($id)
    {
        $deleteUser = $this->userService->deleteUser($id);
        if ($deleteUser['status']) {
            return ResponseHelper::success($deleteUser['message'], $deleteUser['data']);
        } else {
            return ResponseHelper::error($deleteUser['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *
     * get all users
     */

    public function listUser()
    {
        $listUser = $this->userService->listUser();
        if ($listUser['status']) {
            return ResponseHelper::success($listUser['message'], $listUser['data']);
        } else {
            return ResponseHelper::error($listUser['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *
     * activate/deactivate user
     */

    public function activateDeactivateUser(ActivateDeactivateUser $request)
    {
        $validated = $request->validated();
        $activateDeactivateUser = $this->userService->activateDeactivateUser($validated);
        if ($activateDeactivateUser['status']) {
            return ResponseHelper::success($activateDeactivateUser['message'], $activateDeactivateUser['data']);
        } else {
            return ResponseHelper::error($activateDeactivateUser['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *
     * mobile users delete request
     */

    public function deleteRequest()
    {
        $deleteRequest = $this->userService->deleteRequest();
        if ($deleteRequest['status']) {
            return ResponseHelper::success($deleteRequest['message'], $deleteRequest['data']);
        } else {
            return ResponseHelper::error($deleteRequest['message']);
        }
    }

    //profile-settings
    public function profileSettings()
    {
        $profileSettings = $this->userService->profileSettings();
        if ($profileSettings['status']) {
            return ResponseHelper::success($profileSettings['message'], $profileSettings['data']);
        } else {
            return ResponseHelper::error($profileSettings['message']);
        }
    }

    //userDetails
    public function userDetails()
    {
        $userDetails = $this->userService->userDetails();
        if ($userDetails['status']) {
            return ResponseHelper::success($userDetails['message'], $userDetails['data']);
        } else {
            return ResponseHelper::error($userDetails['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * User Check
     */

    public function userCheck()
    {
        $userDetails = $this->userService->userCheck();
        if ($userDetails) {
            return ResponseHelper::success(trans('messages.record_fetched'), $userDetails);
        } else {
            return ResponseHelper::error(trans('messages.record_fetch_failed'));
        }
    }
}
