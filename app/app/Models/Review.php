<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Review extends Model
{
    protected $fillable = ['service_id','user_id','rating','comment','is_verified'];
    protected $casts = ['is_verified' => 'boolean'];
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
}
