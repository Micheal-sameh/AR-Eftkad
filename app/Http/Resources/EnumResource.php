<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EnumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    protected $enumClass;

    public function __construct($resource, $enumClass)
    {
        parent::__construct($resource);
        $this->enumClass = $enumClass;
    }

    public function toArray($request)
    {
        return [
            'value' => $this->resource,
            'name' => $this->enumClass::getStringValue($this->resource),
        ];
    }
}
