<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResignationResource extends JsonResource
{
    public $resource;
    public $statusCode;
    public $message;

    public function __construct($message, $resource, $statusCode)
    {
        parent::__construct($resource);

        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    public function toArray(Request $request): array
    {
        return [
            'message' => $this->message,
            'status' => $this->statusCode,
            'data' => $this->resource,
        ];
    }
}
