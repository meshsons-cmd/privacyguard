<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    message: String,
    session_id: String
});
</script>

<template>
    <Head title="Payment Successful" />
    <div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 font-sans">
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-gray-100 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100">
                <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <h2 class="mt-6 text-3xl font-extrabold text-blue-950">Payment Successful!</h2>
            <p class="mt-2 text-sm text-gray-500">
                {{ message }}
            </p>
            <div class="mt-8">
                <Link :href="route('dashboard')" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-blue-900 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-900 transition-colors">
                    Return to Dashboard
                </Link>
            </div>
        </div>
    </div>
</template>