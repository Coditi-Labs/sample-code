<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Appointment extends Model
{
    use HasFactory, SoftDeletes, Billable;
    protected $table = "tbappointments";
    protected $fillable = ["user_id", "added_by", "uid", "start_date", "end_date", 'make_id', 'make_model_id', 'model_year_id', 'vehicle_type_id', 'steps', 'status', 'payment_collect', 'is_payment', 'payment_collected_date', 'is_new_customer', 'amount', "type", 'is_term_condition', 'stripe_id'];

    /**
     * join with user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * join with shop owner
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * join with vehicle make
     */

    public function make()
    {
        return $this->belongsTo(Make::class);
    }

    /**
     * join with vehicle model
     */

    public function makeModel()
    {
        return $this->belongsTo(VehicleModel::class);
    }
    /**
     * join with vehicle year
     */
    public function modelYear()
    {
        return $this->belongsTo(ModelYear::class);
    }
    /**
     * join with vehicle type
     */

    public function vehicle()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }
    /**
     * join with packages
     */
    public function appointmentPackage()
    {
        return $this->hasMany(AppointmentPackage::class, 'tbappointment_id');
    }
    /**
     * join with film type
     */
    public function appointmentFilm()
    {
        return $this->hasMany(AppointmentFilm::class, 'tbappointment_id');
    }

    /**
     * join with services
     */


    public function service()
    {
        return $this->belongsToMany(Service::class, "tbappointment_packages", "tbappointment_id", "service_id", "id", "id");
    }
    /**
     * join with customer
     */

    public function customer()
    {
        return $this->hasMany(AppointmentCustomer::class);
    }

    public function customerInfo()
    {
        return $this->belongsToMany(User::class, 'appointment_customers', 'appointment_id', 'customer_id');
    }

    /**
     * join with technician 
     */

    public function assignTech()
    {
        return $this->hasMany(AppointmentTechAssign::class);
    }

    /**
     * join with service technican
     */

    public function assignServiceTech()
    {
        return $this->belongsToMany(User::class, 'tbappointment_tech_assigns', 'appointment_id', 'tech_id', "id");
    }

    /**
     * get one techincian
     */

    public function assignTechOne()
    {
        return $this->hasOne(AppointmentTechAssign::class);
    }

    /**
     * create scope for get price of appointment
     */

    public function scopeGetPriceByAppointment($query, $params)
    {
        $query->with(['assignTech', 'user.userProfile', 'customerInfo.userProfile', 'make', 'makeModel', 'modelYear', 'vehicle', 'appointmentPackage.package' => function ($q) use ($params) {

            return $q->withMin([
                'vehicleTypePrice' => function ($q) use ($params) {
                    $q->where('vehicle_id', $params['vehicleType']);
                    $q->orWhereIn('vehicle_id', $params['year_type_id']);
                    return $q;
                }
            ], 'price');
        }, 'appointmentFilm' => function ($q) use ($params) {
            $q->with(['packageFilm' => function ($s) use ($params) {
                $s->withMin(['price' => function ($q) use ($params) {
                    $q->where('vehicle_id', $params['vehicleType']);
                    $q->orWhereIn('vehicle_id', $params['year_type_id']);
                    return $q;
                }], 'price');
                $s->withMax(['price' => function ($q) use ($params) {
                    $q->where('vehicle_id', $params['vehicleType']);
                    $q->orWhereIn('vehicle_id', $params['year_type_id']);
                    return $q;
                }], 'price');
                $s->withSum(['price' => function ($q) use ($params) {
                    $q->where('vehicle_id', $params['vehicleType']);
                    $q->orWhereIn('vehicle_id', $params['year_type_id']);
                    return $q;
                }], 'price');

                $s->withSum(['changedPrice' => function ($query) use ($params) {
                    return $query->whereHas('filmVehicleType', function ($s) use ($params) {
                        $s->where('vehicle_id', $params['vehicleType']);
                        $s->orWhereIn('vehicle_id', $params['year_type_id']);
                        return $s;
                    });
                }], 'price')
                    ->withMin(['changedPrice' => function ($query) use ($params) {
                        return $query->whereHas('filmVehicleType', function ($s) use ($params) {
                            $s->where('vehicle_id', $params['vehicleType']);
                            $s->orWhereIn('vehicle_id', $params['year_type_id']);
                            return $s;
                        });
                    }], 'price')
                    ->withMax(['changedPrice' => function ($query) use ($params) {
                        return $query->whereHas('filmVehicleType', function ($s) use ($params) {
                            $s->where('vehicle_id', $params['vehicleType']);
                            $s->orWhereIn('vehicle_id', $params['year_type_id']);
                            return $s;
                        });
                    }], 'price');

                return $s;
            }]);
            return $q;
        }]);
    }

    /**
     * create scope for notify shop owner
     */


    public function scopeNotifyShopOwner($q)
    {
        $q->select("id", "user_id", "added_by", "start_date", "end_date", 'make_id', 'make_model_id', 'model_year_id', 'vehicle_type_id')->with([
            "appointmentFilm" => function ($film) {
                $film->with(["packageFilm" => function ($q) {
                    return $q->select("id", "film_type_name");
                }]);

                return $film->select("tbappointment_id", "package_film_id");
            },
            "appointmentPackage" => function ($pack) {
                $pack->with(["package" => function ($q) {
                    return $q->select("id", "name");
                }]);

                $pack->with(["service" => function ($q) {
                    return $q->select("id", "name");
                }]);

                return $pack->select("package_id", "service_id", "tbappointment_id");
            },
            "make" => function ($q) {
                return $q->select("id", "make");
            },
            "makeModel" => function ($q) {
                return $q->select("id", "model");
            },
            "modelYear" => function ($q) {
                return $q->select("id", "year");
            },
            "vehicle" => function ($q) {
                return $q->select("id", "type");
            },
            'user' => function ($q) {
                $q->with(["userProfile" => function ($q) {
                    return $q->select("user_id", "name", "phone");
                }]);
                return $q->select("id", "email");
            },
            'owner' => function ($q) {
                $q->with(["userProfile" => function ($q) {
                    return $q->select("user_id", "name", "phone");
                }]);
                return $q->select("id", "email");
            }
        ]);
    }
}
