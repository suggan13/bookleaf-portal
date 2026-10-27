<div x-show="authorTab === 'my_tickets'" x-cloak class="space-y-4">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Your Submitted Support Tickets</h2>
            <p class="text-xs text-slate-500">Live multi-turn conversation feed with BookLeaf Operations Team</p>
        </div>
    </div>

    <div x-show="authorTickets.length === 0" class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
        <i class="ph ph-chat-circle-slash text-3xl text-slate-300"></i>
        <h3 class="font-bold text-slate-800 text-sm mt-2">No Support Queries Raised</h3>
        <p class="text-xs text-slate-500 mt-1">If you have any questions, raise a ticket using the 'Raise Support Query' tab.</p>
    </div>

    <div class="space-y-4">
        <template x-for="ticket in authorTickets" :key="ticket.ticket_id">
            <div class="bg-white border rounded-xl p-5 shadow-sm transition hover:border-slate-300"
                :class="ticket.status === 'Resolved' ? 'border-emerald-200/80' : 'border-slate-200'">

                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-800" x-text="ticket.ticket_id"></span>
                        <h3 class="font-bold text-slate-900 text-sm" x-text="ticket.subject"></h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                            :class="statusColor(ticket.status)" x-text="ticket.status"></span>
                        <span class="text-[11px] text-slate-400" x-text="formatDate(ticket.created_at)"></span>
                    </div>
                </div>

                <!-- Associated Book -->
                <div class="pt-3 pb-2 text-xs text-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Associated Book:</span>
                        <span class="font-semibold text-slate-800" x-text="ticket.book_title"></span>
                    </div>
                    <template x-if="ticket.attachment">
                        <div class="flex items-center gap-1 text-[11px] text-slate-500">
                            <i class="ph-bold ph-paperclip text-slate-400"></i>
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono border border-slate-200" x-text="ticket.attachment"></span>
                        </div>
                    </template>
                </div>

                <!-- Chat History Feed -->
                <div class="mt-2 p-3.5 bg-slate-50/70 rounded-xl border border-slate-200/80 space-y-3 max-h-96 overflow-y-auto">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1.5">
                        <i class="ph-bold ph-chat-centered-text text-emerald-600"></i>
                        <span>Conversation History</span>
                    </div>

                    <template x-if="ticket.messages && ticket.messages.length > 0">
                        <div class="space-y-2.5">
                            <template x-for="(msg, mIdx) in ticket.messages" :key="mIdx">
                                <div class="flex flex-col" :class="msg.sender === 'author' ? 'items-start mr-8' : 'items-end ml-8'">
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-0.5 px-1">
                                        <span class="font-bold" :class="msg.sender === 'author' ? 'text-slate-700' : 'text-emerald-700'"
                                            x-text="msg.name || (msg.sender === 'author' ? 'You' : 'BookLeaf Operations')"></span>
                                        <span>•</span>
                                        <span x-text="formatDate(msg.timestamp)"></span>
                                    </div>
                                    <div class="p-3 rounded-xl text-xs leading-relaxed shadow-2xs whitespace-pre-line"
                                        :class="msg.sender === 'author' ? 'bg-white border border-slate-200 text-slate-800 rounded-tl-xs' : 'bg-emerald-600 text-white rounded-tr-xs'">
                                        <p x-text="msg.message"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!ticket.messages || ticket.messages.length === 0">
                        <div class="p-3 bg-white border border-slate-200 text-slate-800 rounded-xl text-xs" x-text="ticket.description"></div>
                    </template>
                </div>

                <!-- Reopen / Follow-up Actions -->
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <template x-if="ticket.status === 'Resolved'">
                        <div class="flex flex-wrap items-center justify-between gap-2 p-2.5 bg-emerald-50/70 border border-emerald-200 rounded-xl mb-3">
                            <button type="button" @click="closeTicketAsAuthor(ticket.ticket_id)"
                                class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                <i class="ph-bold ph-check-circle"></i>
                                <span>Accept Resolution & Close Ticket</span>
                            </button>
                            <span class="text-[11px] text-emerald-800 font-medium">Issue still not fixed? Reply below to automatically reopen this ticket.</span>
                        </div>
                    </template>

                    <template x-if="ticket.status === 'Closed'">
                        <div class="p-2.5 bg-slate-100 rounded-xl text-xs text-slate-500 flex items-center gap-2 border border-slate-200">
                            <i class="ph-bold ph-lock-key text-slate-400 text-sm"></i>
                            <span>This ticket has been marked as <strong>Closed</strong>. If you have another issue, please raise a new query.</span>
                        </div>
                    </template>

                    <template x-if="ticket.status !== 'Closed'">
                        <div class="space-y-2 mt-2">
                            <textarea x-model="ticketReplies[ticket.ticket_id]" rows="2"
                                :placeholder="ticket.status === 'Resolved' ? 'Not satisfied with this resolution? Type here to reopen this ticket...' : 'Write your follow-up message...'"
                                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                            <div class="flex justify-end">
                                <button type="button" @click="submitAuthorReply(ticket.ticket_id)"
                                    :disabled="submittingReply[ticket.ticket_id] || !ticketReplies[ticket.ticket_id]?.trim()"
                                    class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                    <i class="ph-bold" :class="submittingReply[ticket.ticket_id] ? 'ph-spinner animate-spin' : 'ph-paper-plane-tilt'"></i>
                                    <span x-text="ticket.status === 'Resolved' ? 'Reopen with Reply' : 'Send Follow-up'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>