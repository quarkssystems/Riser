<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use williamcruzme\FCM\Traits\ManageDevices;

class DeviceController extends Controller
{
    use ManageDevices;

    /**
     * Store Device Token
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 28/07/2022
     */
    /**
     * @OA\Post(
     *      path="/api/v1/devices",
     *      operationId="store",
     *      tags={"User"},
     *      summary="Store Device token for user",
     *      description="Store Device token for user",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="token",
     *          description="Device token",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * 
     */
    public function store(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $device = $this->guard()->user()->devices()->whereToken($request->token)->first();

        if ($device) {
            $device->touch();
        } else {
            $device = $this->guard()->user()->devices()->create($request->all());
        }

        return $this->sendResponse($device);
    }

    /**
     * Delete Device Token
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 28/07/2022
     */
    /**
     * @OA\Delete(
     *      path="/api/v1/devices",
     *      operationId="destroy",
     *      tags={"User"},
     *      summary="Delete Device token for user",
     *      description="Delete Device token for user",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="token",
     *          description="Device token",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * 
     */
    public function destroy(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'token' => 'required|string|exists:devices,token',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $model = $this->guard()->user()->devices()->firstWhere('token', $request->token);

        optional($model)->delete();

        return $this->sendDestroyResponse($model);
    }

    /**
     * Get the validation rules that apply to the create a device.
     *
     * @return array
     */
    protected function createRules()
    {
        return [
            'token' => ['required', 'string'],
        ];
    }

    /**
     * Get the validation rules that apply to the delete a device.
     *
     * @return array
     */
    protected function deleteRules()
    {
        return [
            'token' => ['required', 'string', 'exists:devices,token'],
        ];
    }

    /**
     * Get the device management validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [];
    }

    /**
     * Get the response for a successful storing device.
     *
     * @param  Williamcruzme\Fcm\Device  $model
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResponse($model)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $response['data'] = $model;
        return response()->json($response);
    }

    /**
     * Get the response for a successful deleting device.
     *
     * @param  Williamcruzme\Fcm\Device  $model
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendDestroyResponse($model)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        return response()->json($response, 200);
    }
}
