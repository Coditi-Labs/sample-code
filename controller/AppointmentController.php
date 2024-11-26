<?php

namespace App\Http\Controllers\Customer;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\UtilityServicePackage;

use App\Models\User;
use App\Models\Make;
use App\Models\Service;
use App\Models\Proposal;
use App\Models\Package;
use App\Models\ModelYear;
use App\Models\PackageFilm;
use App\Models\VehicleType;
use App\Models\Appointment;
use App\Models\PaymentMode;
use App\Models\UserProfile;
use App\Models\EmailTemplate;
use App\Models\AppointmentFilm;
use App\Models\ModelVehicleType;
use App\Models\AppointmentPackage;
use App\Services\EmailSendService;
use App\Models\AppointmentCustomer;
use App\Models\AppointmentTechAssign;
use App\Http\Controllers\MailController;


class AppointmentController extends Controller
{
    /**
     * Appointment module listing
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $req)
    {
        $startDate = Carbon::tomorrow()->toDateString();
        $endDate = Carbon::now()->endOfWeek(); // End of the current week

        $appointmentsToday = $this->getAppointment($req)->whereDate("start_date", today())->where(["type" => 1])->paginate(20);

        $appointmentsTommorow = $this->getAppointment($req)->whereDate("start_date", today()->addDay(1))->where(["type" => 1])->paginate(20);
        $appointmentsLastWeek = $this->getAppointment($req)->whereDate('start_date', '>=', $startDate)
            ->whereDate('start_date', '<=', $endDate)->where(["type" => 1])->paginate(20);
        $appointmentsCancelled = $this->getAppointment($req)->where("status", 'cancelled')->where(["type" => 1])->paginate(20);


        $filters = Appointment::select('user_id')->with(["user" => function ($q) {
            $q->with(['userProfile' => function ($s) {
                $s->select('user_id', 'name');
                return $s;
            }]);
            return $q->select('id')->where('parent_id', Auth::guard('web')->user()->id);
        }])->where(["type" => 1])->get();

        $filter = $filters->pluck('user')->unique();

        $currentMonth = Appointment::whereMonth("start_date", Carbon::now()->month)->where(["type" => 1])->count();
        $currentMonthName = Carbon::now()->format('M');


        $services = Service::where('user_id', Auth::guard('web')->user()->id)->get();


        return view('customer.appointment.appointments-listing', [
            "appointmentsToday" => $appointmentsToday,
            "appointmentsTommorow" => $appointmentsTommorow,
            "appointmentsLastWeek" => $appointmentsLastWeek,
            "appointmentsCancelled" => $appointmentsCancelled,
            "filter" => $filter,
            'services' => $services,
            "currentMonth" => $currentMonth, "currentMonthName" => $currentMonthName

        ]);
    }

    /**
     * private function for get Appointment
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    private  function getAppointment($req)
    {
        $appointments = Appointment::with(['appointmentPackage.service', 'appointmentFilm.packageFilm' => function ($q) {
            $q->with(['price', 'changedPrice']);

            return $q;
        }])->where('added_by', Auth::guard('web')->user()->id);


        if (!empty($req->uid) && count($req->uid) > 0) {
            $appointments->whereIn("user_id", $req->uid);
        }

        $start_date = Carbon::now()->startOfWeek()->toDateString();
        if (!empty($req->id)) {
            if ($req->id == 1) {
                $start_date = Carbon::today()->toDateString();
            } elseif ($req->id == 2) {
                $start_date = Carbon::tomorrow()->toDateString();
            } elseif ($req->id == 3) {
                $start_date = Carbon::now()->startOfWeek()->toDateString();
            } elseif ($req->id == 4) {
                $start_date =  Carbon::now()->startOfMonth()->toDateString();
            } elseif ($req->id == 5) {
                $start_date =  Carbon::now()->addMonth()->startOfMonth()->toDateString();
            } elseif ($req->id == 6) {
                $start_date =  Carbon::now()->startOfYear()->toDateString();
            }
        }
        if (!empty($req->from_date)) {
            $start_date = $req->from_date;
        }

        $appointments->whereDate('start_date', '>=', $start_date);

        if (!empty($req->cname)) {
            $cname = str_replace("-", " ", $req->cname);
            $appointments->whereHas('user.userProfile', function ($q) use ($cname) {
                $q->where("name", "LIKE", "%" . $cname . "%");
                return $q;
            });
        }

        if (!empty($req->service) && count($req->service) > 0) {
            $service = $req->service;
            $appointments->whereHas('appointmentPackage', function ($q) use ($service) {
                $q->whereIn("service_id", $service);
                return $q;
            });
        }
        return $appointments;
    }

    /**
     * view appointment basic info page
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function addViewBasicInfo(Request $req, $id = null)
    {
        $date = $req->date;
        $time =  !empty($req->time) && Carbon::parse($req->time)->format('H:i:s') !== "00:00:00" ? Carbon::parse($req->time)->format('H:i:s') : "";

        $users = User::with('userProfile')->where(["user_type_id" => 3, "parent_id" => Auth::guard('web')->user()->id])->get();
        $make = Make::get();
        $vehicleType = VehicleType::get();
        $user = Auth::guard('web')->user();
        $weekHour = $user->shopHour;
        $interval = $user ? ($user->onlineBooking ? $user->onlineBooking->appointment_interval : "") : "";
        $workingHour = [];
        $workingDays = [];
        foreach ($weekHour as $hour) {
            $days = $hour->day_id == 7 ? 0 : $hour->day_id;
            if ($hour->is_working_hours == 2) {
                $workingDays[] = $days;
            }
            $workingHour[$days]["minTime"] = $hour->working_start_hour ? $hour->working_start_hour : "10:00";
            $workingHour[$days]["maxTime"] = $hour->working_end_hour ? $hour->working_end_hour : "19:00";
            $workingHour[$days]["lunchStart"] = $hour->lunch_start_hour ? $hour->lunch_start_hour : "14:00";
            $workingHour[$days]["lunchEnd"] = $hour->lunch_end_hour ? $hour->lunch_end_hour : "15:00";
        }

        return view('customer.appointment.appointment-basic-info', compact('users', 'make', 'vehicleType', 'id', "date", "time", 'workingHour', 'workingDays', 'interval'));
    }

    /**
     * view appointment service page
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function addViewService($id)
    {
        $appointment = Appointment::where("uid", $id)->where(["type" => 1])->first();
        $vehicleType = $appointment->vehicle_type_id;
        $year = $appointment->model_year_id;

        $diffYears = getVehicleAge(ModelYear::find($year)->year);

        $year_type_id = ModelVehicleType::where(["vehicle_type_id" => $vehicleType, "year_id" => $year, "make_id" => $appointment->make_id, "model_id" => $appointment->make_model_id])->pluck('id');

        // get services 
        $services =  (new UtilityServicePackage)->getService(Auth::guard('web')->user()->id, ["vehicleType" => $vehicleType, "year_type_id" => $year_type_id]);

        $pricesArr = $this->viewAppointmentFilmType($id);

        return view('customer.appointment.appointment-service', compact('services', 'appointment', 'diffYears', 'pricesArr'));
    }

    /**
     * Save Appointment basic information
     *
     *make, model year, vehicle type
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function appointmentBasicInfoSave(Request $req)
    {
        try {
            // implement server side validation 
            $validation = Validator::make(
                $req->all(),
                [
                    'make_id' => 'required',
                    'make_model_id' => 'required',
                    "model_year_id" => "required",
                    'vehicle_type_id' => 'required',
                    "start_date" => "required|date",
                ]
            );

            if (count($validation->messages()->all()) > 0) {
                // formatted error message and manage in array 
                $error = array_combine($validation->errors()->keys(), $validation->messages()->all());

                return response()->json(["status" => "error", "messages" =>  $error]);
            } else {

                // new customer create on run time
                if (!empty($req->customer_name)) {

                    $userData = [
                        "uid" => Str::replace('/', '-', $this->createAccessTokenKey(Str::random(4))),
                        "email" => $req->customer_email,
                        "user_type_id" => 3,
                        "parent_id" => Auth::guard('web')->user()->id
                    ];
                    $userId = User::updateOrCreate(["email" => $req->customer_email, "user_type_id" => 3], $userData);

                    if (!empty($req->customer_phone)) {
                        $profile['phone'] = $req->customer_phone;
                    }

                    $profile['name'] = $req->customer_name;

                    $userProfile = UserProfile::updateOrCreate(["user_id" => $userId->id], $profile);
                }

                $data = [
                    "added_by" => Auth::guard('web')->user()->id,
                    "user_id" => !empty($req->customer_id) ? $req->customer_id : $userId->id,
                    "make_id" => $req->make_id,
                    "make_model_id" => $req->make_model_id,
                    "model_year_id" => $req->model_year_id,
                    "vehicle_type_id" => $req->vehicle_type_id,
                    "start_date" => Carbon::parse($req->start_date)->format('Y-m-d H:i:s'),
                    "is_new_customer" => $req->isCustomer,
                    "uid" => Str::replace('/', '-', $this->createAccessTokenKey(Str::random(4))),
                    "type" => 1
                ];

                if ($req->step) {
                    $data["steps"] = $req->step;
                }
                $appointment = Appointment::updateOrCreate(["id" => $req->id], $data);

                return response()->json(["status" => 200, "message" => "Appointment has been created successfully", "url" => route('customer.appointment_create_2', $appointment->uid)]);
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    /**
     * Filter data for package id
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function filterDataPackage($data)
    {
        $result = [];
        foreach ($data as $key => $val) {
            if (!empty($val)) {
                $result[] = [
                    "package_id" => $val,
                ];
            }
        }
        return $result;
    }

     /**
     * get appointment package record using appointment  id
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function getAppointmentPackage($id)
    {
        $appointmentPackage = AppointmentPackage::where("tbappointment_id", $id)->get();
        $appointment = Appointment::find($id);
        return response()->json(["status" => 200, "appointmentPackage" => $appointmentPackage, "appointment" => $appointment]);
    }

    /**
     * Save package or service
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function saveAppointmentPackage(Request $req)
    {
        $appointment = Appointment::find($req->tbappointment_id);

        if ($req->step) {
            $appointment->update(["steps" => $req->step]);
        }

        $validation = Validator::make($req->all(), [
            'proposals.*.tbappointment_id' => 'required|integer',
            'proposals.*.package_id' => 'required|integer',
        ]);

        if (count($validation->messages()->all()) > 0) {
            $error = array_combine($validation->errors()->keys(), $validation->messages()->all());

            return response()->json(["status" => "error", "messages" =>  $error]);
        } else {

            $data = $this->filterDataPackage($req->package_id);
            if (!empty($req->package_film_id)) {
                $this->saveAppointmentFilm($req);
            }


            foreach ($data as $appointmentData) {

                $proposal_package = AppointmentPackage::updateOrCreate([
                    'tbappointment_id' => $req->tbappointment_id,
                    'package_id' => $appointmentData['package_id']
                ], [
                    'tbappointment_id' => $req->tbappointment_id,
                    'service_id' => $this->getService($appointmentData['package_id']),
                    "uid" => Str::replace('/', '-', $this->createAccessTokenKey(Str::random(4))),
                ]);
                $proposalPackageId[] = $proposal_package->id;
                $packageId[] =  $proposal_package->package_id;
            }
            if (count($proposalPackageId) > 0) {
                AppointmentPackage::where(['tbappointment_id' => $req->tbappointment_id])->whereNotIn('id', $proposalPackageId)->delete();
            }

            if (AppointmentFilm::where(['tbappointment_id' => $req->tbappointment_id])->whereNotIn('package_id', $packageId)->exists()) {
                AppointmentFilm::where('tbappointment_id', $req->tbappointment_id)->whereNotIn('package_id', $packageId)->delete();
            }

            return response()->json(["status" => 200, "url" => route('customer.appointmentReviewView', $appointment->uid)]);
        }
    }

    /**
     * edit appointment
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editAppointment($id)
    {
        $data = Appointment::with(['customer', 'user' => function ($q) {
            $q->with(['userProfile' => function ($query) {
                $query->select('user_id', 'name', 'phone');
                return $query;
            }]);
            $q->select('id', 'email', 'uid');
            return $q;
        }])->where(["uid" => $id])->where(["type" => 1])->first();
        return response()->json(["status" => 200, "appointment" => $data]);
    }

    /**
     * view film type form
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function  getAppointmentFilmTypeByPackage($id, $appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        $appointmentPackage = $id;

        $vehicleType = $appointment->vehicle_type_id;
        $year = $appointment->model_year_id;
        // get vehicle age
        $diffYears = getVehicleAge(ModelYear::find($year)->year);

        $year_type_id = ModelVehicleType::where(["vehicle_type_id" => $vehicleType, "year_id" => $year, "make_id" => $appointment->make_id, "model_id" => $appointment->make_model_id])->pluck('id');



        $packages = Package::select('id', 'name', 'add_base_price_year')->with(['packageFilm' => function ($q) use ($vehicleType, $year_type_id) {

            $q->with(['price' => function ($s) use ($vehicleType, $year_type_id) {
                $s->with(['vehicleTypes' => function ($r) {
                    return $r->select('type', 'id');
                }]);
                $s->where('vehicle_id', $vehicleType);
                $s->orWhereIn('vehicle_id', $year_type_id);
                return $s->select('film_id', 'vehicle_id', 'price', 'id', 'package_id', 'duration');
            }]);
            return $q->select('id', 'package_id', 'film_type_name', 'allVehicle', 'image', 'all_vehicle_duration')->withSum(['price' => function ($q) use ($vehicleType, $year_type_id) {
                $q->where('vehicle_id', $vehicleType);
                $q->orWhereIn('vehicle_id', $year_type_id);
                return $q;
            }], 'price')->withMin(['price' => function ($q) use ($vehicleType, $year_type_id) {
                $q->where('vehicle_id', $vehicleType);
                $q->orWhereIn('vehicle_id', $year_type_id);
                return $q;
            }], 'price')->withMax(['price' => function ($q) use ($vehicleType, $year_type_id) {
                $q->where('vehicle_id', $vehicleType);
                $q->orWhereIn('vehicle_id', $year_type_id);
                return $q;
            }], 'price')->withSum(['changedPrice' => function ($query) use ($vehicleType, $year_type_id) {
                return $query->whereHas('filmVehicleType', function ($s) use ($vehicleType, $year_type_id) {
                    $s->where('vehicle_id', $vehicleType);
                    $s->orWhereIn('vehicle_id', $year_type_id);
                    return $s;
                });
            }], 'price')->withMin(['changedPrice' => function ($query) use ($vehicleType, $year_type_id) {
                return $query->whereHas('filmVehicleType', function ($s) use ($vehicleType, $year_type_id) {
                    $s->where('vehicle_id', $vehicleType);
                    $s->orWhereIn('vehicle_id', $year_type_id);
                    return $s;
                });
            }], 'price')->withMax(['changedPrice' => function ($query) use ($vehicleType, $year_type_id) {
                return $query->whereHas('filmVehicleType', function ($s) use ($vehicleType, $year_type_id) {
                    $s->where('vehicle_id', $vehicleType);
                    $s->orWhereIn('vehicle_id', $year_type_id);
                    return $s;
                });
            }], 'price');
        }])->whereHas('packageServices', function ($s) {
            return $s->where('user_id', Auth::guard('web')->user()->id);
        })->where('id', $appointmentPackage)->get();



        $pricesArr = [];
        $total = 0;
        if ($packages) {
            foreach ($packages as $key => $value) {
                $pricesArr[$key]['name'] = $value['name'];
                $pricesArr[$key]['uid'] = $value['uid'];
                if (!empty($value['packageFilm'])) {
                    foreach ($value['packageFilm'] as $val) {
                        $pricesArr[$key]['filmType'][] = $val['film_type_name'];

                        $pricesArr[$key]['min'][] = !empty($value->add_base_price_year) ? ($value->add_base_price_year * $diffYears) + (!empty($val['changed_price_min_price']) ? $val['changed_price_min_price'] : (!empty($val['price_min_price']) ? $val['price_min_price'] : $val['allVehicle'])) : (!empty($val['changed_price_min_price']) ? $val['changed_price_min_price'] : (!empty($val['price_min_price']) ? $val['price_min_price'] : $val['allVehicle']));

                        $pricesArr[$key]['max'][] = !empty($value->add_base_price_year) ? ($value->add_base_price_year * $diffYears) + (!empty($val['changed_price_max_price']) ? $val['changed_price_max_price'] : (!empty($val['price_max_price']) ? $val['price_max_price'] : $val['allVehicle'])) : (!empty($val['changed_price_max_price']) ? $val['changed_price_max_price'] : (!empty($val['price_max_price']) ? $val['price_max_price'] : $val['allVehicle']));
                        // $total = $total + $val['price_sum_price'];

                        // $pricesArr[$key]['package_film_sum_all_vehicle'][] = !empty($val['allVehicle'])
                        // ? $val['allVehicle'] : 0;


                        if (!empty($val['allVehicle'])) {
                            $total = $total + $val['allVehicle'];
                        }

                        if (!empty($val['changed_price_sum_price'])) {
                            $total = $total + $val['changed_price_sum_price'];
                        } else {

                            $total = $total + $val['price_sum_price'];
                        }

                        if (!empty($value->add_base_price_year)) {
                            $total = $total + ($value->add_base_price_year * $diffYears);
                        }
                    }
                }
            }
        }
        return response()->json(["status" => 200, 'appointment' => $appointment, 'packages' => $packages, 'pricesArr' => $pricesArr, 'total' => $total, 'diffYears' => $diffYears]);
    }

    /**
     * View film type of appointments
     *
     * @param [string] $id
     * @return void
     */
    public function viewAppointmentFilmType($id)
    {
        $appointment = Appointment::where("uid", $id)->first();
        $appointmentPackage = AppointmentPackage::where("tbappointment_id", $appointment->id)->pluck('package_id');
        $appointmentFilm = AppointmentFilm::where("tbappointment_id", $appointment->id)->pluck('package_film_id');

        $vehicleType = $appointment->vehicle_type_id;
        $year = $appointment->model_year_id;

        // create helper for vehicle age 
        $diffYears = getVehicleAge(ModelYear::find($year)->year);

        $year_type_id = ModelVehicleType::where(["vehicle_type_id" => $vehicleType, "year_id" => $year, "make_id" => $appointment->make_id, "model_id" => $appointment->make_model_id])->pluck('id');

        // get packages 
        $packages = (new UtilityServicePackage)->getPackage(["userId" => Auth::guard('web')->user()->id, "vehicleType" => $vehicleType, "year_type_id" => $year_type_id, "appointmentFilm" => $appointmentFilm, "appointmentPackage" => $appointmentPackage]);

        return $packages;
    }

    /**
     * get service id
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function getService($id)
    {
        $packages = Package::find($id);
        return $packages->service_id;
    }

    /**
     * get film data
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function filterDataFilm($data)
    {
        $result = [];
        foreach ($data as $key => $val) {
            if (!empty($val)) {
                $result[] = [
                    "package_film_id" => $val,
                ];
            }
        }
        return $result;
    }

    /**
     * get appointment film
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function getAppointmentFilmType($id, $appointmentId)
    {
        $filmType = AppointmentFilm::where(["package_id" => $id, "tbappointment_id" => $appointmentId])->get();
        return response()->json(["status" => 200, "filmType" => $filmType]);
    }

    /**
     * get appointment film by appointment id
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function getFilmTypeByAppointment($appointmentId)
    {
        $filmType = AppointmentFilm::where(["tbappointment_id" => $appointmentId])->get();

        return response()->json(["status" => 200, "filmType" => $filmType]);
    }


    /**
     * Save appointment film
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    private function saveAppointmentFilm($req)
    {
        $appointment = Appointment::find($req->tbappointment_id);
        if ($req->step) {
            $appointment->update(["steps" => $req->step]);
        }
        $validation = Validator::make($req->all(), [
            'proposals.*.tbappointment_id' => 'required|integer',
            'proposals.*.package_film_id' => 'required|integer',
            'proposals.*.package_id' => 'required|integer',
        ]);

        if (count($validation->messages()->all()) > 0) {
            $error = array_combine($validation->errors()->keys(), $validation->messages()->all());

            return response()->json(["status" => "error", "messages" =>  $error]);
        } else {
            $data = $this->filterDataFilm($req->package_film_id);


            foreach ($data as $proposalData) {
                $proposal_package_film = AppointmentFilm::updateOrCreate([
                    'tbappointment_id' => $req->tbappointment_id,
                    'package_film_id' => $proposalData['package_film_id']
                ], [
                    'tbappointment_id' => $req->tbappointment_id,
                    'package_film_id' => $proposalData['package_film_id'],
                    'package_id' => $this->getpackageId($proposalData['package_film_id']),
                    "uid" => Str::replace('/', '-', $this->createAccessTokenKey(Str::random(4))),
                ]);
                $proposal_package_filmId[] = $proposal_package_film->id;
                $proposal_package_Id[] = $proposal_package_film->package_id;
            }
            if (count($proposal_package_filmId) > 0) {
                AppointmentFilm::where('tbappointment_id', $req->tbappointment_id)->whereIn("package_id", $proposal_package_Id)->whereNotIn("id", $proposal_package_filmId)->delete();
            }

            return response()->json(["status" => 200]);
        }
    }

    /**
     * get package id
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function getpackageId($id)
    {
        $type = PackageFilm::find($id);
        return $type->package_id;
    }

    /**
     * appointment review page
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function appointmentReviewView($id)
    {
        $appointment = Appointment::where("uid", $id)->first();
        $vehicleType = $appointment->vehicle_type_id;
        $year = $appointment->model_year_id;
        $diffYears = getVehicleAge(ModelYear::find($year)->year);

        $year_type_id = ModelVehicleType::where(["vehicle_type_id" => $vehicleType, "year_id" => $year, "make_id" => $appointment->make_id, "model_id" => $appointment->make_model_id])->pluck('id');
        $users = User::with('userProfile')->where(["user_type_id" => 3, "parent_id" => Auth::guard('web')->user()->id])->get();
        $make = Make::get();

        $appointment = Appointment::getPriceByAppointment(["vehicleType" => $vehicleType, "year_type_id" => $year_type_id])->where("uid", $id)->where(["type" => 1])->first();

        // calculate appointment calculation like price, count etc.
        $appointmentCalculation = (new UtilityServicePackage)->getAppointmentCalculation($appointment, $diffYears);

        $appointmentReview = $appointmentCalculation['appointmentReview'];
        $total = $appointmentCalculation['total'];
        $count = $appointmentCalculation['count'];

        $payment = PaymentMode::get();

        $tech = User::select('id')->with(['userProfile' => function ($q) {
            return $q->select('user_id', 'name');
        }])->where(["user_type_id" => 2, "parent_id" => Auth::guard('web')->user()->id])->get();

        $techList = [];
        if (!empty($tech)) {
            foreach ($tech as $key => $val) {
                $techList[$key]['value'] = $val->id;
                $techList[$key]['label'] = $val->userProfile->name;
            }
        }

        $appointmentPackage = AppointmentPackage::with(['package' => function ($q) {
            return $q->select('id', 'name');
        }])->where('tbappointment_id', $appointment->id)->orderBy('position', 'ASC')->get();

        return view('customer.appointment.appointment-review', compact('appointment', 'users', 'make', 'appointmentReview', 'total', 'payment', 'techList', 'appointmentPackage'));
    }



    /**
     * update vehicle type in review page
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function appointmentVehicleUpdate(Request $req)
    {
        $data = Appointment::find($req->id)->update([
            "make_id" => $req->make_id,
            "make_model_id" => $req->model_id,
            "model_year_id" => $req->model_year_id, "vehicle_type_id" => $req->vehicle_type_id
        ]);
        if ($data) {
            return response()->json(["status" => 200, "message" => "Vehicle has been updated successfully"]);
        }
    }

    /**
     * appointment review update user
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function appointmentUserUpdate(Request $req)
    {
        $data = Appointment::updateOrCreate(["id" => $req->id], ["user_id" => $req->user_id]);
        if ($data) {
            return response()->json(["status" => 200, "message" => "Customer has been updated successfully"]);
        }
    }

    /**
     * appointment save as a draft
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function appointmentDraft($id)
    {
        $data = Appointment::where("uid", $id)->first();
        $data->update(["steps" => 4]);
        return redirect()->route('customer.appointment_listing')->with("message", "Appointment has been saved successfully");
    }

    /**
     * appointment send schedule
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function appointmentSuccess($id, EmailSendService $mail)
    {
        $data = Appointment::where("uid", $id)->first();


        if (!empty($data) && !empty($data->user)) {
            $data->update(["steps" => 4, "status" => "confirmed"]);

            $date  = Carbon::parse($data->start_date)->format('Y M d, h:i A');

            $name = $data ? (!empty($data->user) ? (!empty($data->user->userProfile) ? $data->user->userProfile->name : '') : '') : '';
            $email = $data->user ? $data->user->email : "";

            $template = EmailTemplate::find(5);

            $body = null;
            $subject = null;

            if ($template) {
                $body = Str::replace(['{CUSTOMER_NAME}', '{DATE}'], [ucfirst($name), $date], $template->body);
                $subject = $template->subject;
            }
            try {
                // send mail via smtp 
                $mail->sendEmail($email, $subject, $body);
                return response()->json(["status" => 200, "message" => "Appointment has been schedule.  Send to " . $name . "'s email", "url" => route('customer.appointment_listing')]);
            } catch (\Exception $e) {
                return response()->json(["status" => "error", "message" => "Some technical issue please contact to administrator", "url" => route('customer.appointment_listing')]);
                // dd($e->message());
            }
        }
        return response()->json(["status" => "error", "message" => "Something went wrong,please contact to administrator"]);
    }


    /**
     * delete Service
     * delete packages
     * delete Film Type
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function deleteAppointment($id)
    {

        $appointment = Appointment::where("uid", $id)->first();

        $appointment->appointmentPackage()->delete();
        $appointment->appointmentFilm()->delete();

        if ($appointment->delete()) {
            return redirect()->route('customer.appointment_listing')->with("message", "Appointment has been deleted successfully");
        }
    }

    /**
     * appointment change status successfully
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function changeAppointmentStatus(Request $req)
    {
        $data = Appointment::where(["uid" => $req->id])->first();
        $data->update(["status" => $req->status]);
        if ($data) {
            return response()->json(["status" => 200, "message" => "Appointment status has been changed successfully"]);
        }
    }

    /**
     * view appointment detail page
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function appointmentDetail($id)
    {

        $appointment = Appointment::where("uid", $id)->first();
        $vehicleType = $appointment->vehicle_type_id;
        $year = $appointment->model_year_id;
        $diffYears = getVehicleAge(ModelYear::find($year)->year);


        $year_type_id = ModelVehicleType::where(["vehicle_type_id" => $vehicleType, "year_id" => $year, "make_id" => $appointment->make_id, "model_id" => $appointment->make_model_id])->pluck('id');

        $users = User::with('userProfile')->where(["user_type_id" => 3, "parent_id" => Auth::guard('web')->user()->id])->get();
        $make = Make::get();
        // getPriceByAppointment  scope find min or max price of film types
        $data = Appointment::getPriceByAppointment(["vehicleType" => $vehicleType, "year_type_id" => $year_type_id])->where("uid", $id)->first();
        
        // get appointment calculation like total , count etc.

        $appointmentCalculation = (new UtilityServicePackage)->getAppointmentCalculation($data, $diffYears);

        $appointmentReview = $appointmentCalculation['appointmentReview'];
        $total = $appointmentCalculation['total'];
        $count = $appointmentCalculation['count'];


        $proposals = Proposal::with(['proposalPackage.service', 'proposalFilm'])->where(['added_by' => Auth::guard('web')->user()->id, "user_id" => $data->user_id])->paginate(20);
        return view('customer.appointment.appointments', compact('data', 'proposals', 'total', 'appointmentReview'));
    }

    /**
     * appointment Send  proposal
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function sendProposalAppointment($id, EmailSendService $mail)
    {
        $proposal = Proposal::with(['user' => function ($q) {
            $q->with(['userProfile' => function ($s) {
                $s->select('user_id', 'name');
                return $s;
            }]);
            return $q->select('id', 'email');
        }])->where("uid", $id)->first();


        if ($proposal->user) {
            $email = $proposal->user->email ?? '';
            $name = $proposal->user->userProfile->name ?? '';

            $template = EmailTemplate::find(4);
            // dd($template);
            $body = null;
            $subject = null;

            if ($template) {
                $body = Str::replace(['{LINK}', '{CUSTOMER_LINK}'], [route('sendProposalApproval', $id), $name], $template->body);
                $subject = $template->subject;
            }

            try {
                // send  proposal via email
                $mail->sendEmail($email, $subject, $body);

                return back()->with(["message" => "Proposal has been send to " . $name . "'s email"]);
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
    }

    /**
     * search appointment
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function searchAppointment(Request $req)
    {
        $name = $req->name;

        $name = $req->search;
        $proposal = User::select('id', 'email')->with('userProfile')->whereHas('userProfile', function ($q) use ($name) {
            $q->where("name", "LIKE", "%" . $name . "%");
            return $q;
        })->where('parent_id', Auth::guard('web')->user()->id)->get();

        return response()->json(["status" => 200, "proposal" => $proposal]);
    }

    /**
     * Payment colledt in review page
     *
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function paymentCollect(Request $req)
    {
        $appointment = Appointment::where("uid", $req->id)->first();
        if ($appointment) {
            $data["is_payment"] = $req->is_payment;
            $data["payment_collect"] = $req->payment_collect;
            $data['payment_collected_date'] = Carbon::now()->format('Y-m-d H:i:s');

            if (!empty($req->amount)) {
                $data["amount"] = $req->amount;
            } else {
                $data["amount"] = null;
            }



            $appointment->update($data);
            if ($req->is_payment == 1) {
                return response()->json(["status" => 200, "message" => "Payment has been collected  successfully", "appointment" => $appointment]);
            }
            return response()->json(["status" => 200, "message" => "Payment has been pending", "appointment" => $appointment]);
        }
        return response()->json(["status" => "error", "message" => "Something went wrong please contact to administrator !"]);
    }
    /**
     * Assign technician to the appointment according to services
     */

    public function assignAppointmentTech(Request $req)
    {

        $data = $req->all();

        if (!empty($data['tech'])) {
            foreach ($data['tech'] as  $key => $value) {
                if (!empty($value)) {
                    $record = [
                        "user_id" => Auth::guard('web')->user()->id,
                        "tech_id" => $value,
                        "package_id" => $req->package_id,
                        "appointment_id" => $req->appointment_id
                    ];

                    $tech = AppointmentTechAssign::updateOrCreate([
                        "appointment_id" => $req->appointment_id, "tech_id" => $value,
                        "package_id" => $req->package_id
                    ], $record);

                    $techId[]  = $tech->id;
                }
            }

            if (count($techId) > 0) {
                AppointmentTechAssign::where(["appointment_id" => $req->appointment_id, "package_id" => $req->package_id])->whereNotIn('id', $techId)->delete();
            }
        } else {
            AppointmentTechAssign::where(["appointment_id" => $req->appointment_id, "package_id" => $req->package_id])->delete();
        }
    }

    /**
     * Save service according to priority
     *
     * @param Request $req
     * @return void
     */
    public function saveAppointmentServiceOrder(Request $req)
    {

        foreach ($req->service as $key => $service) {
            $data['position'] = $key + 1;
            $services = AppointmentPackage::updateOrCreate(["id" => @$service['id']], $data);
        }

        $serviceAll = AppointmentPackage::with('package')->where("tbappointment_id", $req->appointment_id)->orderBy('position', 'asc')->get();
        return response()->json(["status" => 200, "serviceAll" => $serviceAll]);
    }
}
