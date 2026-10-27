@extends('layouts.app')

@section('content')
    <!-- 1. Guest / Logged Out View -->
    <template x-if="!currentUser">
        <div>
            @include('components.shared.guest-view')
        </div>
    </template>

    <!-- 2. Author Workspace View (Strictly Scoped) -->
    <template x-if="currentUser && currentUser.role === 'author'">
        <div class="space-y-6">
            @include('components.author.books-tab')
            @include('components.author.create-ticket')
            @include('components.author.tickets-feed')
        </div>
    </template>

    <!-- 3. Admin Operations Desk (Queue & Directory) -->
    <template x-if="currentUser && currentUser.role === 'admin'">
        <div class="space-y-6">
            @include('components.admin.tickets-queue')
            @include('components.admin.directory')
        </div>
    </template>
@endsection