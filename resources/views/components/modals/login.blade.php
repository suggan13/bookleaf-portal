<div x-show="showLoginModal" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200"
        @click.outside="showLoginModal = false">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Sign in to your account</h3>
            <button type="button" @click="showLoginModal = false" class="text-slate-400 hover:text-slate-600 font-bold cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form @submit.prevent="loginManual()" class="mt-4 space-y-4">
            <div x-show="loginError" class="p-2.5 rounded-lg bg-rose-50 text-rose-700 text-xs" x-text="loginError"></div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email address</label>
                <input type="email" x-model="loginEmail" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                <input type="password" x-model="loginPassword" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs">
            </div>
            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition cursor-pointer">
                Sign In
            </button>
        </form>
    </div>
</div>