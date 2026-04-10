<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const url = ref('');
const isScanning = ref(false);
const result = ref(null);
const error = ref(null);
const hasPaid = ref(false);

onMounted(() => {
    // Ping the backend to wake it up from sleep mode
    axios.get('/api/wakeup').catch(() => {});
});

const loadRazorpayScript = () => {
    return new Promise((resolve) => {
        if (window.Razorpay) {
            resolve(true);
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.onload = () => resolve(true);
        script.onerror = () => resolve(false);
        document.body.appendChild(script);
    });
};

const payWithRazorpay = async () => {
    try {
        const scriptLoaded = await loadRazorpayScript();
        if (!scriptLoaded) {
            alert('Failed to load Razorpay SDK. Please check your connection.');
            return;
        }

        const { data } = await axios.post('/payment/order');
        
        const options = {
            key: data.key,
            amount: data.amount,
            currency: data.currency,
            name: "PrivacyGuard AI",
            description: "Full GDPR Audit",
            order_id: data.order_id,
            handler: async function (response) {
                try {
                    const verifyResponse = await axios.post('/payment/verify', {
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature
                    });
                    
                    if(verifyResponse.data.success) {
                        alert(verifyResponse.data.message);
                        hasPaid.value = true;
                        // Here you can unlock features or redirect
                    }
                } catch (verifyError) {
                    alert("Payment verification failed.");
                }
            },
            prefill: {
                name: data.name,
                email: data.email
            },
            theme: {
                color: "#1e3a8a" // Tailwind blue-900
            }
        };
        
        const rzp = new window.Razorpay(options);
        rzp.on('payment.failed', function (response){
            alert("Payment failed: " + response.error.description);
        });
        rzp.open();
    } catch (err) {
        alert("Unable to initiate payment.");
    }
};

const startScan = async () => {
    if (!url.value) {
        error.value = "Please enter a valid URL.";
        return;
    }
    
    isScanning.value = true;
    error.value = null;
    result.value = null;

    try {
        // Calling the Python FastAPI backend via the Laravel proxy or directly
        // Here we call the local Laravel route which proxies to Python securely
        const response = await axios.post('/api/scan', { url: url.value }, { timeout: 180000 });
        result.value = response.data;
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to analyze the website. Please ensure the URL is accessible.";
    } finally {
        isScanning.value = false;
    }
};

const downloadPDF = async () => {
    if (!result.value) return;
    
    try {
        const response = await axios.post('/report/download', {
            url: url.value,
            score: result.value.compliance_score,
            risk: result.value.risk_level,
            summary: result.value.summary,
            missing_clauses: result.value.missing_clauses
        }, {
            responseType: 'blob' // Important for handling binary data like PDF
        });
        
        // Create a blob from the PDF stream
        const file = new Blob([response.data], {type: 'application/pdf'});
        
        // Build a URL from the file
        const fileURL = URL.createObjectURL(file);
        
        // Create a temporary anchor element and trigger download
        const a = document.createElement('a');
        a.href = fileURL;
        a.download = 'privacyguard-audit-report.pdf';
        document.body.appendChild(a);
        a.click();
        
        // Clean up
        URL.revokeObjectURL(fileURL);
        document.body.removeChild(a);
        
    } catch (err) {
        alert("Failed to download the PDF report. Make sure you are logged in and have upgraded.");
    }
};

const getRiskColor = (risk) => {
    switch(risk?.toLowerCase()) {
        case 'low': return 'text-emerald-600 bg-emerald-50';
        case 'medium': return 'text-amber-600 bg-amber-50';
        case 'high': return 'text-rose-600 bg-rose-50';
        case 'critical': return 'text-red-700 bg-red-100';
        default: return 'text-gray-600 bg-gray-50';
    }
};
</script>

<template>
    <Head title="Dashboard - PrivacyGuard AI" />

    <div class="min-h-screen bg-[#F8FAFC] font-sans">
        <!-- Top Navigation (Minimalist) -->
        <nav class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center shadow-sm">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-blue-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <span class="text-xl font-bold text-blue-950 tracking-tight">PrivacyGuard AI</span>
            </div>
            <div class="flex items-center space-x-6 text-sm font-medium text-gray-500">
                <a href="#" class="hover:text-blue-900 transition-colors">Audits</a>
                <a href="#" class="hover:text-blue-900 transition-colors">Settings</a>
                <div class="w-8 h-8 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center text-blue-900 font-bold">
                    JD
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-5xl mx-auto px-6 py-16">
            <!-- Header -->
            <div class="text-center mb-16">
                <h1 class="text-4xl font-extrabold text-blue-950 mb-4 tracking-tight">AI-Powered GDPR Audit</h1>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto">
                    Enter a website URL below. Our AI Engine will locate the privacy policy, analyze it against EU data protection standards, and generate a comprehensive remediation report.
                </p>
            </div>

            <!-- Input Section -->
            <div class="bg-white rounded-2xl shadow-xl shadow-blue-900/5 p-2 flex items-center max-w-3xl mx-auto border border-gray-100">
                <div class="pl-6 pr-4 text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                </div>
                <input 
                    v-model="url" 
                    @keyup.enter="startScan"
                    type="url" 
                    placeholder="https://example.com" 
                    class="flex-1 py-4 px-2 text-lg text-gray-800 bg-transparent border-none focus:ring-0 placeholder-gray-300 outline-none"
                    :disabled="isScanning"
                />
                <button 
                    @click="startScan" 
                    :disabled="isScanning"
                    class="bg-blue-900 hover:bg-blue-800 text-white font-semibold py-4 px-8 rounded-xl transition-all duration-200 flex items-center space-x-2 disabled:opacity-70"
                >
                    <span v-if="!isScanning">Start Scan</span>
                    <span v-else>Scanning...</span>
                    <svg v-if="!isScanning" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                    <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>

            <!-- Upgrade/Payment Banner -->
            <div v-if="result && !hasPaid" class="max-w-3xl mx-auto mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <p class="text-blue-900 font-medium">Unlock full remediation reports and PDF exports.</p>
                </div>
                <button @click.prevent="payWithRazorpay" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-colors shadow-sm cursor-pointer">
                    Upgrade Now
                </button>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="max-w-3xl mx-auto mt-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg flex items-start space-x-3">
                <svg class="w-6 h-6 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-700 font-medium">{{ error }}</p>
            </div>

            <!-- Results Section -->
            <transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                <div v-if="result" class="mt-16 bg-white rounded-3xl shadow-xl shadow-blue-900/5 border border-gray-100 overflow-hidden">
                    
                    <!-- Result Header -->
                    <div class="px-10 py-8 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between bg-gradient-to-r from-white to-blue-50/30">
                        <div>
                            <h2 class="text-2xl font-bold text-blue-950">Audit Report</h2>
                            <p class="text-gray-500 mt-1 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                <span>{{ url }}</span>
                            </p>
                        </div>
                        <div class="mt-6 md:mt-0 flex space-x-4">
                            <!-- Score Card -->
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center min-w-[120px]">
                                <span class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-1">Score</span>
                                <div class="flex items-baseline space-x-1">
                                    <span class="text-3xl font-extrabold text-blue-900">{{ result.compliance_score }}</span>
                                    <span class="text-gray-400 font-medium">/100</span>
                                </div>
                            </div>
                            <!-- Risk Card -->
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center min-w-[120px]">
                                <span class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-1">Risk Level</span>
                                <span class="text-xl font-bold px-3 py-1 rounded-lg" :class="getRiskColor(result.risk_level)">
                                    {{ result.risk_level }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Result Body -->
                    <div class="px-10 py-8">
                        <h3 class="text-lg font-bold text-blue-950 mb-4">Executive Summary</h3>
                        <p class="text-gray-600 leading-relaxed bg-gray-50 p-6 rounded-2xl border border-gray-100 mb-10">
                            {{ result.summary }}
                        </p>

                        <!-- Missing Clauses (Paywalled) -->
                        <div class="relative overflow-hidden">
                            <h3 class="text-lg font-bold text-blue-950 mb-4 flex items-center space-x-2">
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span>Missing Clauses & Vulnerabilities</span>
                            </h3>
                            
                            <div :class="{ 'blur-sm select-none pointer-events-none': !hasPaid }" class="transition-all duration-500">
                                <div v-if="result.missing_clauses && result.missing_clauses.length > 0" class="space-y-3">
                                    <div v-for="(clause, index) in result.missing_clauses" :key="index" class="flex items-start space-x-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
                                        <div class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </div>
                                        <p class="text-gray-700">{{ clause }}</p>
                                    </div>
                                </div>
                                <div v-else class="p-6 text-center text-emerald-600 bg-emerald-50 rounded-2xl border border-emerald-100">
                                    <p class="font-medium">No missing clauses detected. The policy appears fully compliant.</p>
                                </div>
                            </div>

                            <!-- Pay to Unlock Overlay -->
                            <div v-if="!hasPaid" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/60 backdrop-blur-[2px]">
                                <div class="bg-white p-6 rounded-2xl shadow-xl border border-blue-100 text-center max-w-md mx-auto transform hover:scale-105 transition-transform duration-300">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Unlock Full Remediation Report</h4>
                                    <p class="text-sm text-gray-500 mb-6">See exactly which clauses are missing and get actionable steps to fix your GDPR compliance.</p>
                                    <button @click.prevent="payWithRazorpay" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-all cursor-pointer">
                                        Pay $29 to Unlock
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div v-if="hasPaid" class="mt-12 flex justify-end space-x-4">
                            <button @click="downloadPDF" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-medium rounded-xl hover:bg-gray-50 transition-colors cursor-pointer flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <span>Download PDF</span>
                            </button>
                            <button class="px-6 py-3 bg-blue-900 text-white font-medium rounded-xl hover:bg-blue-800 transition-colors shadow-lg shadow-blue-900/20">
                                Send Remediation Plan
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </main>
    </div>
</template>

<style>
/* Add any custom animations or overrides here */
</style>