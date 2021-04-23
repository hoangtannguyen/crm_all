<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Spatie\Activitylog\Traits\LogsActivity;

class Equipment extends Model {
    use SluggableScopeHelpers;
    use Sluggable;
    use LogsActivity;

    protected $table = "equipments";

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
        'title',
        'slug',
        'code',
        'model',
        'warehouse',
        'year_manufacture',
        'serial',
        'status',
        'risk',
        'amount',
        'manufacturer',
        'origin',
        'maintenance_id',
        'provider_id',
        'repair_id','user_id',
        'cate_id',
        'devices_id',
        'unit_id',
        'department_id',
        'image',
        'first_inspection',
        'specificat',
        'first_value',
        'process',
        'year_use',
        'officer_charge_id',
        'officers_use_id',
        'first_information',
        'import_price',
        'bid_project_id',
        'warranty_date',
        'configurat',
        'depreciat',
        'note',
        'officer_department_charge_id',
        'officers_training_id',
        'supplie_id',
        'regular_inspection',
        'date_failure',
        'reason',
        'date_delivery',
        'liquidation_date',
        'date_person_id',
        'update_day'
    ];

    protected static $logAttributes = ['title','status','type','code','department_id', 'date_failure','reason','liquidation_date'];


    /*public function scopeDevice($query) {
        return $query->where('type', 'devices');
    }
*/
    public function scopeSupplie($query) {
        return $query->where('type', 'supplies');
    }

    public function equipment_provider(){
        return $this->belongsTo('App\Models\Provider','provider_id','id');
    }

    public function equipment_maintenance(){
        return $this->belongsTo('App\Models\Provider','maintenance_id','id');
    }

    public function equipment_repair(){
        return $this->belongsTo('App\Models\Provider','repair_id','id');
    }

    public function equipment_user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function equipment_unit(){
        return $this->belongsTo('App\Models\Unit','unit_id','id');
    }

    public function equipment_department(){
        return $this->belongsTo('App\Models\Department','department_id','id');
    }

    public function equipment_cates(){
        return $this->belongsTo('App\Models\Cates','cate_id','id');
    }


    public function equipment_user_charge(){
        return $this->belongsTo('App\Models\User','officer_charge_id','id');
    }

    public function equipment_user_use(){
        return $this->belongsToMany('App\Models\User','equipment_user_use','equipment_id','user_id');
    }
    
    public function equipment_user_department_charge(){
        return $this->belongsTo('App\Models\User','officer_department_charge_id','id');
    }

    public function equipment_user_training(){
        return $this->belongsToMany('App\Models\User','equipment_user_training','equipment_id','user_id');
    }

    public function equipment_supplie(){
        return $this->belongsTo('App\Models\Supplie','supplie_id','id');
    }
    public function equipment_device(){
        return $this->belongsTo('App\Models\Device','devices_id','id');
    }

    public function equipment_transfer(){
        return $this->hasMany('App\Models\Transfer','equipment_id','id')->latest();
    }

    public function device_supplies(){
        return $this->belongsToMany('App\Models\Eqsupplie','supplies_devices','device_id','supplie_id')->withPivot('amount','date_delivery','note','user_id','created_at');
    }

    /**
     * Get all maintenances of equipment
     */
    public function maintenances() {
        return $this->hasMany('App\Models\Maintenance','equipment_id', 'id')->latest();
    }
    public function project(){
        return $this->belongsTo('App\Models\Project','bid_project_id','id');
    }

    /**
     * Get all of the attachments for the equipment.
     */
    public function attachments() {
        return $this->morphToMany('App\Models\Media','mediable')->wherePivot('type','attach')->withPivot('type');
    }
    public function hand_over() {
        return $this->morphToMany('App\Models\Media','mediable')->wherePivot('type','hand_over')->withPivot('type');
    }
    public function was_broken() {
        return $this->morphToMany('App\Models\Media','mediable')->wherePivot('type','was_broken')->withPivot('type');
    }
    public function schedule_repairs(){
        return $this->hasMany('App\Models\ScheduleRepair','equipment_id','id')->latest();
    }
    public function liquidations(){
        return $this->hasMany('App\Models\Liquidation','equipment_id','id');
    }
    public function guarantees(){
        return $this->hasMany('App\Models\Guarantee','equipment_id','id')->latest();
    }
    public function accres(){
        return $this->hasMany('App\Models\Accre','equipment_id','id')->latest();
    }
    public function ballots(){
        return $this->belongsToMany('App\Models\EquipmentBallot','ballots_equipments','equipment_id','ballot_id')->withPivot('amount','unit_price');
    }


}
