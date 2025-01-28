<?php

namespace App\Http\Controllers;

use App\Constants\FeedbackMessage;
use App\Facades\Imageproxy;
use Arr;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class GetImageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $query = $request->query();

        throw_if(! Arr::has($query, 'processing_options'), BadRequestException::class, FeedbackMessage::IMGPROXY_PROCESSING_OPTIONS_MISSING->value);
        throw_if(! Arr::has($query, 'source_url'), BadRequestException::class, FeedbackMessage::IMGPROXY_PROCESSING_OPTIONS_MISSING->value);

        return Imageproxy::proxy($query['processing_options'], $query['source_url']);
    }
}
