<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassroomTemplateExport implements WithHeadings
{
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'name',
            'level',
            'educational_institution_id',
        ];
    }
}
