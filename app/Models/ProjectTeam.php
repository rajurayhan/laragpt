<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'transcriptId',
        'employeeRoleId',
        'associateId',
    ];

    public function employeeRoleInfo()
    {
        return $this->belongsTo(EmployeeRoles::class, 'employeeRoleId', 'id');
    }
    public function associate()
    {
        return $this->belongsTo(Associate::class, 'associateId', 'id');
    }
}
