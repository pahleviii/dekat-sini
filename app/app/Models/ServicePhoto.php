<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ServicePhoto extends Model
{
    protected $fillable = ['service_id','path','is_cover'];
    protected $casts = ['is_cover' => 'boolean'];
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
}
