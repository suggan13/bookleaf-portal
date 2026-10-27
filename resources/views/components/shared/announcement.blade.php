<div class="bg-slate-900 text-slate-300 px-4 py-2 text-xs flex flex-wrap justify-between items-center gap-2 border-b border-slate-800">
    <div class="flex items-center gap-2.5">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 live-pulse"></span>
            AI ENGINE ACTIVE
        </span>
        <span class="hidden sm:inline">BookLeaf Policy Grounded: 80/20 Net Split • 45-Day Quarter Payout • ₹1,000 Threshold • Free Verified Reprints</span>
    </div>
    <div class="flex items-center gap-3 text-[11px] text-slate-400">
        <span class="inline-flex items-center gap-1">
            <i class="ph ph-shield-check text-emerald-400"></i>
            Session Status: <span class="text-emerald-300 font-mono font-semibold" x-text="currentUser ? (currentUser.role === 'admin' ? 'Ops Admin Workspace' : currentUser.name + ' (' + currentUser.author_id + ')') : 'Guest / Logged Out'"></span>
        </span>
    </div>
</div>