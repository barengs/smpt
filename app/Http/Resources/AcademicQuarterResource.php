<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AcademicQuarterResource extends JsonResource
{
    private $message;
    private $status;

    public function __construct($message = "Success", $resource = [], $status = 200)
    {
        parent::__construct($resource);
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => 'success',
            'message' => $this->message,
            'data' => $this->resource
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->status);
    }
}
