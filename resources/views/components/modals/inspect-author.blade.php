<div x-show="inspectedAuthor" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col border border-slate-200 overflow-hidden"
        @click.outside="inspectedAuthor = null">

        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-base" x-text="inspectedAuthor?.name?.charAt(0)"></div>
                <div>
                    <h2 class="text-base font-bold text-slate-900" x-text="inspectedAuthor?.name"></h2>
                    <p class="text-xs text-slate-500" x-text="inspectedAuthor?.email + ' • ' + (inspectedAuthor?.city || 'India')"></p>
                </div>
            </div>
            <button type="button" @click="inspectedAuthor = null" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-5">
            <div class="grid grid-cols-3 gap-3">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] uppercase text-slate-500 font-bold">Total Copies Sold</span>
                    <div class="text-lg font-bold text-emerald-600 mt-0.5" x-text="getAuthorSales(inspectedAuthor?.author_id).toLocaleString()"></div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] uppercase text-slate-500 font-bold">Royalty Earned</span>
                    <div class="text-lg font-bold text-slate-900 mt-0.5" x-text="'₹' + getAuthorEarned(inspectedAuthor?.author_id).toLocaleString()"></div>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                    <span class="text-[10px] uppercase text-amber-800 font-bold">Pending Payout</span>
                    <div class="text-lg font-bold text-amber-700 mt-0.5" x-text="'₹' + getAuthorPending(inspectedAuthor?.author_id).toLocaleString()"></div>
                </div>
            </div>
        </div>

        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button type="button" @click="inspectedAuthor = null" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-200 rounded-lg cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>