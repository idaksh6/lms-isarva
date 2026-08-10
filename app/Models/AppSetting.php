<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

#[Fillable(['key', 'value', 'is_secret'])]
class AppSetting extends Model
{
    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
        ];
    }

    public function plainValue(): ?string
    {
        if ($this->value === null || $this->value === '') {
            return null;
        }

        if (! $this->is_secret) {
            return $this->value;
        }

        try {
            return Crypt::decryptString($this->value);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function put(string $key, ?string $value, bool $secret = false): self
    {
        $stored = $value;

        if ($secret && $value !== null && $value !== '') {
            $stored = Crypt::encryptString($value);
        }

        return static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $stored,
                'is_secret' => $secret,
            ]
        );
    }

    public static function read(string $key, mixed $default = null): mixed
    {
        $row = static::query()->where('key', $key)->first();

        if (! $row) {
            return $default;
        }

        $plain = $row->plainValue();

        return $plain === null ? $default : $plain;
    }

    public static function forget(string $key): void
    {
        static::query()->where('key', $key)->delete();
    }
}
