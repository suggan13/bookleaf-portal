# 📄 BookLeaf Engineering Assignment — Architectural Write-Up

## 1. Executive Summary & Core Objective
The objective of this assignment was to engineer an operational bridge between BookLeaf Publishing's 22,000+ title catalogue and thousands of authors experiencing support delays during high-volume processing periods. The system establishes strict dual-role isolation, real-time ticket state progression, and knowledge-base grounded AI response generation.

---

## 2. What Was Prioritised
1. **Security & Author Scoping (Zero-Trust Multi-Tenancy)**:
   - Evaluated that financial figures (royalties, net profit margins) and contract status must remain confidential to the authenticated author.
   - Enforced route-level role-based access control (`RoleMiddleware`) ensuring authors can strictly query their own `author_id`.
2. **AI Reliability & Graceful Degradation (Knowledge-Base Grounding)**:
   - Prioritised deterministic prompt engineering grounded strictly in BookLeaf's real operational constraints: 80/20 net profit split, quarterly accounting, 45-day post-quarter disbursement, ₹1,000 minimum threshold, and 5–7 day turnaround for verified defective prints.
   - Architected a dual-engine fallback: if OpenAI API keys are absent, network times out, or rate limits occur, the system immediately switches to a rule-based regex heuristics engine. Ticket creation and management never fail.
3. **Operational Efficiency for Support Desk**:
   - Designed queue urgency weighting (`Critical > High > Medium > Low` + FIFO), one-click ticket self-claiming (`assigned_to`), and pre-populated editable AI drafts to eliminate repetitive typing.

---

## 3. Trade-Offs Made
| Architectural Decision | Chosen Approach | Trade-Off Rationale |
| :--- | :--- | :--- |
| **Frontend Rendering** | Alpine.js + Tailwind CSS on Blade | Avoided the build-step bloat and SSR dehydration overhead of Next.js for a rapid, single-bundle, reactive reactive store that boots instantly without Node server dependencies. |
| **Real-Time Synchronization** | High-Frequency Smart Polling (6s) | Implemented polling with tab/modal focus safeguards rather than full WebSockets (Pusher/Reverb) to eliminate external socket daemon dependencies, server stateful socket connections, and configuration complexity for reviewers. |
| **Document Store** | MongoDB ODM (`mongodb/laravel-mongodb`) | Chose MongoDB over strict SQL migrations to handle dynamic ticket schemas, optional attachment metadata, and varying nested book attributes across production and live stages. |

---

## 4. How to Evolve This into an Enterprise Production System
1. **Asynchronous Queue Workers**:
   - Offload OpenAI classification and draft generation calls to background workers (`php artisan queue:work` backed by Redis) so ticket creation response latency drops below 80ms.
2. **Retrieval-Augmented Generation (RAG)**:
   - For a larger knowledge base (e.g. hundreds of shipping policies and print partner contracts), implement vector embeddings using `pgvector` or Pinecone to inject only top-3 semantic chunks rather than the static system prompt.
3. **Webhooks & Multi-Channel Sync**:
   - Connect author ticket dispatch to SendGrid/Postmark for automatic email notification delivery with incoming email parsing (reply-to-ticket loops).
