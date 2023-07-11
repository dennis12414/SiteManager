<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Bus\DispatchesJobs;
/**
 * @OA\Info(
 *    title="Site Manager API",
 *    description="Site Manager API Documentation",
 *    version="1.0.0",
 *    @OA\Contact(
 *     email="ndungudennis250@gmail.com",
 *   )
 * )
 */
class Controller extends BaseController
{

    use AuthorizesRequests,DispatchesJobs, ValidatesRequests;
}

