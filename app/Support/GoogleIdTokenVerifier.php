<?php

namespace App\Support;

use Google\Auth\AccessToken;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Verifies a Google Identity Services ID token with Google's own library.
 *
 * AccessToken::verify() fetches Google's current signing keys and checks the
 * RS256 signature, expiry (`exp`, with `iat`/`nbf`), audience (`aud` must be
 * this app's client ID) and issuer (`iss` must be accounts.google.com). On top
 * of that, the email must be one Google has verified — otherwise the address
 * says nothing about who is signing in.
 */
class GoogleIdTokenVerifier
{
    public function __construct(
        private AccessToken $accessToken,
        private string $clientId,
    ) {}

    /**
     * Return the token's identity claims, or null if the token is not one to trust.
     *
     * @return array{sub: string, email: string, name: ?string, hosted_domain: ?string}|null
     */
    public function verify(string $idToken): ?array
    {
        if ($this->clientId === '') {
            Log::error('Google sign-in is not configured: set GOOGLE_CLIENT_ID.');

            return null;
        }

        try {
            $claims = $this->accessToken->verify($idToken, [
                'audience' => $this->clientId,
                'throwException' => true,
            ]);
        } catch (Throwable $exception) {
            Log::notice('Rejected a Google ID token.', ['reason' => $exception->getMessage()]);

            return null;
        }

        if (! is_array($claims)
            || empty($claims['sub'])
            || empty($claims['email'])
            || ($claims['email_verified'] ?? false) !== true) {
            return null;
        }

        return [
            'sub' => (string) $claims['sub'],
            'email' => mb_strtolower((string) $claims['email']),
            'name' => isset($claims['name']) ? (string) $claims['name'] : null,
            'hosted_domain' => isset($claims['hd']) ? mb_strtolower((string) $claims['hd']) : null,
        ];
    }
}
