<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Service extends Model
{
    protected $fillable = ['user_id','category_id','title','slug','description','whatsapp','phone','address','city','latitude','longitude','price_from','status','is_verified','views_count'];
    protected $casts = ['latitude' => 'decimal:7','longitude' => 'decimal:7','is_verified' => 'boolean'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function photos(): HasMany { return $this->hasMany(ServicePhoto::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function leads(): HasMany { return $this->hasMany(Lead::class); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}
