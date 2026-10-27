<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BookLeaf Publishing — Author Support & Communication Portal</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        @keyframes pulse-subtle {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .live-pulse {
            animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased font-sans min-h-screen flex flex-col"
    x-data="bookleafApp()"
    x-init="init()">

    <!-- TOP GLOBAL ANNOUNCEMENT -->
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

    <!-- MAIN APP HEADER -->
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

                <!-- Right: Active User Chip & Role Action -->
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

            <!-- GATED SECONDARY NAVIGATION (AUTHOR VIEW) -->
            <div x-show="currentUser && currentUser.role === 'author'" x-cloak class="flex items-center gap-6 text-xs font-medium border-t border-slate-100 pt-2 pb-0">
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

            <!-- GATED SECONDARY NAVIGATION (ADMIN VIEW) -->
            <div x-show="currentUser && currentUser.role === 'admin'" x-cloak class="flex items-center gap-6 text-xs font-medium border-t border-slate-100 pt-2 pb-0">
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

    <!-- CONTENT WRAPPER -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">

        <!-- STATE 1: GUEST / NOT LOGGED IN -->
        <div x-show="!currentUser" class="max-w-2xl mx-auto py-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center text-3xl">
                <i class="ph ph-shield-check"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">BookLeaf Author Support Portal</h1>
            <p class="mt-2 text-slate-600 text-sm">
                Sign in to view published titles, track quarterly royalties, or raise an AI-assisted query.
            </p>

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

        <!-- STATE 2: AUTHOR ROLE (STRICTLY AUTHOR ONLY) -->
        <div x-show="currentUser && currentUser.role === 'author'" x-cloak class="space-y-6">

            <!-- Author Identity Header & Isolation Guarantee -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        <span x-text="currentUser?.name?.charAt(0)"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-lg font-bold text-slate-900" x-text="currentUser?.name"></h1>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[11px] font-mono font-bold"
                                x-text="currentUser?.author_id"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-3">
                            <span class="flex items-center gap-1"><i class="ph ph-envelope"></i> <span x-text="currentUser?.email"></span></span>
                            <span>•</span>
                            <span class="flex items-center gap-1"><i class="ph ph-map-pin"></i> <span x-text="currentAuthorProfile?.city || currentUser?.city || 'Mumbai'"></span></span>
                            <span>•</span>
                            <span>Joined: <span x-text="currentAuthorProfile?.joined_date || currentUser?.joined_date || '2023-01-15'"></span></span>
                        </p>
                    </div>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-2">
                    <i class="ph-bold ph-shield-check text-emerald-600 text-base"></i>
                    <div>
                        <div class="font-bold text-[11px]">Strict Author Isolation Active</div>
                        <div class="text-[10px] text-emerald-700">Displaying only data for <strong x-text="currentUser?.author_id"></strong></div>
                    </div>
                </div>
            </div>

            <!-- TAB 1: MY BOOKS & ROYALTIES -->
            <div x-show="authorTab === 'books'" class="space-y-6">
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

                <!-- Author's Isolated Books Table (With Publication Date) -->
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
                                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                                ISBN: <span x-text="book.isbn || 'Pending Allocation'"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-slate-600" x-text="book.genre"></td>
                                        <td class="px-4 py-3.5">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold inline-flex items-center gap-1.5"
                                                :class="book.status === 'Published & Live' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
                                                <span class="w-1.5 h-1.5 rounded-full" :class="book.status === 'Published & Live' ? 'bg-emerald-600' : 'bg-amber-600'"></span>
                                                <span x-text="book.status"></span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-slate-500 font-mono text-[11px]" x-text="book.publication_date || book.published_date || 'In Production'"></td>
                                        <td class="px-4 py-3.5 text-right font-medium" x-text="book.mrp ? '₹' + book.mrp : '—'"></td>
                                        <td class="px-4 py-3.5 text-right font-semibold text-emerald-700" x-text="book.author_royalty_per_copy ? '₹' + book.author_royalty_per_copy : '—'"></td>
                                        <td class="px-4 py-3.5 text-right font-mono" x-text="(book.total_copies_sold || 0).toLocaleString()"></td>
                                        <td class="px-4 py-3.5 text-right font-bold text-slate-900" x-text="'₹' + (book.total_royalty_earned || 0).toLocaleString()"></td>
                                        <td class="px-4 py-3.5 text-right font-bold"
                                            :class="book.royalty_pending > 0 ? 'text-amber-600' : 'text-slate-400'"
                                            x-text="'₹' + (book.royalty_pending || 0).toLocaleString()"></td>
                                        <td class="px-4 py-3.5 text-center">
                                            <div class="flex justify-center gap-1">
                                                <template x-for="ch in (book.available_on || [])" :key="ch">
                                                    <span class="text-[9px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-medium" x-text="ch"></span>
                                                </template>
                                                <span x-show="!book.available_on || book.available_on.length === 0" class="text-[10px] text-slate-400 italic">Production Prep</span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: RAISE SUPPORT QUERY (With Optional File Attachment UI) -->
            <div x-show="authorTab === 'raise_query'" class="max-w-3xl mx-auto">
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
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="block text-xs font-bold text-slate-700">Detailed Description <span class="text-rose-500">*</span></label>
                                <span class="text-[11px] text-slate-400">Be as specific as possible</span>
                            </div>
                            <textarea x-model="ticketForm.description" required rows="4"
                                placeholder="Explain your question or issue in detail."
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                        </div>

                        <!-- Optional File Attachment (Requirement A.3: UI Only) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Attach Supporting Document / Photo (Optional)</label>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 bg-slate-50 border border-dashed border-slate-300 rounded-xl">
                                <label class="px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold cursor-pointer border border-slate-200 shadow-2xs transition flex items-center justify-center gap-2 w-fit">
                                    <i class="ph-bold ph-paperclip text-slate-600"></i>
                                    <span>Choose File</span>
                                    <input type="file" class="hidden" @change="attachedFileName = $event.target.files[0]?.name || ''">
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-600 font-medium truncate" x-text="attachedFileName ? 'Selected: ' + attachedFileName : 'No file chosen (PNG, JPG, PDF up to 5MB)'"></span>
                                    <button type="button" x-show="attachedFileName" @click="attachedFileName = ''" class="text-rose-500 hover:text-rose-700 text-xs font-bold">✕ Remove</button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end items-center">
                            <button type="submit" :disabled="submittingTicket"
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2 cursor-pointer">
                                <template x-if="submittingTicket">
                                    <span class="flex items-center gap-2">
                                        <i class="ph-bold ph-spinner animate-spin"></i>
                                        <span>Submitting...</span>
                                    </span>
                                </template>
                                <template x-if="!submittingTicket">
                                    <span class="flex items-center gap-1.5">
                                        <i class="ph-bold ph-paper-plane-tilt"></i>
                                        <span>Submit Ticket</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 3: MY TICKETS (STRICTLY THIS AUTHOR'S TICKETS ONLY) -->
            <div x-show="authorTab === 'my_tickets'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Your Submitted Support Tickets</h2>
                        <p class="text-xs text-slate-500">Live feed with BookLeaf Operations Team replies</p>
                    </div>
                </div>

                <div x-show="authorTickets.length === 0" class="bg-white border border-slate-200 rounded-xl p-8 text-center shadow-sm">
                    <i class="ph ph-chat-circle-slash text-3xl text-slate-300"></i>
                    <h3 class="font-bold text-slate-800 text-sm mt-2">No Support Queries Raised</h3>
                    <p class="text-xs text-slate-500 mt-1">If you have any questions, raise a ticket using the 'Raise Support Query' tab.</p>
                </div>

                <div class="space-y-3">
                    <template x-for="ticket in authorTickets" :key="ticket.ticket_id">
                        <div class="bg-white border rounded-xl p-5 shadow-sm transition hover:border-slate-300"
                            :class="ticket.status === 'Resolved' ? 'border-emerald-200/80' : 'border-slate-200'">

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

                            <div class="py-3 text-xs text-slate-700 leading-relaxed">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Associated Book:</div>
                                <span class="font-semibold text-slate-800" x-text="ticket.book_title"></span>
                                <p class="mt-2 text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100" x-text="ticket.description"></p>

                                <!-- Attachment Badge in Author View -->
                                <template x-if="ticket.attachment">
                                    <div class="mt-2.5 flex items-center gap-1.5 text-xs text-slate-500">
                                        <i class="ph-bold ph-paperclip text-slate-400"></i>
                                        <span>Attachment:</span>
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200" x-text="ticket.attachment"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-2 pt-3 border-t border-slate-100">
                                <template x-if="ticket.admin_response">
                                    <div class="bg-emerald-50/70 border border-emerald-200 rounded-xl p-4">
                                        <div class="flex justify-between items-center mb-1.5">
                                            <div class="flex items-center gap-2">
                                                <i class="ph-bold ph-check-circle text-emerald-600"></i>
                                                <span class="text-xs font-bold text-emerald-900">Official Reply from BookLeaf Operations</span>
                                            </div>
                                            <span class="text-[10px] text-emerald-700 font-medium" x-text="'Replied: ' + (formatDate(ticket.replied_at) || 'Recently')"></span>
                                        </div>
                                        <div class="text-xs text-emerald-950 whitespace-pre-line leading-relaxed" x-text="ticket.admin_response"></div>
                                    </div>
                                </template>
                                <template x-if="!ticket.admin_response">
                                    <div class="flex items-center gap-2 text-xs text-slate-400 italic py-1">
                                        <i class="ph-bold ph-hourglass-medium text-amber-500"></i>
                                        <span>Ticket is under review by BookLeaf Operations team.</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- STATE 3: ADMIN ROLE (OPS DASHBOARD - ALL AUTHORS/TICKETS) -->
        <div x-show="currentUser && currentUser.role === 'admin'" x-cloak class="space-y-6">

            <!-- Admin Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                            BookLeaf Operations Desk
                        </span>
                        <h1 class="text-xl font-bold tracking-tight">AI-Powered Author Support & Ticket Queue</h1>
                    </div>
                    <p class="text-xs text-slate-300 mt-1">
                        Review tickets across all authors, inspect AI classifications & priority scores, and send policy-grounded replies.
                    </p>
                </div>

                <div class="flex items-center gap-2 bg-slate-800/80 px-3.5 py-2 rounded-xl border border-slate-700 text-xs">
                    <i class="ph-bold ph-sparkle text-indigo-400 text-base"></i>
                    <div>
                        <div class="text-[10px] uppercase text-slate-400 font-semibold">Active AI Engine</div>
                        <div class="font-bold text-emerald-400">Knowledge Base Fed (GPT-4o Mini)</div>
                    </div>
                </div>
            </div>

            <!-- Ops Financial & Publishing Summary Strip -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Authors</span>
                    <div class="text-2xl font-bold text-slate-900 mt-1" x-text="authors.length"></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Seeded Author Base</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Catalog Titles</span>
                    <div class="text-2xl font-bold text-slate-900 mt-1" x-text="books.length"></div>
                    <div class="text-[11px] text-slate-500 mt-0.5" x-text="books.filter(b => b.status === 'Published & Live').length + ' Live • 2 In Prod'"></div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Copies Sold</span>
                    <div class="text-2xl font-bold text-emerald-600 mt-1" x-text="globalStats.total_sales.toLocaleString()"></div>
                    <div class="text-[11px] text-emerald-700 mt-0.5">Amazon / Flipkart / Store</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Earned</span>
                    <div class="text-2xl font-bold text-slate-900 mt-1" x-text="'₹' + globalStats.total_earned.toLocaleString()"></div>
                    <div class="text-[11px] text-slate-500 mt-0.5">80% net author profit</div>
                </div>

                <div class="bg-amber-50/70 p-4 rounded-xl border border-amber-200 shadow-sm col-span-2 lg:col-span-1">
                    <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider">Pending Royalties</span>
                    <div class="text-2xl font-bold text-amber-700 mt-1" x-text="'₹' + globalStats.total_pending.toLocaleString()"></div>
                    <div class="text-[11px] text-amber-900 mt-0.5">Disbursement backlog</div>
                </div>
            </div>

            <!-- ADMIN TAB 1: TICKET QUEUE WITH AI STUDIO -->
            <div x-show="adminTab === 'tickets'" class="space-y-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Status Filter -->
                        <select x-model="ticketFilterStatus" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium">
                            <option value="">All Statuses</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                        </select>

                        <!-- Priority Filter -->
                        <select x-model="ticketFilterPriority" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium">
                            <option value="">All Priorities</option>
                            <option value="Critical">Critical</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>

                        <!-- AI Category Filter -->
                        <select x-model="ticketFilterCategory" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium">
                            <option value="">All AI Categories</option>
                            <option value="Royalty & Payments">Royalty & Payments</option>
                            <option value="ISBN & Metadata Issues">ISBN & Metadata Issues</option>
                            <option value="Printing & Quality">Printing & Quality</option>
                            <option value="Distribution & Availability">Distribution & Availability</option>
                            <option value="Book Status & Production Updates">Book Status & Production Updates</option>
                            <option value="General Inquiry">General Inquiry</option>
                        </select>

                        <!-- Date Range Filter -->
                        <select x-model="ticketFilterDate" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium">
                            <option value="">All Dates</option>
                            <option value="today">Today</option>
                            <option value="7days">Past 7 Days</option>
                            <option value="30days">Past 30 Days</option>
                        </select>
                    </div>

                    <div class="relative w-full sm:w-64">
                        <i class="ph ph-magnifying-glass absolute left-3 top-2 text-slate-400"></i>
                        <input type="text" x-model="ticketSearch" placeholder="Search ticket, author, subject, admin..."
                            class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <!-- Master Ticket Table -->
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="px-4 py-3">Urgency & Priority</th>
                                    <th class="px-4 py-3">Ticket ID & Subject</th>
                                    <th class="px-4 py-3">Author & Book</th>
                                    <th class="px-4 py-3">AI Auto-Classification</th>
                                    <th class="px-4 py-3">Status & Owner</th>
                                    <th class="px-4 py-3">Submitted</th>
                                    <th class="px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="t in filteredTickets" :key="t.ticket_id">
                                    <tr class="hover:bg-slate-50/80 transition"
                                        :class="t.priority === 'Critical' && t.status !== 'Resolved' ? 'bg-rose-50/30' : ''">

                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                                    :class="priorityColor(t.priority)" x-text="t.priority"></span>
                                                <template x-if="t.priority === 'Critical' && t.status !== 'Resolved'">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500 live-pulse" title="Needs immediate ops response"></span>
                                                </template>
                                            </div>
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
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium border border-slate-200">
                                                <i class="ph-bold ph-tag text-indigo-500"></i>
                                                <span x-text="t.category"></span>
                                            </span>
                                        </td>

                                        <td class="px-4 py-3.5">
                                            <div class="flex flex-col gap-1 items-start">
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                                    :class="statusColor(t.status)" x-text="t.status"></span>
                                                <template x-if="t.assigned_name">
                                                    <span class="inline-flex items-center gap-1 text-[10px] text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 font-mono font-medium">
                                                        <i class="ph-bold ph-user text-indigo-600"></i>
                                                        <span x-text="t.assigned_name"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3.5 text-slate-500 text-[11px]" x-text="formatDate(t.created_at)"></td>

                                        <td class="px-4 py-3.5 text-center">
                                            <button type="button" @click="openTicketWorkspace(t)"
                                                class="px-3 py-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition flex items-center gap-1 mx-auto cursor-pointer">
                                                <i class="ph-bold ph-pencil-simple-line"></i>
                                                <span>Resolve</span>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ADMIN TAB 2: MASTER DIRECTORY -->
            <div x-show="adminTab === 'directory'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Seeded Authors Catalog (10 Records)</h2>
                    <input type="text" x-model="directorySearch" placeholder="Search authors..."
                        class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs w-64">
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
                                    <td class="px-4 py-3.5 text-right font-bold"
                                        :class="getAuthorPending(author.author_id) > 0 ? 'text-amber-600' : 'text-slate-400'"
                                        x-text="'₹' + getAuthorPending(author.author_id).toLocaleString()"></td>
                                    <td class="px-4 py-3.5 text-center">
                                        <button type="button" @click="inspectAuthor(author.author_id)"
                                            class="px-2.5 py-1 text-[11px] font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded border border-indigo-200 transition cursor-pointer">
                                            Inspect Author
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- MODAL 1: ADMIN INSPECT AUTHOR PROFILE (NO ROLE SWITCHING) -->
    <div x-show="inspectedAuthor" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col border border-slate-200 overflow-hidden"
            @click.outside="inspectedAuthor = null">

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-base" x-text="inspectedAuthor?.name?.charAt(0)"></div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900" x-text="inspectedAuthor?.name"></h2>
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-mono font-bold" x-text="inspectedAuthor?.author_id"></span>
                        </div>
                        <p class="text-xs text-slate-500" x-text="inspectedAuthor?.email + ' • ' + (inspectedAuthor?.city || 'India') + ' • Joined: ' + (inspectedAuthor?.joined_date || '2023-01-15')"></p>
                    </div>
                </div>
                <button type="button" @click="inspectedAuthor = null" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">
                    <i class="ph ph-x"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-5">
                <!-- Stats Cards -->
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

                <!-- Books Table -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Associated Books</h4>
                    <div class="border rounded-xl overflow-hidden border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-xs text-left">
                            <thead class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase">
                                <tr>
                                    <th class="p-3">Title & ISBN</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3 text-right">Sold</th>
                                    <th class="p-3 text-right">Pending</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="book in books.filter(b => b.author_id === inspectedAuthor?.author_id)" :key="book.book_id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-3">
                                            <div class="font-bold text-slate-900" x-text="book.title"></div>
                                            <div class="text-[10px] font-mono text-slate-400" x-text="'ISBN: ' + (book.isbn || 'Pending')"></div>
                                        </td>
                                        <td class="p-3">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold"
                                                :class="book.status === 'Published & Live' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                                x-text="book.status"></span>
                                        </td>
                                        <td class="p-3 text-right font-mono" x-text="(book.total_copies_sold || 0).toLocaleString()"></td>
                                        <td class="p-3 text-right font-bold text-amber-600" x-text="'₹' + (book.royalty_pending || 0).toLocaleString()"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
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

    <!-- MODAL 2: ADMIN AI TICKET WORKSPACE & RESPONSE STUDIO (PROTECTED WITH x-if) -->
    <div x-show="activeWorkspaceTicket && activeWorkspaceTicket.ticket_id" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">

        <template x-if="activeWorkspaceTicket && activeWorkspaceTicket.ticket_id">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[92vh] flex flex-col border border-slate-200 overflow-hidden"
                @click.outside="closeWorkspace()">

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

                <div class="p-6 overflow-y-auto grid grid-cols-1 md:grid-cols-12 gap-6 flex-1">

                    <!-- Left: Query Details & Overrides (5 cols) -->
                    <div class="md:col-span-5 space-y-4 border-r border-slate-100 pr-0 md:pr-4">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Author Query & Context</div>
                            <div class="text-xs font-semibold text-slate-900" x-text="activeWorkspaceTicket.book_title"></div>
                            <p class="text-xs text-slate-700 mt-2 whitespace-pre-line leading-relaxed bg-white p-3 rounded-lg border border-slate-200/80"
                                x-text="activeWorkspaceTicket.description"></p>

                            <!-- Attachment Badge in Admin Workspace -->
                            <div class="mt-3 pt-2.5 border-t border-slate-200 flex items-center justify-between text-xs">
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Attachment:</span>
                                <template x-if="activeWorkspaceTicket.attachment">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 font-medium text-xs">
                                        <i class="ph-bold ph-paperclip text-indigo-600"></i>
                                        <span x-text="activeWorkspaceTicket.attachment"></span>
                                    </span>
                                </template>
                                <template x-if="!activeWorkspaceTicket.attachment">
                                    <span class="text-slate-400 italic text-xs">None</span>
                                </template>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-bold text-slate-700">Category (AI Detected)</label>
                                <span class="text-[10px] text-indigo-600 font-semibold">Overrideable</span>
                            </div>
                            <select x-model="activeWorkspaceTicket.category"
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                <option value="Royalty & Payments">Royalty & Payments</option>
                                <option value="ISBN & Metadata Issues">ISBN & Metadata Issues</option>
                                <option value="Printing & Quality">Printing & Quality</option>
                                <option value="Distribution & Availability">Distribution & Availability</option>
                                <option value="Book Status & Production Updates">Book Status & Production Updates</option>
                                <option value="General Inquiry">General Inquiry</option>
                            </select>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-bold text-slate-700">Priority Score (AI Assessed)</label>
                                <span class="text-[10px] text-indigo-600 font-semibold">Overrideable</span>
                            </div>
                            <select x-model="activeWorkspaceTicket.priority"
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                <option value="Critical">Critical</option>
                                <option value="High">High</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Ticket Status</label>
                            <select x-model="activeWorkspaceTicket.status"
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Private Internal Notes (Ops Only)</label>
                            <textarea x-model="activeWorkspaceTicket.internal_notes" rows="3"
                                placeholder="Add notes for operations team (invisible to author)..."
                                class="w-full px-3 py-2 bg-yellow-50/50 border border-yellow-200 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Right: AI-Drafted Studio (7 cols) -->
                    <div class="md:col-span-7 space-y-4 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center gap-1.5">
                                    <i class="ph-bold ph-magic-wand text-indigo-600"></i>
                                    <span class="text-xs font-bold text-slate-900">AI-Drafted Response (Policy Grounded)</span>
                                </div>
                                <button type="button" @click="regenerateAIDraft()" :disabled="regeneratingDraft"
                                    class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 cursor-pointer disabled:opacity-50">
                                    <i class="ph-bold" :class="regeneratingDraft ? 'ph-spinner animate-spin' : 'ph-arrows-clockwise'"></i>
                                    <span x-text="regeneratingDraft ? 'Regenerating...' : 'Regenerate Draft'"></span>
                                </button>
                            </div>

                            <div class="bg-indigo-50/40 p-3 rounded-lg border border-indigo-100 text-[11px] text-indigo-900 mb-3 flex items-start gap-2">
                                <i class="ph-bold ph-shield-check text-indigo-600 text-sm mt-0.5 shrink-0"></i>
                                <span>Calibrated against BookLeaf policies (80/20 royalty split, 45-day payment cycle, ₹1,000 threshold, or verified reprint timelines). Edit as desired before dispatch.</span>
                            </div>

                            <label class="block text-xs font-bold text-slate-700 mb-1">Response to Author</label>
                            <textarea x-model="activeWorkspaceTicket.ai_draft_response" rows="12"
                                class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-sans focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none leading-relaxed"></textarea>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex justify-end gap-2.5">
                            <button type="button" @click="closeWorkspace()"
                                class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="button" @click="sendResponseToAuthor()"
                                class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition flex items-center gap-2 cursor-pointer">
                                <i class="ph-bold ph-paper-plane-tilt"></i>
                                <span>Send Official Response to Author</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- MODAL 3: MANUAL LOGIN MODAL (FALLBACK) -->
    <div x-show="showLoginModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200"
            @click.outside="showLoginModal = false">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Sign in to your account</h3>
                <button type="button" @click="showLoginModal = false" class="text-slate-400 hover:text-slate-600 font-bold cursor-pointer">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            <form @submit.prevent="loginManual()" class="mt-4 space-y-4">
                <div x-show="loginError" class="p-2.5 rounded-lg bg-rose-50 text-rose-700 text-xs" x-text="loginError"></div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email address</label>
                    <input type="email" x-model="loginEmail" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" x-model="loginPassword" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                </div>
                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition cursor-pointer">
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-4 text-center text-xs text-slate-400">
        <p>BookLeaf Publishing Platform • 80/20 Net Royalty Split • 45-Day Payment Cycle • Delhi HQ</p>
    </footer>

    <!-- DYNAMIC DATA ENGINE -->
    <script>
        window.bookleafApp = function() {
            return {
                currentUser: null,
                currentAuthorProfile: null,
                authorTab: 'books',
                adminTab: 'tickets',
                showLoginModal: false,
                submittingTicket: false,
                regeneratingDraft: false,
                activeWorkspaceTicket: null,
                inspectedAuthor: null,
                assigningTicket: false,
                attachedFileName: '',
                loginEmail: '',
                loginPassword: '',
                loginError: '',

                // Filters
                ticketFilterStatus: '',
                ticketFilterPriority: '',
                ticketFilterCategory: '',
                ticketFilterDate: '',
                ticketSearch: '',
                directorySearch: '',

                ticketForm: {
                    book_id: 'general',
                    subject: '',
                    description: '',
                },

                // Dynamic data collections
                authors: [],
                books: [],
                tickets: [],
                authorBooks: [],
                authorTickets: [],
                authorSummary: {
                    total_copies_sold: 0,
                    total_royalty_earned: 0,
                    royalty_paid: 0,
                    royalty_pending: 0
                },
                globalStats: {
                    total_sales: 0,
                    total_earned: 0,
                    total_paid: 0,
                    total_pending: 0
                },

                pollTimer: null,

                async init() {
                    await this.checkAuthSession();

                    // Background polling: Modal OPEN hone par overwrite pause karein
                    this.pollTimer = setInterval(() => {
                        if (this.currentUser && !this.activeWorkspaceTicket) {
                            this.loadContextData(false);
                        }
                    }, 6000);
                },

                getCsrfToken() {
                    const tag = document.querySelector('meta[name="csrf-token"]');
                    return tag ? tag.getAttribute('content') : '';
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return isNaN(d) ? dateStr : d.toLocaleDateString('en-IN', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                async checkAuthSession() {
                    const savedRole = sessionStorage.getItem('bookleaf_active_role');
                    if (savedRole === 'guest') {
                        this.currentUser = null;
                        return;
                    }

                    try {
                        const res = await fetch('/api/auth/me', {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.user) {
                                this.currentUser = data.user;
                                await this.loadContextData(true);
                                return;
                            }
                        }
                    } catch (e) {
                        console.warn('Session check failed:', e);
                    }

                    this.currentUser = null;
                },

                async loadContextData(showLoading = true) {
                    if (!this.currentUser) return;

                    // AUTHOR FLOW: Strictly scoped data
                    if (this.currentUser.role === 'author') {
                        try {
                            const [booksRes, ticketsRes] = await Promise.all([
                                fetch('/api/author/my-books', { headers: { 'Accept': 'application/json' } }),
                                fetch('/api/author/tickets', { headers: { 'Accept': 'application/json' } })
                            ]);

                            if (booksRes.ok) {
                                const bData = await booksRes.json();
                                this.authorBooks = bData.books || [];
                                this.authorSummary = bData.summary || this.authorSummary;
                                this.currentAuthorProfile = bData.author || null;
                            }

                            if (ticketsRes.ok) {
                                this.authorTickets = await ticketsRes.json();
                            }
                        } catch (err) {
                            console.error('Error fetching author data:', err);
                        }
                    } 
                    // ADMIN FLOW: Global operations catalog & queue
                    else if (this.currentUser.role === 'admin') {
                        try {
                            const [authorsRes, booksRes, ticketsRes] = await Promise.all([
                                fetch('/api/admin/authors', { headers: { 'Accept': 'application/json' } }),
                                fetch('/api/admin/books', { headers: { 'Accept': 'application/json' } }),
                                fetch('/api/admin/tickets', { headers: { 'Accept': 'application/json' } })
                            ]);

                            if (authorsRes.ok) this.authors = await authorsRes.json();
                            if (booksRes.ok) {
                                this.books = await booksRes.json();
                                this.calculateGlobalStats();
                            }
                            if (ticketsRes.ok) this.tickets = await ticketsRes.json();
                        } catch (err) {
                            console.error('Error fetching admin data:', err);
                        }
                    }
                },

                calculateGlobalStats() {
                    this.globalStats = {
                        total_sales: this.books.reduce((s, b) => s + (Number(b.total_copies_sold) || 0), 0),
                        total_earned: this.books.reduce((s, b) => s + (Number(b.total_royalty_earned) || 0), 0),
                        total_paid: this.books.reduce((s, b) => s + (Number(b.royalty_paid) || 0), 0),
                        total_pending: this.books.reduce((s, b) => s + (Number(b.royalty_pending) || 0), 0),
                    };
                },

                async switchRole(roleIdentifier) {
                    let email = 'admin@bookleafpub.com';
                    let password = 'admin123';

                    if (roleIdentifier === 'AUTH001') {
                        email = 'priya.sharma@email.com';
                        password = 'password123';
                    } else if (roleIdentifier === 'AUTH002') {
                        email = 'rohit.kapoor@email.com';
                        password = 'password123';
                    } else if (roleIdentifier === 'AUTH005') {
                        email = 'sunita.deshmukh@email.com';
                        password = 'password123';
                    } else if (roleIdentifier !== 'admin') {
                        const found = this.authors.find(a => a.author_id === roleIdentifier);
                        if (found) email = found.email;
                    }

                    await this.performLogin(email, password, roleIdentifier);
                },

                async performLogin(email, password, roleIdentifier = null) {
                    this.loginError = '';
                    try {
                        const res = await fetch('/api/auth/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({ email, password })
                        });

                        const data = await res.json();
                        if (res.ok && data.user) {
                            this.currentUser = data.user;
                            sessionStorage.setItem('bookleaf_active_role', roleIdentifier || data.user.role);
                            this.showLoginModal = false;

                            if (this.currentUser.role === 'admin') {
                                this.adminTab = 'tickets';
                            } else {
                                this.authorTab = 'books';
                                this.ticketForm.book_id = 'general';
                            }

                            await this.loadContextData(true);
                        } else {
                            this.loginError = data.message || 'Invalid login credentials.';
                        }
                    } catch (e) {
                        this.loginError = 'Server connection error during login.';
                    }
                },

                async logout() {
                    try {
                        await fetch('/api/auth/logout', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            }
                        });
                    } catch (e) {}

                    this.currentUser = null;
                    this.currentAuthorProfile = null;
                    this.inspectedAuthor = null;
                    this.activeWorkspaceTicket = null;
                    this.authorBooks = [];
                    this.authorTickets = [];
                    this.authors = [];
                    this.books = [];
                    this.tickets = [];
                    sessionStorage.setItem('bookleaf_active_role', 'guest');
                },

                loginManual() {
                    this.performLogin(this.loginEmail, this.loginPassword);
                },

                // Submit Author Ticket with Attachment payload
                async submitAuthorTicket() {
                    this.submittingTicket = true;

                    try {
                        const res = await fetch('/api/author/tickets', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({
                                book_id: this.ticketForm.book_id,
                                subject: this.ticketForm.subject,
                                description: this.ticketForm.description,
                                attachment: this.attachedFileName || null
                            })
                        });

                        const data = await res.json();
                        if (res.ok && data.ticket) {
                            this.authorTickets.unshift(data.ticket);
                            this.ticketForm.subject = '';
                            this.ticketForm.description = '';
                            this.attachedFileName = '';
                            this.authorTab = 'my_tickets';
                        } else {
                            alert(data.message || 'Error submitting query');
                        }
                    } catch (e) {
                        alert('Network connection error while submitting ticket.');
                    } finally {
                        this.submittingTicket = false;
                    }
                },

                async sendResponseToAuthor() {
                    if (!this.activeWorkspaceTicket) return;

                    const ticketId = this.activeWorkspaceTicket.ticket_id;
                    try {
                        const res = await fetch(`/api/admin/tickets/${ticketId}/reply`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({
                                response: this.activeWorkspaceTicket.ai_draft_response,
                                status: this.activeWorkspaceTicket.status === 'Open' ? 'Resolved' : this.activeWorkspaceTicket.status,
                                internal_notes: this.activeWorkspaceTicket.internal_notes,
                                category: this.activeWorkspaceTicket.category,
                                priority: this.activeWorkspaceTicket.priority
                            })
                        });

                        const data = await res.json();
                        if (res.ok && data.ticket) {
                            const idx = this.tickets.findIndex(t => t.ticket_id === ticketId);
                            if (idx !== -1) {
                                this.tickets[idx] = data.ticket;
                            }
                            this.closeWorkspace();
                        } else {
                            alert(data.message || 'Failed to dispatch official response.');
                        }
                    } catch (e) {
                        alert('Network error while dispatching response.');
                    }
                },

                // Self-Assignment Handler
                async assignTicketToSelf() {
                    if (!this.activeWorkspaceTicket || !this.activeWorkspaceTicket.ticket_id) {
                        alert("No active ticket found!");
                        return;
                    }

                    if (this.activeWorkspaceTicket.assigned_name) {
                        alert("Ticket is already claimed by: " + this.activeWorkspaceTicket.assigned_name);
                        return;
                    }

                    this.assigningTicket = true;
                    const ticketId = this.activeWorkspaceTicket.ticket_id;

                    try {
                        const res = await fetch(`/api/admin/tickets/${ticketId}/assign`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            }
                        });

                        const data = await res.json();

                        if (res.ok && data.ticket) {
                            this.activeWorkspaceTicket.assigned_to = data.ticket.assigned_to;
                            this.activeWorkspaceTicket.assigned_name = data.ticket.assigned_name;

                            const idx = this.tickets.findIndex(t => t.ticket_id === ticketId);
                            if (idx !== -1) {
                                this.tickets[idx] = data.ticket;
                            }
                        } else {
                            alert("Assignment Error: " + (data.message || 'Action rejected by server'));
                        }
                    } catch (e) {
                        console.error('Assign failed:', e);
                        alert("Network Error: " + e.message);
                    } finally {
                        this.assigningTicket = false;
                    }
                },

                inspectAuthor(authorId) {
                    const found = this.authors.find(a => a.author_id === authorId);
                    if (found) {
                        this.inspectedAuthor = found;
                    }
                },

                openTicketWorkspace(ticket) {
                    this.assigningTicket = false;
                    this.activeWorkspaceTicket = JSON.parse(JSON.stringify(ticket));
                },

                closeWorkspace() {
                    this.activeWorkspaceTicket = null;
                },

                // Dynamic Regenerate Draft (Backend Hooked with Policy-Grounded Fallback)
                async regenerateAIDraft() {
                    if (!this.activeWorkspaceTicket) return;
                    this.regeneratingDraft = true;

                    const ticketId = this.activeWorkspaceTicket.ticket_id;
                    const category = this.activeWorkspaceTicket.category || 'General Inquiry';
                    const author = this.activeWorkspaceTicket.author_name || 'Author';
                    const book = this.activeWorkspaceTicket.book_title || 'your book';

                    try {
                        // 1. Live Backend Request
                        const res = await fetch(`/api/admin/tickets/${ticketId}/regenerate-draft`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({ category })
                        });

                        if (res.ok) {
                            const data = await res.json();
                            if (data.ai_draft_response) {
                                this.activeWorkspaceTicket.ai_draft_response = data.ai_draft_response;
                                return;
                            }
                        }
                    } catch (e) {
                        console.warn('Backend draft regeneration endpoint unavailable, using policy-calibrated fallback:', e);
                    } finally {
                        this.regeneratingDraft = false;
                    }

                    // 2. Intelligent Policy-Grounded Fallback (BookLeaf Policies)
                    let customizedBody = '';
                    if (category === 'Royalty & Payments') {
                        customizedBody = `We understand your concern regarding royalty payouts for '${book}'. BookLeaf follows an 80/20 net profit split calculated quarterly and processed within 45 days of the quarter ending, provided accumulated earnings meet our ₹1,000 disbursement threshold. Our accounts team has initiated a detailed payout verification and will update your dashboard within 48 hours.`;
                    } else if (category === 'Printing & Quality') {
                        customizedBody = `We sincerely apologize for any quality defects experienced with your author copies of '${book}'. BookLeaf guarantees free reprints for verified defects (binding, color, or alignment). Please share high-resolution photos of the defective copies, and our production desk will dispatch replacements within 5-7 business days.`;
                    } else if (category === 'Distribution & Availability') {
                        customizedBody = `Thank you for bringing the availability of '${book}' to our notice. Platform stock sync delays across Amazon/Flipkart can take 24-48 business hours to reflect live status. We have triggered an expedited re-sync with our distribution team.`;
                    } else if (category === 'ISBN & Metadata Issues') {
                        customizedBody = `We take catalog accuracy very seriously. We have escalated the ISBN record for '${book}' directly to our production head for immediate correction on retail databases within 48 hours.`;
                    } else {
                        customizedBody = `Thank you for reaching out to BookLeaf Publishing regarding '${book}'. We have logged your query with our operations desk and are reviewing your records per standard publisher policy. You will receive an operational status update within 24 business hours.`;
                    }

                    await new Promise(r => setTimeout(r, 500));

                    this.activeWorkspaceTicket.ai_draft_response = 
                        `Dear ${author},\n\nThank you for reaching out to BookLeaf Publishing.\n\n${customizedBody}\n\nPlease feel free to reply if you need any additional clarification.\n\nWarm regards,\nBookLeaf Operations Team`;

                    this.regeneratingDraft = false;
                },

                // Filtered & Smart-Sorted Tickets Queue
                get filteredTickets() {
                    const now = new Date();

                    return this.tickets.filter(t => {
                        const matchStatus = !this.ticketFilterStatus || t.status === this.ticketFilterStatus;
                        const matchPriority = !this.ticketFilterPriority || t.priority === this.ticketFilterPriority;
                        const matchCat = !this.ticketFilterCategory || t.category === this.ticketFilterCategory;

                        // Date Filter Logic
                        let matchDate = true;
                        if (this.ticketFilterDate && t.created_at) {
                            const tDate = new Date(t.created_at);
                            const diffDays = (now - tDate) / (1000 * 60 * 60 * 24);
                            if (this.ticketFilterDate === 'today') matchDate = diffDays <= 1;
                            else if (this.ticketFilterDate === '7days') matchDate = diffDays <= 7;
                            else if (this.ticketFilterDate === '30days') matchDate = diffDays <= 30;
                        }

                        const q = this.ticketSearch.toLowerCase().trim();
                        const matchQ = !q || (t.ticket_id && t.ticket_id.toLowerCase().includes(q)) ||
                            (t.subject && t.subject.toLowerCase().includes(q)) ||
                            (t.author_name && t.author_name.toLowerCase().includes(q)) ||
                            (t.assigned_name && t.assigned_name.toLowerCase().includes(q));

                        return matchStatus && matchPriority && matchCat && matchDate && matchQ;
                    }).sort((a, b) => {
                        // 1. Unresolved tickets come before Resolved/Closed
                        const aOpen = (a.status !== 'Resolved' && a.status !== 'Closed') ? 1 : 0;
                        const bOpen = (b.status !== 'Resolved' && b.status !== 'Closed') ? 1 : 0;
                        if (bOpen !== aOpen) return bOpen - aOpen;

                        // 2. Urgent Priority First (Critical > High > Medium > Low)
                        const priorityWeights = {
                            'Critical': 4,
                            'High': 3,
                            'Medium': 2,
                            'Low': 1
                        };
                        const diffWeight = (priorityWeights[b.priority] || 0) - (priorityWeights[a.priority] || 0);
                        if (diffWeight !== 0) return diffWeight;

                        // 3. Oldest Unresolved First
                        return new Date(a.created_at) - new Date(b.created_at);
                    });
                },

                get filteredDirectoryAuthors() {
                    if (!this.directorySearch.trim()) return this.authors;
                    const q = this.directorySearch.toLowerCase();
                    return this.authors.filter(a =>
                        (a.name && a.name.toLowerCase().includes(q)) ||
                        (a.city && a.city.toLowerCase().includes(q)) ||
                        (a.author_id && a.author_id.toLowerCase().includes(q))
                    );
                },

                getAuthorSales(authorId) {
                    return this.books.filter(b => b.author_id === authorId).reduce((s, b) => s + (Number(b.total_copies_sold) || 0), 0);
                },
                getAuthorEarned(authorId) {
                    return this.books.filter(b => b.author_id === authorId).reduce((s, b) => s + (Number(b.total_royalty_earned) || 0), 0);
                },
                getAuthorPending(authorId) {
                    return this.books.filter(b => b.author_id === authorId).reduce((s, b) => s + (Number(b.royalty_pending) || 0), 0);
                },
                getAuthorBooksCount(authorId) {
                    return this.books.filter(b => b.author_id === authorId).length;
                },

                priorityColor(priority) {
                    switch (priority) {
                        case 'Critical':
                            return 'bg-rose-100 text-rose-800 border border-rose-200';
                        case 'High':
                            return 'bg-amber-100 text-amber-800 border border-amber-200';
                        case 'Medium':
                            return 'bg-blue-100 text-blue-800 border border-blue-200';
                        case 'Low':
                            return 'bg-slate-100 text-slate-700 border border-slate-200';
                        default:
                            return 'bg-slate-100 text-slate-700';
                    }
                },

                statusColor(status) {
                    switch (status) {
                        case 'Open':
                            return 'bg-rose-100 text-rose-800';
                        case 'In Progress':
                            return 'bg-yellow-100 text-yellow-800';
                        case 'Resolved':
                            return 'bg-emerald-100 text-emerald-800';
                        case 'Closed':
                            return 'bg-slate-100 text-slate-600';
                        default:
                            return 'bg-slate-100 text-slate-700';
                    }
                }
            };
        };
    </script>

    <!-- LOAD ALPINE AFTER SCRIPT IS DEFINED -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>