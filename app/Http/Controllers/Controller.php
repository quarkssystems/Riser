<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="Riser API",
 *    version="1.0.0",
 *    description="API to interact with Riser",
 *    contact={
 *       "name": "API Support",
 *       }
 * )
 *
 *
 *  @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Dynamic host server"
 *  )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * description="Enter your Bearer Token",
 * type="http",
 * scheme="bearer"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
