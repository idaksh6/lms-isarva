@if (config('lms.show_demo_credentials'))
    @php
        $accounts = config('lms.demo_accounts', []);
        $password = config('lms.demo_password', 'password');
    @endphp
    <div class="mt-6 rounded-lg border border-brand-200 bg-brand-50/80 p-4" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="flex w-full items-center justify-between text-left text-sm font-semibold text-brand-900">
            <span>Testing logins (remove before production)</span>
            <svg class="h-4 w-4 transition" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-cloak class="mt-3 space-y-3">
            <p class="text-xs text-brand-800">
                Password for all accounts: <code class="rounded bg-white px-1.5 py-0.5 font-mono text-brand-900">{{ $password }}</code>
            </p>

            <div class="overflow-hidden rounded-lg border border-brand-200/80 bg-white text-xs">
                <table class="w-full text-left">
                    <thead class="bg-brand-100/80 text-[10px] font-semibold uppercase tracking-wide text-brand-900">
                        <tr>
                            <th class="px-2 py-1.5">Role</th>
                            <th class="px-2 py-1.5">Email</th>
                            <th class="px-2 py-1.5 hidden sm:table-cell">Name / ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-100 text-slate-700">
                        @foreach ($accounts as $account)
                            <tr class="hover:bg-slate-50">
                                <td class="px-2 py-1.5 font-medium text-brand-700">{{ $account['role'] }}</td>
                                <td class="px-2 py-1.5">
                                    <button type="button"
                                            onclick="document.getElementById('email').value='{{ $account['email'] }}'; document.getElementById('password').value='{{ $password }}';"
                                            class="font-mono text-brand-600 hover:underline"
                                            title="Fill sign-in form">
                                        {{ $account['email'] }}
                                    </button>
                                </td>
                                <td class="hidden px-2 py-1.5 sm:table-cell text-slate-500">
                                    {{ $account['name'] }}
                                    @if (! empty($account['student_id']))
                                        <span class="text-slate-400">· {{ $account['student_id'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-[10px] text-brand-700">Click an email to fill the form. Run <code class="rounded bg-white px-1">php artisan db:seed</code> if accounts are missing.</p>
        </div>
    </div>
@endif
