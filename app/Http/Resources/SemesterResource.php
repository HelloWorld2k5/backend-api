<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SemesterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'semester_id'   => $this->semester_id,
            'semester_name' => $this->semester_name,
            'start_date'    => $this->start_date,
            'end_date'      => $this->end_date,
            'year_id'       => $this->year_id,
            'year_name'     => $this->academicYear?->year_name,
            'start_year'    => $this->academicYear?->start_year,
            'end_year'      => $this->academicYear?->end_year,
        ];
    }
}

