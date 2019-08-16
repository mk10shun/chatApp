<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = [];

    public function scopeBetweenTwo($query, $id)
    {
        return $query->where(function($q) use ($id){
                          $q->where('from', auth()->id())
                            ->where('to', $id);
                        })->orWhere(function($p) use ($id){
                          $p->where('to', auth()->id())
                          ->where('from', $id);
                        });
    }
}
