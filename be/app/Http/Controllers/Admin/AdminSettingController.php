<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Interfaces\data\SiteSettingInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function __construct(
        protected SiteSettingInterface $settingRepo
    ) {}

    /**
     * GET /admin/settings
     * Return all site configuration key-value pairs.
     */
    public function index(): JsonResponse
    {
        $settings = $this->settingRepo->all();

        return $this->successResponse($settings, 'Settings retrieved successfully');
    }

    /**
     * PUT /admin/settings
     * Update one or more site configuration key-value pairs.
     *
     * Request body: { "key": "value", ... }
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            '*' => 'present',
        ]);

        $updated = [];
        $failed  = [];

        foreach ($request->all() as $key => $value) {
            if (!is_string($key) || empty($key)) {
                $failed[] = $key;
                continue;
            }

            $success = $this->settingRepo->set($key, $value);

            if ($success) {
                $updated[] = $key;
            } else {
                $failed[] = $key;
            }
        }

        return $this->successResponse([
            'updated' => $updated,
            'failed'  => $failed,
        ], 'Settings updated successfully');
    }

    /**
     * GET /admin/settings/{key}
     * Return a single setting by its key.
     */
    public function show(string $key): JsonResponse
    {
        $value = $this->settingRepo->get($key);

        if ($value === null) {
            return $this->errorResponse("Setting key '{$key}' not found", 404);
        }

        return $this->successResponse([
            'key'   => $key,
            'value' => $value,
        ], 'Setting retrieved successfully');
    }

    /**
     * PUT /admin/settings/{key}
     * Update a single setting by its key.
     */
    public function updateSingle(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'value' => 'required',
        ]);

        $success = $this->settingRepo->set($key, $request->input('value'));

        if (!$success) {
            return $this->errorResponse("Failed to update setting '{$key}'", 500);
        }

        return $this->successResponse([
            'key'   => $key,
            'value' => $request->input('value'),
        ], 'Setting updated successfully');
    }
}
