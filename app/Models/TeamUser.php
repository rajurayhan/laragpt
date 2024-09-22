<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'team_users';

    protected $fillable = [
        'teamId',
        'userId'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class,'teamId','id');

    }
    public function user()
    {
        return $this->belongsTo(User::class,'userId','id');
    }

}
