<template>
    <!-- bg-[#000000d1] -->
    <div v-if="loadingPage"
        class="fixed inset-0 bg-black  overflow-hidden flex items-center justify-center loading-cirle-gif pre-loading w-full h-full z-50">
        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="50" height="50vh" viewBox="0 0 24 24">
            <g class="spinner_GuJz">
                <circle cx="3" cy="12" r="2" stroke="#ffffff" />
                <circle cx="21" cy="12" r="2" stroke="#ffffff" />
                <circle cx="12" cy="21" r="2" stroke="#ffffff" />
                <circle cx="12" cy="3" r="2" stroke="#ffffff" />
                <circle cx="5.64" cy="5.64" r="2" stroke="#ffffff" />
                <circle cx="18.36" cy="18.36" r="2" stroke="#ffffff" />
                <circle cx="5.64" cy="18.36" r="2" stroke="#ffffff" />
                <circle cx="18.36" cy="5.64" r="2" stroke="#ffffff" />
            </g>
        </svg>

    </div>
    <!-- bg-[#000000d1] -->
    <div v-if="loading" id="loader"
        class="absolute inset-0 bg-black  overflow-hidden flex items-center justify-center loading-cirle-gif pre-loading w-full h-full z-50">
        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="50" height="50vh" viewBox="0 0 24 24">
            <g class="spinner_GuJz">
                <circle cx="3" cy="12" r="2" stroke="#ffffff" />
                <circle cx="21" cy="12" r="2" stroke="#ffffff" />
                <circle cx="12" cy="21" r="2" stroke="#ffffff" />
                <circle cx="12" cy="3" r="2" stroke="#ffffff" />
                <circle cx="5.64" cy="5.64" r="2" stroke="#ffffff" />
                <circle cx="18.36" cy="18.36" r="2" stroke="#ffffff" />
                <circle cx="5.64" cy="18.36" r="2" stroke="#ffffff" />
                <circle cx="18.36" cy="5.64" r="2" stroke="#ffffff" />
            </g>
        </svg>

    </div>


     <!-- bg-[#000000d1] -->
     <div v-if="onlineLoading" id="loader"
        class="absolute inset-0 bg-white  overflow-hidden flex items-center justify-center loading-cirle-gif pre-loading w-full h-screen z-50">
        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="50" height="50vh" viewBox="0 0 24 24">
            <g class="spinner_GuJz">
                <circle cx="3" cy="12" r="2" stroke="#ffffff" />
                <circle cx="21" cy="12" r="2" stroke="#ffffff" />
                <circle cx="12" cy="21" r="2" stroke="#ffffff" />
                <circle cx="12" cy="3" r="2" stroke="#ffffff" />
                <circle cx="5.64" cy="5.64" r="2" stroke="#ffffff" />
                <circle cx="18.36" cy="18.36" r="2" stroke="#ffffff" />
                <circle cx="5.64" cy="18.36" r="2" stroke="#ffffff" />
                <circle cx="18.36" cy="5.64" r="2" stroke="#ffffff" />
            </g>
        </svg>

    </div>

</template>
<script>
export default {
    props: ['loading', 'loadingPage','onlineLoading'],

}
</script>
