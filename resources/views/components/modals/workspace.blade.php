<div x-show="activeWorkspaceTicket && activeWorkspaceTicket.ticket_id" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">

    <template x-if="activeWorkspaceTicket && activeWorkspaceTicket.ticket_id">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[92vh] flex flex-col border border-slate-200 overflow-hidden"
            @click.outside="closeWorkspace()">

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <div class="flex items-center gap-3">
                    <span class="font-mono font-bold text-xs px-2.5 py-1 rounded bg-indigo-100 text-indigo-800"
                        x-text="activeWorkspaceTicket.ticket_id"></span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900" x-text="activeWorkspaceTicket.subject"></h2>
                        <p class="text-[11px] text-slate-500">Author: <strong class="text-slate-800" x-text="activeWorkspaceTicket.author_name"></strong> (<span x-text="activeWorkspaceTicket.author_id"></span>)</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click.stop="assignTicketToSelf()"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 flex items-center gap-1.5 transition cursor-pointer">
                        <i class="ph-bold" :class="assigningTicket ? 'ph-spinner animate-spin text-indigo-600' : 'ph-user-check text-indigo-600'"></i>
                        <span x-text="activeWorkspaceTicket.assigned_name ? 'Assigned: ' + activeWorkspaceTicket.assigned_name : (assigningTicket ? 'Assigning...' : 'Assign to Me')"></span>
                    </button>
                    <button type="button" @click="closeWorkspace()" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">
                        <i class="ph ph-x"></i>
                    </button>
                </div>
            </div>

            <!-- Workspace Columns -->
            <div class="p-6 overflow-y-auto grid grid-cols-1 md:grid-cols-12 gap-6 flex-1">
                <!-- Left: Settings & Notes (5 cols) -->
                <div class="md:col-span-5 space-y-4 border-r border-slate-100 pr-0 md:pr-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Category (AI Detected)</label>
                        <select x-model="activeWorkspaceTicket.category" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium">
                            <option value="Royalty & Payments">Royalty & Payments</option>
                            <option value="ISBN & Metadata Issues">ISBN & Metadata Issues</option>
                            <option value="Printing & Quality">Printing & Quality</option>
                            <option value="Distribution & Availability">Distribution & Availability</option>
                            <option value="Book Status & Production Updates">Book Status & Production Updates</option>
                            <option value="General Inquiry">General Inquiry</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Priority Score</label>
                        <select x-model="activeWorkspaceTicket.priority" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium">
                            <option value="Critical">Critical</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ticket Status</label>
                        <select x-model="activeWorkspaceTicket.status" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium">
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Internal Notes</label>
                        <textarea x-model="activeWorkspaceTicket.internal_notes" rows="4" placeholder="Private ops notes..."
                            class="w-full px-3 py-2 bg-yellow-50/50 border border-yellow-200 rounded-lg text-xs text-slate-800"></textarea>
                    </div>
                </div>

                <!-- Right: Discussion History & AI Studio (7 cols) -->
                <div class="md:col-span-7 space-y-4 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="ph-bold ph-chats-circle text-indigo-600"></i> Ticket Discussion History
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono" x-text="(activeWorkspaceTicket.messages || []).length + ' messages'"></span>
                        </div>

                        <!-- Chat Messages Preview -->
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-3 max-h-56 overflow-y-auto mb-3">
                            <template x-for="(msg, mIdx) in (activeWorkspaceTicket.messages || [])" :key="mIdx">
                                <div class="flex flex-col" :class="msg.sender === 'author' ? 'items-start mr-8' : 'items-end ml-8'">
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mb-0.5 px-1">
                                        <span class="font-bold" :class="msg.sender === 'author' ? 'text-indigo-700' : 'text-emerald-700'"
                                            x-text="msg.name || (msg.sender === 'author' ? activeWorkspaceTicket.author_name : 'BookLeaf Operations')"></span>
                                        <span>•</span>
                                        <span x-text="formatDate(msg.timestamp)"></span>
                                        <template x-if="mIdx === activeWorkspaceTicket.messages.length - 1 && msg.sender === 'author'">
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] bg-rose-100 text-rose-700 font-bold border border-rose-200">Needs Response</span>
                                        </template>
                                    </div>
                                    <div class="p-3 rounded-xl text-xs leading-relaxed whitespace-pre-line shadow-2xs"
                                        :class="msg.sender === 'author' ? 'bg-white border border-slate-200 text-slate-800 rounded-tl-xs' : 'bg-indigo-600 text-white rounded-tr-xs'">
                                        <p x-text="msg.message"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- AI Suggested Draft Box -->
                    <div class="pt-1">
                        <div class="flex justify-between items-center mb-1.5">
                            <div class="flex items-center gap-1.5">
                                <i class="ph-bold ph-magic-wand text-indigo-600"></i>
                                <label class="text-xs font-bold text-slate-800">Response to Author</label>
                            </div>
                            <button type="button" @click="regenerateAIDraft()" :disabled="regeneratingDraft"
                                class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 cursor-pointer disabled:opacity-50">
                                <i class="ph-bold" :class="regeneratingDraft ? 'ph-spinner animate-spin' : 'ph-arrows-clockwise'"></i>
                                <span x-text="regeneratingDraft ? 'Drafting...' : 'Regenerate Draft (Recent Chat Aware)'"></span>
                            </button>
                        </div>

                        <textarea x-model="activeWorkspaceTicket.ai_draft_response" rows="6"
                            placeholder="Type official response here..."
                            class="w-full p-3 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 font-sans focus:ring-2 focus:ring-indigo-500 focus:outline-none leading-relaxed"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2.5">
                        <button type="button" @click="closeWorkspace()" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" @click="sendResponseToAuthor()" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition flex items-center gap-2 cursor-pointer">
                            <i class="ph-bold ph-paper-plane-tilt"></i>
                            <span>Send Response & Mark Resolved</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>