<div x-show="adminTab === 'directory'" x-cloak class="space-y-4">
    <div class="flex justify-between items-center">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Authors Catalog (<span x-text="authors.length"></span> Records)</h2>
        <input type="text" x-model="directorySearch" placeholder="Search authors..." class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs w-64">
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
            <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                <tr>
                    <th class="px-5 py-3">Author ID</th>
                    <th class="px-5 py-3">Name & Contact</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3 text-center">Titles</th>
                    <th class="px-4 py-3 text-right">Copies Sold</th>
                    <th class="px-4 py-3 text-right">Earned</th>
                    <th class="px-4 py-3 text-right">Pending Payout</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="author in filteredDirectoryAuthors" :key="author.author_id">
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 font-mono font-bold text-indigo-700" x-text="author.author_id"></td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900" x-text="author.name"></div>
                            <div class="text-[11px] text-slate-400 font-mono" x-text="author.email"></div>
                        </td>
                        <td class="px-4 py-3.5 text-slate-600" x-text="author.city"></td>
                        <td class="px-4 py-3.5 text-center font-bold text-slate-800" x-text="getAuthorBooksCount(author.author_id)"></td>
                        <td class="px-4 py-3.5 text-right font-mono" x-text="getAuthorSales(author.author_id).toLocaleString()"></td>
                        <td class="px-4 py-3.5 text-right font-semibold text-slate-900" x-text="'₹' + getAuthorEarned(author.author_id).toLocaleString()"></td>
                        <td class="px-4 py-3.5 text-right font-bold text-amber-600" x-text="'₹' + getAuthorPending(author.author_id).toLocaleString()"></td>
                        <td class="px-4 py-3.5 text-center">
                            <button type="button" @click="inspectAuthor(author.author_id)" class="px-2.5 py-1 text-[11px] font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded border border-indigo-200 transition cursor-pointer">
                                Inspect Author
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>