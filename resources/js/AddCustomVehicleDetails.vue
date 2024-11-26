<template>
    <div>
        <SidebarComponent v-slot:default ref="sidebarOpen" @form-submit="submitForm()">

            <form method="POST">
                <div class="sidebar-body-content px-2 py-2">
                    <div class=" ">

                        <div class="mb-8">
                            <label for=" " class="block text-sm  leading-6 text-stone-300">Vehicle Year <span
                                    class="text-red-500">*</span></label>
                            <div class="mt-2.5 relative">
                                <input type="text" name="name" id="name" autocomplete="off" v-model="year"
                                    class="block w-full rounded-md border px-3.5 py-2 text-gray-500 text-base bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600   sm:leading-6"
                                    placeholder="Enter year..." maxlength="4" @keypress="isNumber($event)">

                            </div>
                            <p class="text-[10px] text-red-600" v-if="error.length > 0 && yearError != null">
                                {{ yearError }}
                            </p>
                        </div>

                        <div class="mb-8">
                            <label for=" " class="block text-sm  leading-6 text-stone-300">Vehicle Make <span
                                    class="text-red-500">*</span></label>
                            <div class="mt-2.5 relative">
                                <input type="text" name="name" id="name" autocomplete="off" v-model="make"
                                    class="block w-full rounded-md border px-3.5 py-2 text-gray-500 text-base bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600   sm:leading-6"
                                    placeholder="Enter make...">

                            </div>
                            <p class="text-[10px] text-red-600" v-if="error.length > 0 && makeError != null">
                                {{ makeError }}
                            </p>
                        </div>

                        <div class="mb-8">
                            <label for=" " class="block text-sm  leading-6 text-stone-300">Vehicle Model <span
                                    class="text-red-500">*</span></label>
                            <div class="mt-2.5 relative">
                                <input type="text" name="name" id="name" autocomplete="off" v-model="model"
                                    class="block w-full rounded-md border px-3.5 py-2 text-gray-500 text-base bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600   sm:leading-6"
                                    placeholder="Enter model...">

                            </div>
                            <p class="text-[10px] text-red-600" v-if="error.length > 0 && modelError != null">
                                {{ modelError }}
                            </p>
                        </div>

                        <div class="mb-8">
                            <label for=" " class="block text-sm  leading-6 text-stone-300">Vehicle Type <span
                                    class="text-red-500">*</span></label>
                            <div class="mt-2.5 relative">
                                <select type="text" name="name" id="name" autocomplete="off" v-model="vehicleType"
                                    class="block w-full rounded-md border px-3.5 py-2 text-gray-500 text-base bg-black shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600   sm:leading-6">
                                    <option value="0">Please select vehicle type</option>
                                    <option v-for="vtype in vehicleTypeArr" :key="vtype.id" :value="vtype.id">{{
            vtype.type }}</option>
                                </select>

                            </div>
                            <p class="text-[10px] text-red-600" v-if="error.length > 0 && vehicleTypeError != null">
                                {{ vehicleTypeError }}
                            </p>
                        </div>

                    </div>

                </div>

            </form>
        </SidebarComponent>
    </div>
</template>
<script type="text/javascript">
import axios from 'axios';
import SidebarComponent from './SidebarComponent.vue';
import swal from 'sweetalert';
export default {
    data() {
        return {
            year: null,
            yearError: "",
            error: [],
            make: '',
            model: '',
            makeError: '',
            modelError: '',
            vehicleType: 0,
            vehicleTypeError: '',
            vehicleTypeArr: [],
        }
    },
    components: {
        SidebarComponent
    },
    methods: {
        getVehicleType() {
            this.vehicleTypeArr = [];
            axios.get(`/customer/get-vehicle-type-all`).then((res) => {
                if (res.data.status == 200) {
                    res.data.vType.map((resp) => {
                        if (!this.vehicleTypeArr.includes(resp)) {
                            this.vehicleTypeArr.push(resp);
                        }
                    });
                }
            });
        },
        sidebarOpen(params) {
            this.getVehicleType();
            this.yearError = "";
            this.makeError = "";
            this.modelError = "";
            this.vehicleTypeError = "";
            this.error = [];

            this.$refs.sidebarOpen.toggleCollapse(params, "Vehicle Details", "Enter vehicle details below");
        },
        // type number only
        isNumber: function (evt) {
            evt = evt ? evt : window.event;
            let charCode = evt.which ? evt.which : evt.keyCode;
            if (
                charCode > 31 &&
                (charCode < 48 || charCode > 57) &&
                charCode !== 46
            ) {
                evt.preventDefault();
            } else {
                return true;
            }
        },
        // submit form 
        submitForm() {
            this.yearError = "";
            this.makeError = "";
            this.modelError = "";
            this.vehicleTypeError = "";
            this.error = [];
            // validation form 
            if (this.year == null || this.year == '') {
                this.yearError = "Please enter vehicle year";
                this.error.push(this.yearError);
            }

            if (this.make == null || this.make == '') {
                this.makeError = "Please enter vehicle make";
                this.error.push(this.makeError);
            }

            if (this.model == null || this.model == '') {
                this.modelError = "Please enter vehicle model";
                this.error.push(this.modelError);
            }
            if (this.vehicleType == null || this.vehicleType == '') {
                this.vehicleTypeError = "Please enter vehicle type";
                this.error.push(this.vehicleTypeError);
            }

            if (this.error.length === 0) {
                let formData = {
                    make: this.make,
                    model: this.model,
                    year: this.year,
                    vehicle_type: this.vehicleType,

                }
                // submit from to server
                axios.post(`/customer/add-custom-vehicle-detail`, formData).then((resp) => {
                    if (resp.data.status == 200) {
                        let data = {
                            year: resp.data.year,
                            make: resp.data.make,
                            model: resp.data.model,
                            vehicle_type_id: resp.data.vehicle_type_id,
                        };

                        this.$emit('vehicle-detail', data); // emit event to parent component

                        toastr.success(resp.data.message);
                        this.$refs.sidebarOpen.toggleCollapse(false);
                    }
                    // server side validation
                    if (resp.data.status == "error") {
                        let message = resp.data.messages;
                        if (message.make) {
                            this.makeError = message.make;
                            this.error.push(this.makeError);
                        }
                        if (message.model) {
                            this.modelError = message.model;
                            this.error.push(this.modelError);
                        }
                        if (message.year) {
                            this.yearError = message.year;
                            this.error.push(this.yearError);
                        }

                        if (message.vehicle_type) {
                            this.vehicleTypeError = message.vehicle_type;
                            this.error.push(this.vehicleTypeError);
                        }

                    }

                });
            }
        }
    }
}
</script>
