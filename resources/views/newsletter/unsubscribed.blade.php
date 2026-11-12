@extends('layouts.frontend')

@section('content')
<div class="mx-auto max-w-xl px-4 py-16 text-center sm:py-24">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    <h1 class="mt-6 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl">
        {{ __('You have been unsubscribed') }}
    </h1>
    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
        {{ __('We have removed :email from our newsletter mailing list. You will no longer receive marketing emails from us.', ['email' => $email]) }}
    </p>
    <div class="mt-8 flex justify-center gap-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-800 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
            {{ __('Return to store') }}
        </a>
    </div>
</div>
@endsection

