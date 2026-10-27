<div x-show="authorTab === 'books'" x-cloak class="space-y-6">
    <!-- Financial Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">My Titles</span>
            <div class="text-2xl font-bold text-slate-900 mt-1" x-text="authorBooks.length"></div>
            <div class="text-[11px] text-slate-500 mt-0.5">Published & Active</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Copies Sold</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1" x-text="authorSummary.total_copies_sold.toLocaleString()"></div>
            <div class="text-[11px] text-emerald-700 mt-0.5">Amazon, Flipkart & Store</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Royalty Earned</span>
            <div class="text-2xl font-bold text-slate-900 mt-1" x-text="'₹' + authorSummary.total_royalty_earned.toLocaleString()"></div>
            <div class="text-[11px] text-slate-500 mt-0.5">80% author profit share</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Royalty Paid</span>
            <div class="text-2xl font-bold text-slate-700 mt-1" x-text="'₹' + authorSummary.royalty_paid.toLocaleString()"></div>
            <div class="text-[11px] text-slate-500 mt-0.5">Direct NEFT transfers</div>
        </div>
        <div class="bg-amber-50/70 p-4 rounded-xl border border-amber-200 shadow-sm col-span-2 lg:col-span-1">
            <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider">Pending Payout</span>
            <div class="text-2xl font-bold text-amber-700 mt-1" x-text="'₹' + authorSummary.royalty_pending.toLocaleString()"></div>
            <div class="text-[10px] text-amber-900 mt-0.5">Next cycle: 45 days post quarter</div>
        </div>
    </div>

    <!-- Books Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-3.5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">My Books Catalog</h2>
            <span class="text-xs text-slate-500" x-text="authorBooks.length + ' titles associated with ' + currentUser?.author_id"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-5 py-3">Book Title & ISBN</th>
                        <th class="px-4 py-3">Genre</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Published Date</th>
                        <th class="px-4 py-3 text-right">MRP</th>
                        <th class="px-4 py-3 text-right">Royalty/Copy</th>
                        <th class="px-4 py-3 text-right">Copies Sold</th>
                        <th class="px-4 py-3 text-right">Total Earned</th>
                        <th class="px-4 py-3 text-right">Pending</th>
                        <th class="px-4 py-3 text-center">Channels</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="book in authorBooks" :key="book.book_id">
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 text-sm" x-text="book.title"></div>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5">ISBN: <span x-text="book.isbn || 'Pending Allocation'"></span></div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600" x-text="book.genre"></td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold"
                                    :class="book.status === 'Published & Live' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                    x-text="book.status"></span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 font-mono text-[11px]" x-text="book.publication_date || book.published_date || 'In Production'"></td>
                            <td class="px-4 py-3.5 text-right font-medium" x-text="book.mrp ? '₹' + book.mrp : '—'"></td>
                            <td class="px-4 py-3.5 text-right font-semibold text-emerald-700" x-text="book.author_royalty_per_copy ? '₹' + book.author_royalty_per_copy : '—'"></td>
                            <td class="px-4 py-3.5 text-right font-mono" x-text="(book.total_copies_sold || 0).toLocaleString()"></td>
                            <td class="px-4 py-3.5 text-right font-bold text-slate-900" x-text="'₹' + (book.total_royalty_earned || 0).toLocaleString()"></td>
                            <td class="px-4 py-3.5 text-right font-bold text-amber-600" x-text="'₹' + (book.royalty_pending || 0).toLocaleString()"></td>
                            <td class="px-4 py-3.5 text-center">
                                <template x-for="ch in (book.available_on || [])" :key="ch">
                                    <span class="text-[9px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded mr-1" x-text="ch"></span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>