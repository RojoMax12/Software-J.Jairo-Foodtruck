<?php

namespace App\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

class JwtService
{   
    public function issueForUser(Authenticatable $user, array $extraClaims = []): array
    {
        $issuedAt = now()->timestamp;
        $expiresIn = $this->ttlInSeconds();
        $expiresAt = $issuedAt + $expiresIn;

        $claims = array_merge([
            'iss' => $this->issuer(),
            'sub' => (string) $user->getAuthIdentifier(),
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => $expiresAt,
            'jti' => (string) Str::uuid(),
        ], $extraClaims);

        $claims = array_filter($claims, static fn ($value) => $value !== null);

        return [
            'access_token' => JWT::encode($claims, $this->secret(), $this->algo()),
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn,
            'user' => $user,
        ];
    }

    public function decode(string $token): object
    {
        return JWT::decode($token, new Key($this->secret(), $this->algo()));
    }

    public function blacklist(object $claims): void
    {
        if (! config('jwt.blacklist_enabled')) {
            return;
        }

        $jti = $claims->jti ?? null;
        $exp = $claims->exp ?? null;

        if (! $jti || ! $exp) {
            return;
        }

        $seconds = max(1, (int) $exp - now()->timestamp);

        Cache::put($this->blacklistKey((string) $jti), true, now()->addSeconds($seconds));
    }

    public function isBlacklisted(?string $jti): bool
    {
        if (! config('jwt.blacklist_enabled') || ! $jti) {
            return false;
        }

        return Cache::has($this->blacklistKey($jti));
    }

    public function blacklistKey(string $jti): string
    {
        return 'jwt:blacklist:'.$jti;
    }

    public function secret(): string
    {
        $secret = (string) config('jwt.secret');

        if ($secret === '') {
            throw new RuntimeException('JWT_SECRET no está configurado.');
        }

        return $secret;
    }

    public function algo(): string
    {
        return (string) config('jwt.algo', 'HS256');
    }

    public function issuer(): string
    {
        return (string) config('jwt.issuer', config('app.url'));
    }

    public function ttlInSeconds(): int
    {
        return max(60, (int) config('jwt.ttl', 60) * 60);
    }
}