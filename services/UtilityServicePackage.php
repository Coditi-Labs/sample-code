<?php
namespace App\Services;

use App\Models\FilmVehicleType;
use App\Models\Package;
use App\Models\Service;

class UtilityServicePackage{
    /**
     * get  service 
     * use calculatePrice scope 
     * @param [type] $userId
     * @param [type] $params
     * @return void
     */
    public function getService($userId, $params){
        $services =  Service::calculatePrice($params)->where("user_id", $userId)
            ->orderBy("service_order","ASC")
            ->get();

        return $services;
    }
    /**
     * get  package 
     * use calculatePackage scope 
     * @param [type] $params
     * @return void
     */

    public function getPackage($params){
        $packages = Package::select('id', 'name', 'add_base_price_year')->calculatePackage($params)->whereIn('id', $params['appointmentPackage'])->get();
        return $packages;
    }

    /**
     * get appointment review calculcation
     */

    public function getAppointmentCalculation($appointment,$diffYears){
        $appointmentReview = [];
        $total = 0;

        $count = 0;
        $techs = $appointment->assignTech;
        foreach ($techs as $key => $value) {
            $appointmentReview[$value->package_id]['position'] = $value->id;
        }

        if (!empty($appointment->appointmentPackage)) {
            foreach ($appointment->appointmentPackage as $key => $val) {
                
                $appointmentReview[$val->package->id]['appointment_package_id'] = $val->id;
                $appointmentReview[$val->package->id]['name'] = $val->package->name;
                $appointmentReview[$val->package->id]['add_base_price_year'] = $val->package->add_base_price_year;
                $appointmentReview[$val->package->id]['vehicle_type_price_min_price'] = $val->package->vehicle_type_price_min_price;
                $appointmentReview[$val->package->id]['vehicle_type_duration'] =$val?->package?->vehicleTypeOneDuration?->duration;
                
                if($val->package->is_film_type == 2){
                    $vehicleDetail = $val->package->vehicleTypePrice->where("vehicle_id",$appointment->vehicle_type_id)->first();
                    if($vehicleDetail){
                        $appointmentReview[$val->package->id]['duration'][] = $vehicleDetail->duration;
                        $appointmentReview[$val->package->id]['min'][] = $vehicleDetail->price;
                        $appointmentReview[$val->package->id]['filmCount'][] = $count ++;
                        
                        $total += $vehicleDetail->price;
                    }
                }
            }
        }

        if (!empty($appointment->appointmentFilm)) {
            foreach ($appointment->appointmentFilm as $key => $value) {

                $appointmentReview[$value->package->id]['appointment_film_id'][] = $value->id;

                $appointmentReview[$value->package_id]['filmName'][] = $value->packageFilm->film_type_name;
                $appointmentReview[$value->package_id]['duration'][] = $value->packageFilm->all_vehicle_duration ??  ($value ? ($value->vtypePrice ? $value->vtypePrice->duration : "") : "");
                $appointmentReview[$value->package_id]['filmCount'][] = $count ++ ;
                

                $appointmentReview[$value->package_id]['min'][] = !empty($appointmentReview[$value->package_id]['add_base_price_year']) ? ($diffYears * $appointmentReview[$value->package_id]['add_base_price_year']) + (!empty($value->packageFilm->changed_price_min_price) ? $value->packageFilm->changed_price_min_price : (!empty($value->packageFilm->price_min_price) ? $value->packageFilm->price_min_price : $value->packageFilm->allVehicle)) : (!empty($value->packageFilm->changed_price_min_price) ? $value->packageFilm->changed_price_min_price : (!empty($value->packageFilm->price_min_price) ? $value->packageFilm->price_min_price : $value->packageFilm->allVehicle));



                $appointmentReview[$value->package_id]['max'][] = !empty($appointmentReview[$value->package_id]['add_base_price_year']) ? ($diffYears * $appointmentReview[$value->package_id]['add_base_price_year']) + (!empty($value->packageFilm->changed_price_max_price) ? $value->packageFilm->changed_price_max_price : (!empty($value->packageFilm->price_max_price) ? $value->packageFilm->price_max_price : $value->packageFilm->allVehicle)) : (!empty($value->packageFilm->changed_price_max_price) ? $value->packageFilm->changed_price_max_price : (!empty($value->packageFilm->price_max_price) ? $value->packageFilm->price_max_price : $value->packageFilm->allVehicle));

                // $appointmentReview[$value->package_id]['vehicle_all_min'][] = !empty($value->packageFilm->allVehicle) ? $value->packageFilm->allVehicle : 0;
                // $appointmentReview[$value->package_id]['vehicle_all_max'][] = !empty($value->packageFilm->allVehicle) ? $value->packageFilm->allVehicle : 0;

                if (!empty($value->packageFilm->allVehicle)) {
                    $total = $total + $value->packageFilm->allVehicle;
                }
                if (!empty($value->packageFilm->changed_price_sum_price)) {
                    $total = $total + ($value->packageFilm->changed_price_sum_price);
                } else {
                    $total = $total + $value->packageFilm->price_sum_price;
                }

                if (!empty($appointmentReview[$value->package_id]['add_base_price_year'])) {

                    $total = $total + ($appointmentReview[$value->package_id]['add_base_price_year'] * $diffYears);
                }
            }
        }

       

        return ["appointmentReview"=>$appointmentReview,"total"=>$total,"count"=>$count];
    }

    /**
     * Calculate  duration
     *
     * @param [array] $appointment
     * @return void
     */
    public function calculateDuration($appointment){
        try {

            $durations= [];
            if (!empty($appointment)) {
                if(count($appointment->appointmentFilm) > 0){

                    foreach ($appointment->appointmentFilm as $film) {
                        $durations[$film->tbappointment_id][] = $film->packageFilm->all_vehicle_duration ?? ($film ? ($film->vtypePrice ? $film->vtypePrice->duration : "") : "");
                    }
                }

                if($appointment->appointmentPackage){

                    $package = $appointment->appointmentPackage()->pluck("package_id");
                    $types = FilmVehicleType::where(["vehicle_id"=>$appointment->vehicle_type_id])->whereIn("package_id",$package)->where("film_id",null)->get();
                    foreach ($types as $type) {
                        $durations[$appointment->id][] = $type->duration ?? "";
                    }
                }
            }

            $convert = $this->calculateEndTime($appointment,$durations);
            $seconds = $convert[$appointment->id];

            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $remainingSeconds = $seconds % 60;

            $time = sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
            return $time;
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    /**
     * Calculate the end time of service duration
     *
     * @param [Object] $appointment
     * @param [arr] $timeArray
     * @return void
     */
    private function calculateEndTime($appointment,$timeArray){
        $time = [];
        $totalSeconds = [];
        
        $totalSecond = 0;
        if (in_array($appointment->id, array_keys($timeArray))) {
            foreach ($timeArray[$appointment->id] as $time) {
                if ($time) {
                    list($hrs, $min, $sec) = explode(":", $time);
                    $totalSecond += $hrs * 3600 + $min * 60 + $sec; // convert into seconds
                }
            }
        }
        $totalSeconds[$appointment->id] = $totalSecond;
        
        return $totalSeconds;
    }
    
}

?>