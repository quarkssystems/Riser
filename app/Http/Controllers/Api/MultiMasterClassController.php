<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\MasterClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MultiMasterClassController extends Controller
{
    /**
    * Create Master Class
    *
    * Developed By : Amish Soni
    * Developed On : 08/11/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v2/create-master-class",
    *      operationId="createMultiMasterClass",
    *      tags={"Master Class"},
    *      summary="Create Master Class",
    *      description="Create Master Class API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="multipart/form-data",
    *           @OA\Schema(
    *               required={"title", "category_id", "class_type", "class_dates", "amount", "banner_image"},
    *               @OA\Property(
    *                   property="title",
    *                   type="string",
    *                   example="This is title for master class"
    *               ),
    *               @OA\Property(
    *                   property="category_id",
    *                   description="Category IDs if multiple send with comma seperated",
    *                   example="1,2",
    *                   type="string"
    *               ),
    *               @OA\Property(
    *                   property="class_type",
    *                   description="Master class type",
    *                   type="string",
    *                   default="single",
    *                   enum={"single", "multi"}
    *               ),
    *               @OA\Property(
    *                   property="total_days",
    *                   description="Master class days if mulitple selected",
    *                   type="integer",
    *               ),
    *               @OA\Property(
    *                   property="class_dates",
    *                   description="Master class dates",
    *                   type="array",
    *                   @OA\Items(
    *                      type="object",
    *                      example={"start_date": "2022-07-05", "start_time": "01:30 PM", "duration": "30"},
    *                  ),
    *               ),
    *               @OA\Property(
    *                   property="amount",
    *                   type="number",
    *                   format="double",
    *                   example="1000"
    *               ),
    *               @OA\Property(
    *                   property="banner_image",
    *                   type="file",
    *               ),
    *           ),
    *       ),
    *   ),
    *
    * @OA\Response(
    *    response=200,
    *    description="Success"
    *     ),
    * @OA\Response(
    *    response=401,
    *    description="Returns when user is not authenticated",
    *    @OA\JsonContent(
    *       @OA\Property(property="message", type="string", example="Not authorized"),
    *    )
    * ),
    * @OA\Response(response=400, description="Bad request"),
    * )
    */
    public function createMultiMasterClass(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $fileName = null;

        $validator = Validator::make($request->all(), [
            // 'banner_image' => 'required|image|max:5120',
            'title'        => 'required|string|max:255',
            'category_id'  => 'required|string',
            'class_type'  => 'required|in:single,multi',
            'class_dates'   => 'required',
            'total_days'   => 'required_if:class_type,multi|integer|nullable',
            // 'start_date'   => 'required|date_format:Y-m-d|after_or_equal:today',
            // 'start_time'   => 'required|date_format:h:i A',
            'amount'       => 'required|gte:1|regex:/^\d{0,10}(\.\d{1,2})?$/',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }
dd($request->class_dates);
        //validation for past date & time
        $todayDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d h:i A');
        $bookingDate = $request->start_date;
        $bookingTime = $request->start_time;
        $bookingDateTime = $bookingDate.' '.$bookingTime;
        $date1 = Carbon::createFromFormat('Y-m-d h:i A', $bookingDateTime);
        $date2 = Carbon::createFromFormat('Y-m-d h:i A', $todayDateTime);

        if ($date1->lt($date2)) {
            $response['status'] = false;
            $response['message'] = trans('messages.after_date',['date' => $date2->format('d-m-Y h:i A')]);

            return response($response, 200);
        }

        $request->start_time = Carbon::parse($request->start_time)->format('H:i');
        $duration = $request->duration ?? 30;

        if (empty(auth()->user()->vEmail) || (!filter_var(auth()->user()->vEmail, FILTER_VALIDATE_EMAIL))){
            $response['status'] = false;
            $response['message'] = trans('messages.invalid_email');
            return response($response);
        }

        try {
            if($request->hasFile('banner_image')){
                $fileName = storeFile('master-class', $request->banner_image);
            }
            $masterClassData = new MasterClass();
            $masterClassData->user_id = auth()->user()->iUserId;
            $masterClassData->title = $request->title;
            $masterClassData->banner_image = $fileName;
            $masterClassData->start_date = $request->start_date;
            $masterClassData->start_time = $request->start_time;
            $masterClassData->amount = $request->amount;
            $masterClassData->duration = $duration;
            $masterClassData->save();

            if($request->filled('category_id')){
                $categoryIds = explode(',', $request->category_id);
                $masterClassData->categories()->sync($categoryIds);
            }

            $response['data'] = $masterClassData;
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }

        return response($response);
    }
}
