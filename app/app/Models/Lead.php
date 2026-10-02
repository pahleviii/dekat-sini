<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Lead extends Model
{
    protected $fillable = ['service_id','user_id','channel','message'];
    public $timestamps = true;
    const UPDATED_AT = null;
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
}
