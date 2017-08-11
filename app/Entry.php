<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $table = 'entries';

    /**
     * Get the account record associated with the entry.
     */
    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    public function getPost()
    {
        if (is_null($this->post)) {
            return;
        }
        return $this->post;
    }

    public function getTitle()
    {
        if (is_null($this->title)) {
            return;
        }
        return $this->title;
    }
}
