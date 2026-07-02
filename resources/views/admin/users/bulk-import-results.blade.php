@extends('layouts.lms')

@section('title', 'Import results')
@section('page_title', 'Import results')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="users" title="Student accounts created" subtitle="Save these login details and share them with students securely.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ count($results['created']) }}</strong> created</span>
            @if (count($results['skipped']))
                <span class="lms-stat-chip"><strong>{{ count($results['skipped']) }}</strong> skipped</span>
            @endif
            @if (count($results['invalid']))
                <span class="lms-stat-chip"><strong>{{ count($results['invalid']) }}</strong> invalid</span>
            @endif
        </div>
    </x-lms.module-hero>

    @if (count($results['created']))
        <section class="lms-panel">
            <div class="lms-panel-head">
                <h2 class="lms-panel-title">New student credentials</h2>
                <p class="lms-panel-desc">Students sign in with their email and the password below.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="lms-data-table w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Student ID</th>
                            <th class="px-4 py-3">Email (login)</th>
                            <th class="px-4 py-3">Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results['created'] as $account)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $account['name'] }}</td>
                                <td class="px-4 py-3 font-mono">{{ $account['student_id'] }}</td>
                                <td class="px-4 py-3 font-mono">{{ $account['email'] }}</td>
                                <td class="px-4 py-3 font-mono font-semibold text-brand-700">{{ $account['password'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if (count($results['skipped']))
        <section class="lms-panel">
            <div class="lms-panel-head">
                <h2 class="lms-panel-title">Skipped</h2>
            </div>
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach ($results['skipped'] as $row)
                    <li class="flex items-center justify-between px-4 py-3">
                        <span class="font-mono text-slate-700">{{ $row['email'] }}</span>
                        <span class="text-slate-500">{{ $row['reason'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if (count($results['invalid']))
        <section class="lms-panel">
            <div class="lms-panel-head">
                <h2 class="lms-panel-title">Invalid entries</h2>
            </div>
            <ul class="divide-y divide-slate-100 px-4 py-2 text-sm text-red-700">
                @foreach ($results['invalid'] as $entry)
                    <li class="py-2 font-mono">{{ $entry }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.users.bulk-import') }}" class="lms-btn-secondary">Import more</a>
        <a href="{{ route('admin.users.index') }}" class="lms-btn-primary">View all users</a>
    </div>
</div>
@endsection
