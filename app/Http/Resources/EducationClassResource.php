<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationClassResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var \Illuminate\Http\Resources\Json\JsonResource
     */
    public $resource;
    public $statusCode;
    public $message;
    /**
     * Create a new resource instance.
     * @param mixed $status [explicite description]
     * @param mixed $message [explicite description]
     * @param mixed $resource [explicite description]
     */
    public function __construct($message, $resource, $statusCode)
    {
        parent::__construct($resource);

        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->message,
            'status' => $this->statusCode,
            'data' => $this->resource,
        ];
    }
}
