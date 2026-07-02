<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkStudentImporter
{
    /**
     * @return array{
     *     created: list<array{name: string, email: string, student_id: string, password: string}>,
     *     skipped: list<array{email: string, reason: string}>,
     *     invalid: list<string>
     * }
     */
    public function import(string $rawInput): array
    {
        $emails = $this->parseEmails($rawInput);
        $invalid = $this->invalidEmails($rawInput, $emails);
        $created = [];
        $skipped = [];

        foreach ($emails as $email) {
            if (User::query()->where('email', $email)->exists()) {
                $skipped[] = ['email' => $email, 'reason' => 'Account already exists'];

                continue;
            }

            $password = Str::password(10, symbols: false);
            $studentId = $this->nextStudentId();

            User::query()->create([
                'name' => $this->nameFromEmail($email),
                'email' => $email,
                'password' => Hash::make($password),
                'role' => UserRole::Student,
                'student_id' => $studentId,
                'email_verified_at' => now(),
            ]);

            $created[] = [
                'name' => $this->nameFromEmail($email),
                'email' => $email,
                'student_id' => $studentId,
                'password' => $password,
            ];
        }

        return compact('created', 'skipped', 'invalid');
    }

    /**
     * @return list<string>
     */
    public function parseEmails(string $rawInput): array
    {
        $tokens = preg_split('/[\s,;]+/', trim($rawInput), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $emails = [];
        foreach ($tokens as $token) {
            $email = strtolower(trim($token));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }

        return array_values(array_unique($emails));
    }

    /**
     * @param  list<string>  $validEmails
     * @return list<string>
     */
    private function invalidEmails(string $rawInput, array $validEmails): array
    {
        $tokens = preg_split('/[\s,;]+/', trim($rawInput), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $invalid = [];

        foreach ($tokens as $token) {
            $email = strtolower(trim($token));
            if ($email === '') {
                continue;
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid[] = $token;
            }
        }

        return array_values(array_unique($invalid));
    }

    public function nameFromEmail(string $email): string
    {
        $local = Str::before($email, '@');
        $local = str_replace(['.', '_', '-'], ' ', $local);

        return Str::title(trim($local));
    }

    public function nextStudentId(): string
    {
        $year = now()->format('Y');
        $prefix = "DS{$year}";

        $latest = User::query()
            ->where('student_id', 'like', "{$prefix}%")
            ->orderByDesc('student_id')
            ->value('student_id');

        $next = $latest
            ? ((int) substr($latest, strlen($prefix))) + 1
            : 1;

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
