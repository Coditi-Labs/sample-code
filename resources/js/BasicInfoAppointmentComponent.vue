<template>
  <div>
    <div class="relative md:ml-64 rounded-xl border min-h-screen  ">
      <nav class="  w-full z-10  md:flex-row md:flex-nowrap md:justify-start flex items-center border-b">
        <div class="w-full mx-autp items-center flex justify-between md:flex-nowrap flex-wrap py-[26px] px-4 ">
          <div>
            <a class=" text-xs 2xl:text-sm flex gap-2  items-center  block cursor-pointer"
              @click="formSubmitCheck(1)"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 20 20" fill="none">
                <path d="M11.4942 13.3337L8.45553 10.295C8.29282 10.1322 8.29282 9.86842 8.45554 9.7057L11.4942 6.66699"
                  stroke="#9E9EA9" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                </path>
              </svg> <span class="hidden lg:inline-flex">Exit & save as draft</span></a>
            <!-- sidebar component  -->
            <AddCustomVehicleDetails ref="vehicleCustomInfo" @vehicle-detail="vehicleDetail">
            </AddCustomVehicleDetails>

          </div>
          <div>
            <!-- use nav bar of step form -->
            <NavBarComponent :nav-bar="navBar" :form-type="formType"></NavBarComponent>
          </div>
          <div class="flex space-y-6 md:space-x-2 md:space-y-0">
            <a @click="formSubmit(2)"
              class="text-white btn-purple focus:ring-4 focus:ring-blue-300 font-medium rounded-lg hidden lg:inline-flex items-center text-xs 2xl:text-sm px-5 py-2.5 cursor-pointer ">
              Continue <i class="fa-solid fa-angle-right pl-2"></i>
            </a>
          </div>
        </div>

      </nav>


      <div class="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-3 mb-4  px-4 lg:px-0">
        <div></div>
        <div class="w-full 2xl:w-[400px] mx-auto">
          <div class="text-lg 2xl:text-xl  font-medium py-10">Basic information</div>
          <div class="text-base pb-8">Customer & date</div>
          <form>
            <!-- preloader -->
            <PreLoader :loading="loading"></PreLoader>
            <div class="mb-8 relative">
              <label for="name" class="block  text-xs 2xl:text-sm  leading-6 text-stone-300 mb-1">Select
                customer <span class="text-red-500">*</span></label>
              <div class="mb-4">
                <button id="dropdownBgHoverButton" data-dropdown-toggle="dropdownBgHover"
                  class=" justify-between items-center flex w-full rounded-md border px-3.5 py-2 text-gray-500 bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm: text-xs 2xl:text-base sm:leading-6 capitalize"
                  type="button"> <span class="flex"> <svg class="w-4 mt-1 h-4 mr-2 text-gray-500 "
                      xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z">
                      </path>
                    </svg> {{ customerLabel }}</span><svg class="w-2.5 h-2.5 ms-3" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m1 1 4 4 4-4" />
                  </svg>
                </button>


                <div id="dropdownBgHover"
                  class="z-10 hidden w-full card border proposel-select shadow-lg border-gray-600 divide-y divide-zinc-500 text-white bg-black overflow-hidden rounded-lg shadow  ">
                  <div class="relative  py-2">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                      <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"></path>
                      </svg>
                    </div>
                    <input type="text" @keyup="searchFilter($event)"
                      class="ps-10 block w-full rounded-md  px-2 py-2 text-white bg-transparent    placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                      placeholder="Search for customer.. ">
                  </div>
                  <ul class="p-3 space-y-1 text-sm text-gray-700   " aria-labelledby="dropdownBgHoverButton">
                    <li class="userAll" v-for="user in users" :key="user.id"
                      :data-user="user.user_profile.name.toLowerCase()">
                      <div class="flex items-center p-2 rounded text-white ">
                        <input :id="`user-checkbox-${user.id}`" type="radio" :value="user.id" v-model="customer_id"
                          class="w-4 h-4 text-white bg-black shadow-sm ring-1 ring-inset ring-gray-300"
                          @click="userSelected($event, user.id, user.user_profile.name)">
                        <label :for="`user-checkbox-${user.id}`"
                          class="w-full ms-2 text-sm font-medium text-gray-50 rounded ">{{
                            user.user_profile.name }}</label>
                      </div>
                    </li>

                  </ul>
                  <div class="flex items-center text-white py-2">
                    <!-- add new customer button -->
                    <button type="button" @click="newCustomer($event)"
                      class="block w-full rounded-md   text-left px-3.5 py-2 text-white   placeholder:text-gray-400   sm:text-sm sm:leading-6"><i
                        class="fas fa-plus pr-2"></i> New Customer</button>
                  </div>
                </div>

                <p class="text-red-500  text-xs absolute pt-1 left-0   w-full"
                  v-if="error.length > 0 && customer_idError != null">{{ customer_idError }}</p>
              </div>
            </div>

            <div :class="isCustomer == 2 ? ' ' : 'hidden'" id="new-customer">
              <div class="mb-8 relative">
                <label for="name" class="block  text-xs 2xl:text-sm  leading-6 text-stone-300 mb-1">Name
                  <span class="text-red-500">*</span></label>

                <div class="relative">
                  <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="#9E9EA9" class="w-4 h-4">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z">
                      </path>
                    </svg>
                  </div>
                  <input type="text" name="name" v-model="customer_name" id="name" autocomplete="off"
                    class="ps-10 block w-full rounded-md border px-2 py-2 text-white bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                    placeholder="Customer’s full name">

                </div>
                <p class="text-red-500  text-xs absolute pt-1 left-0   w-full"
                  v-if="error.length > 0 && customer_name_error != null">{{ customer_name_error }}</p>
              </div>
              <div class="mb-8 relative">
                <label for="email-address-icon" class="block  text-xs 2xl:text-sm  leading-6 text-stone-300 mb-1">Email
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                      fill="currentColor" viewBox="0 0 20 16">
                      <path
                        d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z">
                      </path>
                      <path
                        d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z">
                      </path>
                    </svg>
                  </div>
                  <input type="email" id="email" v-model="customer_email" autocomplete="off"
                    class="ps-10 block w-full rounded-md border px-3.5 py-2 text-white bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                    placeholder="name@shopbooster.com" name="email">
                </div>
              </div>
              <div class="mb-8 relative">
                <label for="email-address-icon" class="block  text-xs 2xl:text-sm  leading-6 text-stone-300 mb-1">Phone
                  number
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                      <path
                        d="M6.46189 7.68099C7.08035 8.90507 7.90089 10.0532 8.92349 11.0758C9.94609 12.0984 11.0942 12.919 12.3183 13.5374L13.5811 12.2746C13.8509 12.0048 14.2696 11.9527 14.5973 12.1482L16.7891 13.4558C17.2555 13.734 17.3354 14.3767 16.9514 14.7607L14.6841 17.0281C14.2634 17.4488 13.6482 17.6196 13.0833 17.433C11.9773 17.0675 10.9021 16.5824 9.87813 15.9776C8.70133 15.2826 7.59203 14.4295 6.58091 13.4184C5.5698 12.4073 4.71674 11.298 4.02171 10.1212C3.41692 9.09716 2.9318 8.02204 2.56634 6.91603C2.37968 6.35113 2.55053 5.73593 2.97121 5.31524L5.2386 3.04786C5.62256 2.66389 6.26531 2.74384 6.54351 3.21018L7.85108 5.40202C8.04658 5.72973 7.99451 6.14837 7.72467 6.41821L6.46189 7.68099Z"
                        stroke="#838391" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                  </div><input type="text" v-model="customer_phone" id="phone" autocomplete="off"
                    class="ps-10 block w-full rounded-md border px-3.5 py-2 text-white bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                    placeholder="Enter phone number .." name="phone" maxlength="10">
                </div><!---->
              </div>
            </div>


            <div class="mb-8 relative">
              <label for="name" class="block  text-xs 2xl:text-sm  leading-6 text-stone-300 mb-1">Start
                date & time <span class="text-red-500">*</span></label>
              <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-1.5 pointer-events-none">
                </div>
                <!-- date time picker -->
                <DateTimePicker @selected-date-time="selectedDateTime" :date-time="start_date"
                  :workingHours="workingHour" :workingDays="workingDays" :appointmentInterval="appointmentInterval">
                </DateTimePicker>
              </div>
              <p class="text-red-500 text-xs absolute pt-1 left-0 w-full"
                v-if="error.length > 0 && start_dateError != null">{{ start_dateError }}</p>

            </div>

            <div class="border-b pt-1 mb-8 relative"></div>
            <!-- add vehicle details -->
            <div class="text-base pb-8">Vehicle details</div>
            <div class="mb-8 relative">
              <label for="name" class="block  text-xs 2xl:text-sm  leading-6 text-stone-300 mb-1">Vehicle
                Year <span class="text-red-500">*</span></label>
              <select id="countries"
                class="  block w-full rounded-md border px-3.5 py-2 text-gray-500 bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm: text-xs 2xl:text-base sm:leading-6"
                v-model="model_year_id" @change="getMakAll($event)">
                <option value="0">Vehicle year</option>
                <option v-for="year in years" :key="year.id" :value="year.id"> {{ year.year }}</option>
              </select>
              <p class="text-red-500 text-xs absolute pt-1 left-0 w-full"
                v-if="error.length > 0 && model_year_idError != null">{{ model_year_idError }}</p>

            </div>

            <div class="mb-8 relative">
              <label for="name" class="block  text-xs 2xl:text-sm  leading-6 text-stone-300 mb-1">Vehicle
                Make <span class="text-red-500">*</span></label>
              <select id="countries"
                class="  block w-full rounded-md border px-3.5 py-2 text-gray-500 bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-xs 2xl:text-base sm:leading-6"
                @change="getModel($event)" v-model="make_id">
                <option value="0">Vehicle make</option>
                <option v-for="item in make" :key="item.id" :value="item.id">{{ item.make }}</option>

              </select>

              <p class="text-red-500 text-xs absolute pt-1 left-0 w-full"
                v-if="error.length > 0 && make_idError != null">{{ make_idError }}</p>
            </div>
            <div class="mb-8 relative">
              <label for="name" class="block  text-xs 2xl:text-sm  leading-6 text-stone-300 mb-1">Vehicle
                Model <span class="text-red-500">*</span></label>
              <select id="countries"
                class="  block w-full rounded-md border px-3.5 py-2 text-gray-500 bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm: text-xs 2xl:text-base sm:leading-6"
                v-model="model_id" @change="getVehicleTypeAll($event)">
                <option value="0">Vehicle model</option>
                <option v-for="model in models" :key="model.id" :value="model.id">{{ model.model }}
                </option>

              </select>

              <p class="text-red-500 text-xs absolute pt-1 left-0 w-full"
                v-if="error.length > 0 && model_idError != null">{{ model_idError }}</p>

            </div>

            <div class="mb-8 relative">
              <label for="name" class="block  text-xs 2xl:text-sm  leading-6 text-stone-300 mb-1">Vehicle
                Type <span class="text-red-500">*</span></label>
              <select id="countries"
                class="  block w-full rounded-md border px-3.5 py-2 text-gray-500 bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm: text-xs 2xl:text-base sm:leading-6"
                v-model="vehicle_type_id">
                <option value="0">Select vehicle type</option>
                <option v-for="val in vehicleType" :key="val.id" :value="val.id"> {{ val.type }}
                </option>
              </select>

              <p class="text-red-500 text-xs absolute pt-1 left-0 bo w-full"
                v-if="error.length > 0 && vehicle_type_idError != null">{{ vehicle_type_idError }}</p>

              <p class="text-zinc-400 text-[10px] 2xl:text-xs mt-1 block invisible">We haven’t recognised
                this vehicle. To add it, select the vehicle type so we can show you the relevant
                services.</p>

              <p class="text-zinc-400 text-xs mt-2 block">Is your model not in the database? <a
                  class="text-blue-800   cursor-pointer font-bold" @click="addManually()">Add
                  new vehicle </a></p>


            </div>
          </form>
        </div>
        <div></div>
        <div>

          <a @click="formSubmit(2)"
            class="text-white btn-purple focus:ring-4 focus:ring-blue-300 font-medium rounded-lg flex justify-center items-center w-full lg:hidden text-xs 2xl:text-sm px-5 py-2.5 cursor-pointer ">
            Continue <i class="fa-solid fa-angle-right pl-2"></i>
          </a>
        </div>
      </div>
    </div>

  </div>
</template>
<script type="text/javascript">
// import section 

import NavBarComponent from '../proposals/NavBarComponent.vue';

import AddCustomVehicleDetails from '../AddCustomVehicleDetails.vue';

import PreLoader from "../PreLoader.vue";
import DateTimePicker from "../DateTimePicker.vue";

import moment from "moment";
// vue section
export default {
  props: ['users', 'appointmentUid', 'date', 'time', "workingHour", "workingDays", 'appointmentInterval'],
  data() {
    return {
      make_id: 0,
      customer_id: 0,
      model_id: 0,
      models: [],
      model_year_id: 0,
      years: [],
      vehicle_type_id: 0,
      error: [],
      make_idError: '',
      model_idError: '',
      model_year_idError: '',
      vehicle_type_idError: '',
      start_dateError: "",
      start_date: null,
      customer_idError: '',
      navBar: 1,
      id: null,
      vehicleType: [],
      formType: 'appointment',
      customerId: [],
      isCustomer: 1,
      appointmentCustomerId: [],
      make: [],
      customer_name: null,
      customer_phone: null,
      customer_email: null,
      proposalCustomerId: null,
      customerLabel: "Select customer",
      loading: false,

    }
  },
  components: {
    NavBarComponent,
    PreLoader,
    DateTimePicker,
    AddCustomVehicleDetails,
  },
  mounted() {
    if (this.appointmentUid != null) {
      this.getBasicInfo(this.appointmentUid); // call the function to get the basic info
    }

    if (this.date != null) {
      let time = this.time == "" ? moment().format("HH:mm:ss") : moment(this.time, "HH:mm:ss").format("HH:mm:ss");

      let date = this.date + ' ' + time;
      this.start_date = moment(date).format('YYYY-MM-DD hh:mm A'); // format the date
    }

    this.getYearAll();
  },
  watch: {
    // instant validations on form fields
    customer_id: function (newVal) {
      if (newVal == 0) {
        this.customer_idError = "Please select customer";
        this.error.push(this.customer_idError);
      } else {
        this.customer_idError = "";
        this.error.push(this.customer_idError);
      }
    },
    start_date: function (newVal) {
      if (newVal == 0) {
        this.start_dateError = "Please select Date & Time";
        this.error.push(this.start_dateError);
      } else {
        this.start_dateError = "";
        this.error.push(this.start_dateError);
      }
    },
    make_id: function (newVal) {
      if (newVal == 0) {
        this.make_idError = "Please select make";
        this.error.push(this.make_idError);
      } else {
        this.make_idError = "";
        this.error.push(this.make_idError);
      }
    },
    model_id: function (newVal) {
      if (newVal == 0) {
        this.model_idError = "Please select model";
        this.error.push(this.model_idError);
      } else {
        this.model_idError = "";
        this.error.push(this.model_idError);
      }
    },
    model_year_id: function (newVal) {
      if (newVal == 0) {
        this.model_year_idError = "Please select year";
        this.error.push(this.model_year_idError);
      } else {
        this.model_year_idError = "";
        this.error.push(this.model_year_idError);
      }
    },
  },
  methods: {
    getVehicleTypeAll(event) {
      this.getVehicleType(event.target.value);
    },

    getBasicInfo(id) {
      this.make_id = 0;
      this.customer_id = 0;
      this.model_id = 0;
      this.model_year_id = 0;
      this.vehicle_type_id = 0;
      this.start_date = null;

      // call api to get basic info
      axios.get(`/customer/appointment-edit-basic-information/${id}`).then((resp) => {
        if (resp.data.status == 200) {
          this.navBar = resp.data.appointment.steps;

          let appointment = resp.data.appointment;


          this.customer_id = appointment.user_id != null ? appointment.user_id : 0;

          this.make_id = appointment.make_id;
          this.model_id = appointment.make_model_id;
          this.model_year_id = appointment.model_year_id;
          this.vehicle_type_id = appointment.vehicle_type_id;
          this.id = appointment.id;
          this.start_date = moment(appointment.start_date).format('YYYY-MM-DD hh:mm A');
          this.isCustomer = appointment.is_new_customer;
          let user = appointment.user;
          this.customerLabel = user ? user.user_profile ? user.user_profile.name : '' : ''

          if (this.isCustomer == 2) {
            let user = appointment.user;
            this.customer_email = user ? user.email ? user.email : '' : '';
            this.customer_name = user ? user.user_profile ? user.user_profile.name : '' : '';
            this.customer_phone = user ? user.user_profile ? user.user_profile.phone : '' : '';
          }


          if (appointment.customer.length > 0) {
            appointment.customer.map((res) => { // loop through customer array
              if (!this.customerId.includes(res.customer_id)) {
                this.customerId.push(res.customer_id);
              }
              if (!this.appointmentCustomerId.includes(res.customer_id)) {
                this.appointmentCustomerId.push(res.customer_id)
              }
            });
          }


          if (this.model_year_id) {
            this.getMake(this.model_year_id);
          }
          if (this.make_id) {
            this.getAllModel(this.make_id);
          }
          if (this.model_id) {
            this.getVehicleType(this.model_id);
          }


        }
      });
    },
    getModel(event) {
      let id = event.target.value;
      this.getAllModel(id);


    },

    newCustomer(event) {
      this.customer_id = null;
      this.isCustomer = 2;
      this.customerLabel = "New Customer";
      document.querySelector('#new-customer').classList.remove('hidden')
    },

    // call api for get vehicle type
    getVehicleType(id) {
      this.vehicleType = [];
      this.loading = true;

      axios.get(`/customer/get-vehicle-type/${id}`).then((res) => {
        if (res.data.status == 200) {
          let result = res.data.vehicleType;
          result.map((resp, i) => {
            if (!this.vehicleType.includes(resp)) {
              this.vehicleType.push(resp)
            }
            if (i == 0 && this.vehicle_type_id == 0) {

              this.vehicle_type_id = resp.id;
            }
          });
          this.loading = false;
        }

      });

    },
    // call api for get vehicle model
    getAllModel(id) {
      this.loading = true;
      this.models = [];
      axios.get(`/customer/get-model-make/${id}/${this.model_year_id}`).then((res) => {
        if (res.data.status == 200) {
          let result = res.data.models;
          result.map((resp, i) => {
            if (!this.models.includes(resp)) {
              this.models.push(resp)
            }
            console.log(this.models)

          });
          this.loading = false;

        }

      });
    },
    // call api for get vehicle year
    getYearAll(id) {
      this.years = [];

      axios.get(`/customer/get-year-all`).then((resp) => {
        resp.data.year.map((resp, i) => {
          if (!this.years.includes(resp)) {
            this.years.push(resp)
          }
        });
      });
    },
    // get all vehicle make
    getMakAll(event) {
      let id = event.target.value;
      this.getMake(id);

    },
    // get vehicle make year through api
    getMake(id) {
      this.loading = true;

      this.make = [];
      axios.get(`/customer/get-make-year/${id}`).then((resp) => {
        if (resp.data.status == 200) {

          resp.data.make.map((res) => {
            if (!this.make.includes(res)) {
              this.make.push(res);
            }
          });
          this.loading = false;

        }
      });
    },

    //show selected user

    userSelectFilter(id) {
      if (this.customerId.length > 0) {
        this.customer_name = '';
        this.customer_phone = '';
        this.customer_email = '';
        this.isCustomer = 1;
        this.customerLabel = "Please select Customer";
        return this.customerId.includes(id) ? true : false;

      } else if (this.customerId.length == 0) {
        return false;
      } else {
        return true;
      }
    },
    // search filter implement on dropdown
    searchFilter(event) {
      let search = event.target.value.toLowerCase();
      let allService = document.querySelectorAll('.userAll');
      let searchDoc = document.querySelector("li[data-user*='" + search + "']");

      if (search.length > 0) {
        for (let iterate of allService) {
          iterate.classList.add('hidden');
        }
        if (searchDoc != null) {
          searchDoc.classList.remove('hidden');
        }
      } else {
        for (let iterate of allService) {
          iterate.classList.remove('hidden');
        }
      }
    },
    // select user
    userSelected(event, id, name) {
      this.customer_id = null;
      this.customerLabel = null;

      if (event.target.checked) {
        this.customerLabel = name;

        this.isCustomer = 1;
        this.customer_id = id;
      }
    },
    // get all vehicle type 
    getVehicleTypeAll(event) {
      let id = event.target.value;
      this.getVehicleType(id);
    },
    // get vehicle type through api
    getVehicleType(id) {
      this.vehicleType = [];

      axios.get(`/customer/get-vehicle-type-model/${id}/${this.make_id}/${this.model_year_id}`).then((res) => {
        if (res.data.status == 200) {
          let result = res.data.vehicleType;
          result.map((resp, i) => {
            if (!this.vehicleType.includes(resp)) {
              this.vehicleType.push(resp)
            }
            if (this.vehicleType.length > 1) {
              this.vehicle_type_id = 0;
            } else {
              this.vehicle_type_id = resp.id;
            }
          });
        }
        if (res.data.status == "error") {
          toastr.error(res.data.message);
          setTimeout(() => {
            window.location.reload();
          });
        }
      });

    },
    // add new customer
    addManually() {
      this.$refs.vehicleCustomInfo.sidebarOpen(true);
    },
    // get vehicle details
    vehicleDetail(info) {
      if (info) {
        this.model_year_id = info.year;
        this.make_id = info.make;
        this.model_id = info.model;
        this.vehicle_type_id = info.vehicle_type_id;

        this.getYearAll();

        this.getAllModel(this.make_id);
        this.getVehicleType(this.model_id);
        this.getMake(this.model_year_id);

      }
    },
    // submit form and check form is save as draft.
    formSubmitCheck(flag) {
      if (flag == 1) {
        swal({
          text: "Do you want to save as draft?",
          icon: "warning",
          buttons: true,
          dangerMode: false,
          buttons: {
            confirm: {
              text: "Save in draft",
              visible: true,
              closeModal: true,
              className: "swal-special-button",
              allowOutsideClick: false,
            },
            cancel: {
              text: "Exit",
              visible: true,
              closeModal: true,
              className: "swal-special-button--cancel",

            },
          }

        }).then((willExit) => {
          if (willExit) {
            this.formSubmit(flag); // submit form 
          } else {
            window.location.href = "/customer/appointment";
          }
        });
      }
    },
    // select date time
    selectedDateTime(dateTime) {
      this.start_date = dateTime;
    },
    // submit form 
    formSubmit(flag) {

      this.error = [];
      this.make_idError = '';
      this.model_idError = '';
      this.customer_idError = "";
      this.model_year_idError = '';
      this.vehicle_type_idError = '';
      this.start_dateError = "";
      //  validation on submit form
      if (this.make_id == 0 || this.make_id == '') {
        this.make_idError = "Please select make";
        this.error.push(this.make_idError);
      }
      if (this.isCustomer == 1) {
        if (this.customer_id == 0 || this.customer_id == null) {
          this.customer_idError = "Please select customer";
          this.error.push(this.customer_idError);
        }
      }

      if (this.isCustomer == 2) {
        if (this.customer_name == "" || this.customer_name == null) {
          this.customer_name_error = "Please enter customer name";
          this.error.push(this.customer_name_error);
        }
      }

      if (this.model_id == 0 || this.model_id == '') {
        this.model_idError = "Please select model";
        this.error.push(this.model_idError);
      }

      if (this.model_year_id == 0 || this.model_year_id == '') {
        this.model_year_idError = "Please select year";
        this.error.push(this.model_year_idError);
      }

      if (this.vehicle_type_id == 0 || this.vehicle_type_id == '') {
        this.vehicle_type_idError = "Please select vehicle type ";
        this.error.push(this.vehicle_type_idError);
      }


      if (this.start_date == null || this.start_date == '') {
        this.start_dateError = "Please select Date & Time";
        this.error.push(this.start_dateError);
      }

      if (this.error.length == 0) {

        let formData = {

          make_id: this.make_id,
          make_model_id: this.model_id,
          model_year_id: this.model_year_id,
          vehicle_type_id: this.vehicle_type_id,
          start_date: this.start_date,
          appointmentCustomerId: this.appointmentCustomerId,
          customer_name: this.customer_name,
          customer_phone: this.customer_phone,
          customer_email: this.customer_email,
          appointmentCustomerId: this.appointmentCustomerId,
          customer_id: this.customer_id,
          id: this.id,
          isCustomer: this.isCustomer,
        };

        if (this.navBar) {
          formData['step'] = this.navBar;
        }


        // call api for submit form 
        axios.post('/customer/save-appointment-basic-info', formData).then((resp) => {
          if (resp.data.status == 200) {
            if (flag == 1) {
              toastr.success("Appointment Basic information has been created successfully");
              setTimeout(() => {
                window.location.href = "/customer/appointment";
              }, 3000);
            } else {
              window.location.href = `${resp.data.url}`;
            }

          }
          // server side validation
          if (resp.data.status == "error") {
            this.error = [];
            this.make_idError = '';
            this.model_idError = '';
            this.customer_idError = "";
            this.model_year_idError = '';
            this.vehicle_type_idError = '';
            this.start_dateError = "";

            let messages = resp.data.messages;
            if (messages.model_id) {
              this.model_idError = messages.model_id;
              this.error.push(this.model_idError);
            }
            if (messages.start_end) {
              this.start_endError = messages.start_end;
              this.error.push(this.start_endError);
            }

            if (messages.make_id) {
              this.make_idError = messages.make_id;
              this.error.push(this.make_idError);
            }

            if (messages.customer_id) {
              this.customer_idError = messages.customer_id;
              this.error.push(this.customer_idError);
            }

            if (messages.model_year_id) {
              this.model_year_idError = messages.model_year_id;
              this.error.push(this.model_year_idError);
            }

            if (messages.vehicle_type_id) {
              this.vehicle_type_idError = messages.vehicle_type_id;
              this.error.push(this.vehicle_type_idError);
            }

          }
        })
      }




    }
  }

}
</script>
