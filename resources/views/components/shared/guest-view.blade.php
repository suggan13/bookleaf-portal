<div class="max-w-2xl mx-auto py-12 text-center">
    <div class="w-16 h-16 mx-auto mb-4 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center text-3xl">
        <i class="ph ph-shield-check"></i>
    </div>
    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">BookLeaf Author Support Portal</h1>
    <p class="mt-2 text-slate-600 text-sm">Sign in to view published titles, track quarterly royalties, or raise an AI-assisted query.</p>

    <div class="mt-8 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-left max-w-xl mx-auto">
        <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
            <i class="ph-bold ph-key text-emerald-600"></i>
            <span>Click Any Account to Sign In Directly</span>
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button type="button" @click="switchRole('admin')" class="p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/60 hover:bg-indigo-100 text-left transition cursor-pointer">
                <div class="text-[10px] font-bold text-indigo-700 uppercase">BookLeaf Operations</div>
                <div class="text-xs font-bold text-slate-900 mt-1">admin@bookleafpub.com</div>
                <div class="text-[11px] text-slate-500 font-mono mt-0.5">Admin Ops Desk →</div>
            </button>
            <button type="button" @click="switchRole('AUTH001')" class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/60 hover:bg-emerald-100 text-left transition cursor-pointer">
                <div class="text-[10px] font-bold text-emerald-700 uppercase">Priya Sharma (AUTH001)</div>
                <div class="text-xs font-bold text-slate-900 mt-1">priya.sharma@email.com</div>
                <div class="text-[11px] text-slate-500 font-mono mt-0.5">Author Isolated View →</div>
            </button>
            <button type="button" @click="switchRole('AUTH002')" class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/60 hover:bg-emerald-100 text-left transition cursor-pointer">
                <div class="text-[10px] font-bold text-emerald-700 uppercase">Rohit Kapoor (AUTH002)</div>
                <div class="text-xs font-bold text-slate-900 mt-1">rohit.kapoor@email.com</div>
                <div class="text-[11px] text-slate-500 font-mono mt-0.5">Author Isolated View →</div>
            </button>
            <button type="button" @click="switchRole('AUTH005')" class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/60 hover:bg-amber-100 text-left transition cursor-pointer">
                <div class="text-[10px] font-bold text-amber-800 uppercase">Sunita Deshmukh (AUTH005)</div>
                <div class="text-xs font-bold text-slate-900 mt-1">sunita.deshmukh@email.com</div>
                <div class="text-[11px] text-amber-700 font-mono mt-0.5">In Production Pipeline →</div>
            </button>
        </div>
    </div>
</div>