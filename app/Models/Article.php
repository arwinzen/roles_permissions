<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'full_text', 'category_id', 'user_id', 'published_at'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // scope applies only if user is logged in and is an admin
        if(auth()->check() && !auth()->user()->is_admin && !auth()->user()->is_publisher){
            static::addGlobalScope('user', function (Builder $builder) {
                // check for organization_id on authenticated user, else use user_id in the scope
                $organizationId = auth()->user()->organization_id ?? auth()->id();
                $builder->where('user_id', $organizationId);
            });
        }
    }
}
