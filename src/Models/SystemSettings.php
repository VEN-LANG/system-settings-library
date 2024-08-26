<?php

namespace Venom\SystemSettings\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSettings extends Model
{
    protected $fillable = ['key', 'type', 'value'];

    // Accessor for converting JSON strings to arrays
    public function getValueAttribute($value)
    {
        return $this->type === 'json' ? json_decode($value, true) : $value;
    }

    // Mutator for converting arrays to JSON strings
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $this->type === 'json' ? json_encode($value) : $value;
    }

    public function hasKey($key){
        return $this->where('key', $key)->count() > 0;
    }
}