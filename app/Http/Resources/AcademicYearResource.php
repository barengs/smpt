<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearResource extends JsonResource
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
     * @param mixed $message [explicite description]
     * @param mixed $resource [explicite description]
     * @param mixed $statusCode [explicite description]
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
            'success' => $this->statusCode >= 200 && $this->statusCode < 300,
            'message' => $this->message,
            'data' => $this->resource,
        ];
    }

    /**
     * Customize the outgoing response for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->statusCode);
    }
}
