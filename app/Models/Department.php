<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;


class Department extends Model {

    use SluggableScopeHelpers;
    use Sluggable;

    protected $table = "departments";

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'           
            ]
        ];
    }
    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    protected $fillable = [
        'title','code','slug','phone','contact','email','address','user_id','author_id','nursing_id','image',
    ];


    public function users(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function ballots(){
        return $this->hasMany('App\Models\EquipmentBallot','department_id','id');
    }

    public function supplieBallots(){
        return $this->hasMany('App\Models\SupplieBallot','department_id','id');
    }

    public function department_users(){
        return $this->belongsTo('App\Models\User','nursing_id','id');
    }

    public function department_equipment(){
        return $this->hasMany('App\Models\Equipment','department_id','id');
    }

    public function department_user(){
        return $this->hasMany('App\Models\User');
    }

    public function department_transfer(){
        return $this->hasMany('App\Models\Transfer','department_id','id');
    }

}
