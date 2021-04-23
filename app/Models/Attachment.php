<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model {

    protected $table = "attachments";

    protected $fillable = [
        'id', 'equipment_id', 'media_id'
    ];

    /**
     * Get the euipment of maintenance
     */
    public function equipment(){
        return $this->belongsTo('App\Models\Equipment', 'equipment_id', 'id');
    }

    /**
     * Get the author of maintenance
     */
    public function media(){
        return $this->belongsTo('App\Models\Media', 'media_id', 'id');
    }
}