<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'gender_label' => $this->getGenderLabel(),
            'email' => $this->email,
            'tel' => $this->tel,
            'category' => [
                'id' => $this->category->id,
                'content' => $this->category->content,
            ],
            'tag' => [
                'id' => $this->tags_id,
                'name' => $this->tag_name,
            ],
            'address' => $this->address,
            'building' => $this->building,
            'detail' => $this->detail,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    private function getGenderLabel()
    {
        $labels = [
            1 => '男性',
            2 => '女性',
            3 => 'その他'
        ];

        if (isset($labels[$this->gender])) {
            return $labels[$this->gender];
        }

        return $this->gender;
    }
}
