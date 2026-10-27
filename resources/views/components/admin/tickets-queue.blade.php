<div x-show="adminTab === 'tickets'" x-cloak class="space-y-4">
    <!-- Filters Strip -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-2.5">
            <select x-model="ticketFilterStatus" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium">
                <option value="">All Statuses</option>
                <option value="Open">Open</option>
                <option value="In Progress">In Progress</option>
                <option value="Resolved">Resolved</option>
                <option value="Closed">Closed</option>
            </select>
            <select x-model="ticketFilterPriority" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium">
                <option value="">All Priorities</option>
                <option value="Critical">Critical</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
            <select x-model="ticketFilterCategory" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium">
                <option value="">All Categories</option>
                <option value="Royalty & Payments">Royalty & Payments</option>
                <option value="ISBN & Metadata Issues">ISBN & Metadata Issues</option>
                <option value="Printing & Quality">Printing & Quality</option>
                <option value="Distribution & Availability">Distribution & Availability</option>
                <option value="Book Status & Production Updates">Book Status & Production Updates</option>
                <option value="General Inquiry">General Inquiry</option>
            </select>
        </div>
        <input type="text" x-model="ticketSearch" placeholder="Search ticket, author, subject..."
            class="w-full sm:w-64 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
    </div>

    <!-- Master Queue Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Ticket ID & Subject</th>
                        <th class="px-4 py-3">Author & Book</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Status & Owner</th>
                        <th class="px-4 py-3">Submitted</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="t in filteredTickets" :key="t.ticket_id">
                        <tr class="hover:bg-slate-50/80 transition" :class="t.priority === 'Critical' && t.status !== 'Resolved' ? 'bg-rose-50/30' : ''">
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                    :class="priorityColor(t.priority)" x-text="t.priority"></span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-mono font-bold text-indigo-700 text-xs" x-text="t.ticket_id"></span>
                                <div class="font-semibold text-slate-900 mt-0.5" x-text="t.subject"></div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-800" x-text="t.author_name"></div>
                                <div class="text-[11px] text-slate-500 truncate max-w-[180px]" x-text="t.book_title"></div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px]" x-text="t.category"></span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                    :class="statusColor(t.status)" x-text="t.status"></span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500 text-[11px]" x-text="formatDate(t.created_at)"></td>
                            <td class="px-4 py-3.5 text-center">
                                <button type="button" @click="openTicketWorkspace(t)" class="px-3 py-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition cursor-pointer">
                                    Resolve
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>