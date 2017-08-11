<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the account record associated with the debt.
     */
    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    /**
     * Get the contact record associated with the debt.
     */
    public function contacts()
    {
        return $this->belongsToMany('App\Contact')->withPivot('company_id')->withTimestamps();
    }
}
