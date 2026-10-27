<header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 gap-4">

            <!-- Brand / Logo -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-700 to-emerald-500 flex items-center justify-center text-white shadow-sm shadow-emerald-500/20">
                    <i class="ph-bold ph-book-open-text text-xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-lg tracking-tight text-slate-900">BookLeaf</span>
                        <span class="text-xs uppercase tracking-wider text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200"
                            x-text="!currentUser ? 'Support Portal' : (currentUser.role === 'admin' ? 'Ops Desk' : 'Author Portal')"></span>
                    </div>
                    <p class="text-[11px] text-slate-500 hidden sm:block">AI-Assisted Author Support & Communication</p>
                </div>
            </div>

            <!-- Center: Inspect Author Pills (ADMIN ONLY) -->
            <div x-show="currentUser && currentUser.role === 'admin'" x-cloak class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
                <span class="px-2 text-[10px] uppercase font-bold tracking-wider text-slate-400 hidden sm:inline">Inspect Author:</span>

                <button type="button" @click="inspectAuthor('AUTH001')"
                    class="px-2.5 py-1 rounded-lg transition text-xs flex items-center gap-1 text-slate-700 bg-white shadow-xs hover:bg-slate-50 cursor-pointer">
                    <i class="ph-bold ph-user text-emerald-600"></i>
                    <span>Priya (AUTH001)</span>
                </button>

                <button type="button" @click="inspectAuthor('AUTH002')"
                    class="px-2.5 py-1 rounded-lg transition text-xs flex items-center gap-1 text-slate-700 bg-white shadow-xs hover:bg-slate-50 cursor-pointer">
                    <i class="ph-bold ph-user text-indigo-600"></i>
                    <span x-text="authors.find(a => a.author_id === 'AUTH002')?.name ? authors.find(a => a.author_id === 'AUTH002').name.split(' ')[0] + ' (AUTH002)' : 'AUTH002'"></span>
                </button>

                <button type="button" @click="inspectAuthor('AUTH005')"
                    class="px-2.5 py-1 rounded-lg transition text-xs flex items-center gap-1 text-slate-700 bg-white shadow-xs hover:bg-slate-50 cursor-pointer">
                    <i class="ph-bold ph-clock-countdown text-amber-600"></i>
                    <span>Sunita (In Prod)</span>
                </button>
            </div>

            <!-- Right: Active User Chip & Logout / Sign In -->
            <div class="flex items-center gap-3 shrink-0">
                <template x-if="currentUser">
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden lg:block">
                            <div class="text-xs font-bold text-slate-900" x-text="currentUser.name"></div>
                            <div class="text-[11px] text-slate-500 font-mono" x-text="currentUser.email"></div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider flex items-center gap-1"
                            :class="currentUser.role === 'admin' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'">
                            <i class="ph" :class="currentUser.role === 'admin' ? 'ph-crown' : 'ph-feather'"></i>
                            <span x-text="currentUser.role === 'admin' ? 'Ops Admin' : (currentUser.author_id || 'Author')"></span>
                        </span>

                        <button type="button" @click="logout()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition border border-slate-200 cursor-pointer"
                            title="Sign out of account">
                            <i class="ph ph-sign-out text-sm"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </div>
                </template>
                <template x-if="!currentUser">
                    <button type="button" @click="showLoginModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition cursor-pointer">
                        <i class="ph ph-lock-key"></i>
                        <span>Sign In</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Author Tabs (My Books, Raise Query, My Tickets) -->
        <div x-show="currentUser && currentUser.role === 'author'" x-cloak class="flex items-center gap-6 text-xs font-medium border-t border-slate-100 pt-2">
            <button type="button" @click="authorTab = 'books'"
                :class="authorTab === 'books' ? 'text-emerald-700 font-bold border-b-2 border-emerald-600 pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'"
                class="flex items-center gap-1.5 transition cursor-pointer">
                <i class="ph ph-books text-base"></i>
                <span>My Books & Royalties</span>
            </button>
            <button type="button" @click="authorTab = 'raise_query'"
                :class="authorTab === 'raise_query' ? 'text-emerald-700 font-bold border-b-2 border-emerald-600 pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'"
                class="flex items-center gap-1.5 transition cursor-pointer">
                <i class="ph ph-paper-plane-tilt text-base"></i>
                <span>Raise Support Query</span>
            </button>
            <button type="button" @click="authorTab = 'my_tickets'"
                :class="authorTab === 'my_tickets' ? 'text-emerald-700 font-bold border-b-2 border-emerald-600 pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'"
                class="flex items-center gap-1.5 transition cursor-pointer">
                <i class="ph ph-chat-circle-dots text-base"></i>
                <span>My Tickets (<span x-text="authorTickets.length"></span>)</span>
            </button>
        </div>

        <!-- Admin Tabs (Ticket Queue, Directory) -->
        <div x-show="currentUser && currentUser.role === 'admin'" x-cloak class="flex items-center gap-6 text-xs font-medium border-t border-slate-100 pt-2">
            <button type="button" @click="adminTab = 'tickets'"
                :class="adminTab === 'tickets' ? 'text-indigo-700 font-bold border-b-2 border-indigo-600 pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'"
                class="flex items-center gap-1.5 transition cursor-pointer">
                <i class="ph ph-ticket text-base"></i>
                <span>Ticket Queue (<span x-text="tickets.filter(t => t.status === 'Open' || t.status === 'In Progress').length"></span> Active)</span>
            </button>
            <button type="button" @click="adminTab = 'directory'"
                :class="adminTab === 'directory' ? 'text-indigo-700 font-bold border-b-2 border-indigo-600 pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'"
                class="flex items-center gap-1.5 transition cursor-pointer">
                <i class="ph ph-users-three text-base"></i>
                <span>Authors & Catalog Directory</span>
            </button>
        </div>
    </div>
</header>