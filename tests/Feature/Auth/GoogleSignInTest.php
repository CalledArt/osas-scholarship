<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\StudentRosterEntry;
use App\Models\User;
use App\Support\GoogleIdTokenVerifier;
use Firebase\JWT\JWT;
use Google\Auth\AccessToken;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use OpenSSLAsymmetricKey;
use Tests\TestCase;

/**
 * Drives Google sign-in with ID tokens signed by a key generated per test.
 *
 * Google's real AccessToken verifier runs unchanged; only the HTTP call that
 * fetches Google's public keys is answered locally with this test's key. So
 * signature, audience, issuer and expiry are checked by the same code that
 * checks real tokens in production.
 */
class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENT_ID = 'test-client-id.apps.googleusercontent.com';

    private const KEY_ID = 'test-key';

    private const EMAIL = 'juan.delacruz@cvsu.edu.ph';

    private OpenSSLAsymmetricKey $googleKey;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.client_id' => self::CLIENT_ID]);

        $this->googleKey = $this->newRsaKey();
        $publishedKey = openssl_pkey_get_details($this->googleKey)['rsa'];

        $jwks = json_encode(['keys' => [[
            'kty' => 'RSA',
            'alg' => 'RS256',
            'use' => 'sig',
            'kid' => self::KEY_ID,
            'n' => JWT::urlsafeB64Encode($publishedKey['n']),
            'e' => JWT::urlsafeB64Encode($publishedKey['e']),
        ]]]);

        $this->app->instance(GoogleIdTokenVerifier::class, new GoogleIdTokenVerifier(
            new AccessToken(fn () => new Response(200, ['Cache-Control' => 'max-age=3600'], $jwks)),
            self::CLIENT_ID,
        ));
    }

    private function newRsaKey(): OpenSSLAsymmetricKey
    {
        return openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    }

    /**
     * An ID token shaped like the ones Google issues, signed with the given key.
     *
     * @param  array<string, mixed>  $claims
     */
    private function idToken(array $claims = [], ?OpenSSLAsymmetricKey $signingKey = null): string
    {
        return JWT::encode([
            'iss' => 'https://accounts.google.com',
            'aud' => self::CLIENT_ID,
            'sub' => '109876543210987654321',
            'email' => self::EMAIL,
            'email_verified' => true,
            'name' => 'Juan Dela Cruz',
            'iat' => now()->getTimestamp(),
            'exp' => now()->addHour()->getTimestamp(),
            ...$claims,
        ], $signingKey ?? $this->googleKey, 'RS256', self::KEY_ID);
    }

    private function signInWith(string $idToken): TestResponse
    {
        return $this->post(route('login.google'), ['credential' => $idToken]);
    }

    private function assertRefused(TestResponse $response, ?string $message = null): void
    {
        $response->assertRedirect(route('login'));
        $message === null
            ? $response->assertSessionHasErrors('google')
            : $response->assertSessionHasErrors(['google' => $message]);
        $this->assertGuest();
    }

    public function test_a_rostered_student_gets_an_account_built_from_their_roster_entry(): void
    {
        StudentRosterEntry::factory()->create([
            'email' => self::EMAIL,
            'student_number' => '21-00123',
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'course' => 'BS Information Technology',
            'year_level' => 3,
        ]);

        $response = $this->signInWith($this->idToken());

        $response->assertRedirect(route('dashboard'));
        $user = User::sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertSame(self::EMAIL, $user->email);
        $this->assertSame('109876543210987654321', $user->google_id);
        $this->assertSame('Juan Santos Dela Cruz', $user->name);
        $this->assertSame('21-00123', $user->student_number);
        $this->assertSame('BS Information Technology', $user->course);
        $this->assertSame(3, $user->year_level);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse($user->must_change_password);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_a_returning_student_signs_in_to_the_same_account(): void
    {
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);
        $this->signInWith($this->idToken());
        $this->post(route('logout'));

        $this->signInWith($this->idToken())->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs(User::sole());
    }

    public function test_an_email_not_on_the_roster_is_refused_and_gets_no_account(): void
    {
        StudentRosterEntry::factory()->create(['email' => 'someone.else@cvsu.edu.ph']);

        $response = $this->signInWith($this->idToken());

        $this->assertRefused($response, self::EMAIL.' is not on the OSAS student roster. If you are an enrolled student, contact the OSAS office.');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_an_email_posted_alongside_the_token_is_ignored(): void
    {
        StudentRosterEntry::factory()->create(['email' => 'rostered@cvsu.edu.ph']);

        $response = $this->post(route('login.google'), [
            'credential' => $this->idToken(['email' => 'not.rostered@gmail.com']),
            'email' => 'rostered@cvsu.edu.ph',
        ]);

        $this->assertRefused($response);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_student_removed_from_the_roster_is_refused_at_their_next_sign_in(): void
    {
        $entry = StudentRosterEntry::factory()->create(['email' => self::EMAIL]);
        $this->signInWith($this->idToken());
        $this->post(route('logout'));
        $entry->delete();

        $this->assertRefused($this->signInWith($this->idToken()));
    }

    public function test_a_token_not_signed_by_google_is_refused(): void
    {
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);

        $response = $this->signInWith($this->idToken(signingKey: $this->newRsaKey()));

        $this->assertRefused($response, 'Google sign-in could not be verified. Please try again.');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_token_issued_to_another_app_is_refused(): void
    {
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);

        $this->assertRefused($this->signInWith($this->idToken(['aud' => 'another-app.apps.googleusercontent.com'])));
    }

    public function test_a_token_from_another_issuer_is_refused(): void
    {
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);

        $this->assertRefused($this->signInWith($this->idToken(['iss' => 'https://evil.example.com'])));
    }

    public function test_an_expired_token_is_refused(): void
    {
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);

        $response = $this->signInWith($this->idToken([
            'iat' => now()->subHours(2)->getTimestamp(),
            'exp' => now()->subHour()->getTimestamp(),
        ]));

        $this->assertRefused($response);
    }

    public function test_a_token_whose_email_google_has_not_verified_is_refused(): void
    {
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);

        $this->assertRefused($this->signInWith($this->idToken(['email_verified' => false])));
    }

    public function test_sign_in_is_refused_while_no_client_id_is_configured(): void
    {
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);
        $this->app->instance(GoogleIdTokenVerifier::class, new GoogleIdTokenVerifier(new AccessToken, ''));

        $this->assertRefused($this->signInWith($this->idToken()));
    }

    public function test_google_never_signs_in_to_an_admin_account(): void
    {
        User::factory()->admin()->create(['email' => self::EMAIL]);
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);

        $response = $this->signInWith($this->idToken());

        $this->assertRefused($response, 'This is an OSAS staff account. Staff sign in with their email and password.');
        $this->assertSame(UserRole::Admin, User::sole()->role);
        $this->assertNull(User::sole()->google_id);
    }

    public function test_an_existing_student_account_is_linked_on_first_google_sign_in(): void
    {
        $student = User::factory()->create(['email' => self::EMAIL, 'must_change_password' => true]);
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);

        $this->signInWith($this->idToken())->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($student);
        $student->refresh();
        $this->assertSame('109876543210987654321', $student->google_id);
        $this->assertFalse($student->must_change_password);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_an_account_linked_to_another_google_account_is_refused(): void
    {
        User::factory()->create(['email' => self::EMAIL, 'google_id' => 'another-google-account']);
        StudentRosterEntry::factory()->create(['email' => self::EMAIL]);

        $this->assertRefused(
            $this->signInWith($this->idToken()),
            'This student account is linked to a different Google account.',
        );
    }

    public function test_a_roster_student_number_already_used_by_another_account_is_refused_cleanly(): void
    {
        User::factory()->create(['student_number' => '21-00123']);
        StudentRosterEntry::factory()->create(['email' => self::EMAIL, 'student_number' => '21-00123']);

        $this->assertRefused(
            $this->signInWith($this->idToken()),
            'Your student number is already used by another account. Please contact the OSAS office.',
        );
    }

    public function test_a_credential_is_required(): void
    {
        $this->post(route('login.google'))->assertSessionHasErrors('credential');
        $this->assertGuest();
    }

    public function test_the_login_page_renders_googles_sign_in_button(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('https://accounts.google.com/gsi/client', false)
            ->assertSee('data-client_id="'.self::CLIENT_ID.'"', false)
            ->assertSee('class="g_id_signin"', false);
    }

    public function test_the_login_page_says_so_when_google_sign_in_is_not_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Google sign-in is not configured yet.')
            ->assertDontSee('https://accounts.google.com/gsi/client', false);
    }
}
