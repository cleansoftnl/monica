<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityStatistic extends Model
{
    protected $table = 'activity_statistics';

    /**
     * Get the account record associated with the activity statistic.
     */
    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    /**
     * Get the contact record associated with the activity statistic.
     */
    public function contact()
    {
        return $this->belongsTo('App\Contact');
    }
}
