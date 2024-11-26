@extends('layouts.app')
@section('content')
    <basic-info-appointment-component :users="{{ json_encode($users) }}" :make="{{ json_encode($make) }}"
        :vehicle-type="{{ json_encode($vehicleType) }}" :appointment-uid="{{ json_encode($id) }}"
        :date="{{ json_encode($date) }}" :time="{{ json_encode($time) }}"
        :working-hour="{{ json_encode($workingHour) }}" :working-days="{{ json_encode($workingDays)}}" :appointment-interval="{{ json_encode($interval) }}"></basic-info-appointment-component>
@endsection
