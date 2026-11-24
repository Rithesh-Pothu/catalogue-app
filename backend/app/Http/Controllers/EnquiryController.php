<?php

namespace App\Http\Controllers;

use App\Enquiries\Enquiries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function __invoke(Enquiries $enquiries, Request $request, int $id): JsonResponse
    {
        $enquiries->submit($id, $request->all());

        return response()->json(['message' => 'Thank you for your enquiry.'], 201);
    }
}
