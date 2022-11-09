<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'notifiable_type' => $this->notifiable_type,
            'notifiable_id' => $this->notifiable_id,
            'data' => $this->data,
            'read_at' => $this->read_at,
            'created_at' => Carbon::createFromFormat('Y-m-d H:i:s',$this->created_at)->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s',$this->updated_at)->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s'),
        ];
    }
}
