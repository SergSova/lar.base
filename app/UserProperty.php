<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserProperty
 *
 * @package App
 * @property integer id
 * @property integer user_id
 * @property string  sex
 * @property string  phones
 * @property string  birthday
 * @property string  status
 */
class UserProperty extends Model
{
    const ACTIVE = 'active';
    const BLOCKED = 'blocked';

    protected $fillable
        = [
            'user_id',
            'sex',
            'phones',
            'birthday',
            'status',
        ];

    protected $dates = ['birthday'];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function getStatusAttribute($key)
    {
        return $key ?? 'active';
    }

    public function getExStatusAttribute()
    {
        return $this->isActive() ? self::BLOCKED : self::ACTIVE;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status == self::ACTIVE;
    }

}
