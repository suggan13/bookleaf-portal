<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'BookLeaf Publishing — Support Portal' }}</title>

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
                            900: '#14532d'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .live-pulse { animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse-subtle { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans min-h-screen flex flex-col"
    x-data="bookleafApp()"
    x-init="init()">

    <!-- Header & Navigation Shared Partials -->
    @include('components.shared.announcement')
    @include('components.shared.header')

    <!-- Main View Content Area -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        @yield('content')
    </main>

    <!-- Global Application Modals -->
    @include('components.modals.workspace')
    @include('components.modals.inspect-author')
    @include('components.modals.login')

    <!-- Footer -->
    @include('components.shared.footer')

    <!-- 1. Inline Alpine State Engine (Guarantees zero 404/ReferenceError in production) -->
    <script>
        window.bookleafApp = function () {
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

                ticketReplies: {},
                submittingReply: {},

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

                authors: [],
                books: [],
                tickets: [],
                authorBooks: [],
                authorTickets: [],
                authorSummary: { total_copies_sold: 0, total_royalty_earned: 0, royalty_paid: 0, royalty_pending: 0 },
                globalStats: { total_sales: 0, total_earned: 0, total_paid: 0, total_pending: 0 },
                pollTimer: null,

                async init() {
                    await this.checkAuthSession();
                    this.pollTimer = setInterval(() => {
                        const isTyping = Object.values(this.ticketReplies).some(v => v && v.trim().length > 0);
                        if (this.currentUser && !this.activeWorkspaceTicket && !this.submittingTicket && !isTyping) {
                            this.loadContextData(false);
                        }
                    }, 7000);
                },

                getCsrfToken() {
                    const tag = document.querySelector('meta[name="csrf-token"]');
                    return tag ? tag.getAttribute('content') : '';
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return isNaN(d) ? dateStr : d.toLocaleDateString('en-IN', {
                        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                },

                async checkAuthSession() {
                    try {
                        const res = await fetch('/api/auth/me', {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin'
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.user) {
                                this.currentUser = data.user;
                                await this.loadContextData(true);
                            }
                        }
                    } catch (e) {
                        console.warn('Session check failed:', e);
                    }
                },

                async loadContextData(showLoading = true) {
                    if (!this.currentUser) return;
                    const headers = {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.getCsrfToken()
                    };

                    if (this.currentUser.role === 'author') {
                        const [booksRes, ticketsRes] = await Promise.all([
                            fetch('/api/author/my-books', { credentials: 'same-origin', headers }),
                            fetch('/api/author/tickets', { credentials: 'same-origin', headers })
                        ]);
                        if (booksRes.ok) {
                            const bData = await booksRes.json();
                            this.authorBooks = bData.books || [];
                            this.authorSummary = bData.summary || this.authorSummary;
                            this.currentAuthorProfile = bData.author || null;
                        }
                        if (ticketsRes.ok) this.authorTickets = await ticketsRes.json();
                    } else if (this.currentUser.role === 'admin') {
                        const [authorsRes, booksRes, ticketsRes] = await Promise.all([
                            fetch('/api/admin/authors', { credentials: 'same-origin', headers }),
                            fetch('/api/admin/books', { credentials: 'same-origin', headers }),
                            fetch('/api/admin/tickets', { credentials: 'same-origin', headers })
                        ]);
                        if (authorsRes.ok) this.authors = await authorsRes.json();
                        if (booksRes.ok) {
                            this.books = await booksRes.json();
                            this.globalStats = {
                                total_sales: this.books.reduce((s, b) => s + (Number(b.total_copies_sold) || 0), 0),
                                total_earned: this.books.reduce((s, b) => s + (Number(b.total_royalty_earned) || 0), 0),
                                total_paid: this.books.reduce((s, b) => s + (Number(b.royalty_paid) || 0), 0),
                                total_pending: this.books.reduce((s, b) => s + (Number(b.royalty_pending) || 0), 0)
                            };
                        }
                        if (ticketsRes.ok) this.tickets = await ticketsRes.json();
                    }
                },

                async switchRole(roleIdentifier) {
                    let email = 'admin@bookleafpub.com';
                    let password = 'admin123';
                    if (roleIdentifier === 'AUTH001') { email = 'priya.sharma@email.com'; password = 'password123'; }
                    else if (roleIdentifier === 'AUTH002') { email = 'rohit.kapoor@email.com'; password = 'password123'; }
                    else if (roleIdentifier === 'AUTH005') { email = 'sunita.deshmukh@email.com'; password = 'password123'; }
                    await this.performLogin(email, password, roleIdentifier);
                },

                async performLogin(email, password, roleIdentifier = null) {
                    this.loginError = '';
                    try {
                        const res = await fetch('/api/auth/login', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({ email, password })
                        });
                        const data = await res.json();
                        if (res.ok && data.user) {
                            this.currentUser = data.user;
                            this.showLoginModal = false;
                            await this.loadContextData(true);
                        } else {
                            this.loginError = data.message || 'Invalid login credentials.';
                        }
                    } catch (e) {
                        this.loginError = 'Server connection error.';
                    }
                },

                async logout() {
                    try {
                        await fetch('/api/auth/logout', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { 'X-CSRF-TOKEN': this.getCsrfToken(), 'Accept': 'application/json' }
                        });
                    } catch (e) {}
                    this.currentUser = null;
                    this.authorBooks = [];
                    this.authorTickets = [];
                    this.tickets = [];
                },

                loginManual() {
                    this.performLogin(this.loginEmail, this.loginPassword);
                },

                async submitAuthorTicket() {
                    this.submittingTicket = true;
                    try {
                        const res = await fetch('/api/author/tickets', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
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
                        }
                    } finally {
                        this.submittingTicket = false;
                    }
                },

                async submitAuthorReply(ticketId) {
                    const message = this.ticketReplies[ticketId]?.trim();
                    if (!message) return;
                    this.submittingReply[ticketId] = true;
                    try {
                        const res = await fetch(`/api/author/tickets/${ticketId}/reply`, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({ message })
                        });
                        const data = await res.json();
                        if (res.ok && data.ticket) {
                            const idx = this.authorTickets.findIndex(t => t.ticket_id === ticketId);
                            if (idx !== -1) this.authorTickets[idx] = data.ticket;
                            this.ticketReplies[ticketId] = '';
                        }
                    } finally {
                        this.submittingReply[ticketId] = false;
                    }
                },

                async closeTicketAsAuthor(ticketId) {
                    if (!confirm('Close this ticket permanently?')) return;
                    const res = await fetch(`/api/author/tickets/${ticketId}/close`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() }
                    });
                    const data = await res.json();
                    if (res.ok && data.ticket) {
                        const idx = this.authorTickets.findIndex(t => t.ticket_id === ticketId);
                        if (idx !== -1) this.authorTickets[idx] = data.ticket;
                    }
                },

                openTicketWorkspace(ticket) {
                    this.assigningTicket = false;
                    this.activeWorkspaceTicket = JSON.parse(JSON.stringify(ticket));
                    const msgs = this.activeWorkspaceTicket.messages || [];
                    if (msgs.length > 0 && msgs[msgs.length - 1].sender === 'author') {
                        this.$nextTick(() => { this.regenerateAIDraft(); });
                    }
                },

                closeWorkspace() {
                    this.activeWorkspaceTicket = null;
                },

                async regenerateAIDraft() {
                    if (!this.activeWorkspaceTicket) return;
                    this.regeneratingDraft = true;

                    const ticketId = this.activeWorkspaceTicket.ticket_id;
                    const category = this.activeWorkspaceTicket.category || 'General Inquiry';
                    const author = this.activeWorkspaceTicket.author_name || 'Author';
                    const book = this.activeWorkspaceTicket.book_title || 'your book';

                    let recentAuthorMessage = this.activeWorkspaceTicket.description || '';
                    const msgs = this.activeWorkspaceTicket.messages || [];
                    for (let i = msgs.length - 1; i >= 0; i--) {
                        if (msgs[i].sender === 'author' && msgs[i].message) {
                            recentAuthorMessage = msgs[i].message.trim();
                            break;
                        }
                    }

                    try {
                        const res = await fetch(`/api/admin/tickets/${ticketId}/regenerate-draft`, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({ category, recent_author_message: recentAuthorMessage })
                        });

                        if (res.ok) {
                            const data = await res.json();
                            if (data.ai_draft_response) {
                                this.activeWorkspaceTicket.ai_draft_response = data.ai_draft_response;
                                this.regeneratingDraft = false;
                                return;
                            }
                        }
                    } catch (e) {}

                    // Contextual policy-calibrated fallback engine
                    const lowerMsg = recentAuthorMessage.toLowerCase();
                    const isDissatisfied = lowerMsg.includes('not satisfied') || lowerMsg.includes('not setisfied') || lowerMsg.includes('still');
                    let tailoredBody = isDissatisfied
                        ? `We understand that our previous response did not address your concerns and that you are not satisfied regarding '${book}'. In response to your message ("${recentAuthorMessage}"), we have requested our Senior Accounts Specialist to pull your ledger statement. Under BookLeaf's Royalty Policy, royalties follow an 80/20 net profit split and are disbursed within 45 days of the quarter ending for balances reaching the ₹1,000 threshold. We have placed an expedited hold on your query to furnish your exact payment status and itemized sales log within 24 business hours.`
                        : `Thank you for following up regarding '${book}'. In response to your message ("${recentAuthorMessage}"), our operations team is actively reviewing your request under publisher policy.`;

                    this.activeWorkspaceTicket.ai_draft_response = `Dear ${author},\n\nThank you for reaching out to BookLeaf Publishing.\n\n${tailoredBody}\n\nWarm regards,\nBookLeaf Operations Team`;
                    this.regeneratingDraft = false;
                },

                async sendResponseToAuthor() {
                    if (!this.activeWorkspaceTicket) return;
                    const ticketId = this.activeWorkspaceTicket.ticket_id;
                    const res = await fetch(`/api/admin/tickets/${ticketId}/reply`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
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
                        if (idx !== -1) this.tickets[idx] = data.ticket;
                        this.closeWorkspace();
                    }
                },

                async assignTicketToSelf() {
                    const ticketId = this.activeWorkspaceTicket.ticket_id;
                    this.assigningTicket = true;
                    try {
                        const res = await fetch(`/api/admin/tickets/${ticketId}/assign`, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { 'X-CSRF-TOKEN': this.getCsrfToken(), 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (res.ok && data.ticket) {
                            this.activeWorkspaceTicket.assigned_name = data.ticket.assigned_name;
                            this.activeWorkspaceTicket.status = data.ticket.status;
                        }
                    } finally {
                        this.assigningTicket = false;
                    }
                },

                inspectAuthor(authorId) {
                    this.inspectedAuthor = this.authors.find(a => a.author_id === authorId) || null;
                },

                get filteredTickets() {
                    return this.tickets.filter(t => {
                        const q = this.ticketSearch.toLowerCase().trim();
                        const matchQ = !q || (t.ticket_id && t.ticket_id.toLowerCase().includes(q)) || (t.subject && t.subject.toLowerCase().includes(q));
                        const matchStatus = !this.ticketFilterStatus || t.status === this.ticketFilterStatus;
                        const matchPriority = !this.ticketFilterPriority || t.priority === this.ticketFilterPriority;
                        const matchCat = !this.ticketFilterCategory || t.category === this.ticketFilterCategory;
                        return matchStatus && matchPriority && matchCat && matchQ;
                    });
                },

                get filteredDirectoryAuthors() {
                    if (!this.directorySearch.trim()) return this.authors;
                    const q = this.directorySearch.toLowerCase();
                    return this.authors.filter(a => a.name.toLowerCase().includes(q) || a.author_id.toLowerCase().includes(q));
                },

                getAuthorSales(id) { return this.books.filter(b => b.author_id === id).reduce((s, b) => s + (Number(b.total_copies_sold) || 0), 0); },
                getAuthorEarned(id) { return this.books.filter(b => b.author_id === id).reduce((s, b) => s + (Number(b.total_royalty_earned) || 0), 0); },
                getAuthorPending(id) { return this.books.filter(b => b.author_id === id).reduce((s, b) => s + (Number(b.royalty_pending) || 0), 0); },
                getAuthorBooksCount(id) { return this.books.filter(b => b.author_id === id).length; },

                priorityColor(p) {
                    return { Critical: 'bg-rose-100 text-rose-800', High: 'bg-amber-100 text-amber-800', Medium: 'bg-blue-100 text-blue-800', Low: 'bg-slate-100 text-slate-700' }[p] || 'bg-slate-100 text-slate-700';
                },
                statusColor(s) {
                    return { Open: 'bg-rose-100 text-rose-800', 'In Progress': 'bg-yellow-100 text-yellow-800', Resolved: 'bg-emerald-100 text-emerald-800', Closed: 'bg-slate-100 text-slate-600' }[s] || 'bg-slate-100 text-slate-700';
                }
            };
        };
    </script>

    <!-- 2. Alpine.js Engine Loaded with defer -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>