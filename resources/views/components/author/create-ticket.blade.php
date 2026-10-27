<div x-show="authorTab === 'raise_query'" x-cloak class="max-w-3xl mx-auto">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Submit a Support Query</h2>
                <p class="text-xs text-slate-500">Our operations team will investigate and resolve your issue within 24-48 business hours.</p>
            </div>
            <span class="px-2.5 py-1 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">24-48 HR SLA</span>
        </div>

        <form @submit.prevent="submitAuthorTicket()" class="p-6 space-y-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Regarding Book / Project <span class="text-rose-500">*</span></label>
                <select x-model="ticketForm.book_id" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="general">General / Account Level Inquiry</option>
                    <template x-for="b in authorBooks" :key="b.book_id">
                        <option :value="b.book_id" x-text="b.title + ' (' + (b.isbn || b.status) + ')'"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Subject Line <span class="text-rose-500">*</span></label>
                <input type="text" x-model="ticketForm.subject" required placeholder="e.g. Question regarding Q2 royalty disbursement delay"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Detailed Description <span class="text-rose-500">*</span></label>
                <textarea x-model="ticketForm.description" required rows="4" placeholder="Explain your question or issue in detail."
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Attach Supporting Document / Photo (Optional)</label>
                <div class="flex items-center gap-3 p-3 bg-slate-50 border border-dashed border-slate-300 rounded-xl">
                    <label class="px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold cursor-pointer border border-slate-200 shadow-2xs transition flex items-center gap-2">
                        <i class="ph-bold ph-paperclip"></i>
                        <span>Choose File</span>
                        <input type="file" class="hidden" @change="attachedFileName = $event.target.files[0]?.name || ''">
                    </label>
                    <span class="text-xs text-slate-600 truncate" x-text="attachedFileName ? 'Selected: ' + attachedFileName : 'No file chosen (PNG, JPG, PDF up to 5MB)'"></span>
                    <button type="button" x-show="attachedFileName" @click="attachedFileName = ''" class="text-rose-500 text-xs font-bold">✕ Remove</button>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button type="submit" :disabled="submittingTicket"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
                    <i class="ph-bold" :class="submittingTicket ? 'ph-spinner animate-spin' : 'ph-paper-plane-tilt'"></i>
                    <span x-text="submittingTicket ? 'Submitting...' : 'Submit Ticket'"></span>
                </button>
            </div>
        </form>
    </div>
</div>