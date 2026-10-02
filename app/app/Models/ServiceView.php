<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ServiceView extends Model
{
    protected $fillable = ['service_id','viewed_at','ip_hash'];
}
