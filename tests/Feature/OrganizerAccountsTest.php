<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganizerAccountsTest extends TestCase
{
    private const CONNECTION = 'qr_game_accounts_test';

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('These isolated tests require pdo_sqlite.');
        }

        // Use only a private in-memory database, never the project's MySQL data.
        config([
            'database.connections.'.self::CONNECTION => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
            'hashing.bcrypt.rounds' => 4,
        ]);
        DB::purge(self::CONNECTION);
        DB::setDefaultConnection(self::CONNECTION);
        Schema::swap(DB::connection(self::CONNECTION)->getSchemaBuilder());

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('games', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('status')->default('not_started');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
        $this->adminMigration()->up();
    }

    protected function tearDown(): void
    {
        DB::purge(self::CONNECTION);

        parent::tearDown();
    }

    private function adminMigration(): object
    {
        return require database_path('migrations/2026_09_26_090000_add_is_admin_to_users_table.php');
    }

    private function account(bool $admin = false, string $email = 'existing@example.test'): User
    {
        $user = new User();
        $user->name = 'Bestaande organisator';
        $user->email = $email;
        $user->password = Hash::make('Existing-password-123');
        $user->is_admin = $admin;
        $user->save();

        return $user->refresh();
    }

    private function input(): array
    {
        return [
            'name' => 'Nieuwe organisator',
            'email' => 'new@example.test',
            'password' => 'New-password-123',
            'password_confirmation' => 'New-password-123',
        ];
    }

    public function test_migration_preserves_existing_user_and_game_data(): void
    {
        $this->adminMigration()->down();
        $id = DB::table('users')->insertGetId([
            'name' => 'Bestaande organisator',
            'email' => 'old@example.test',
            'password' => Hash::make('Existing-password-123'),
            'remember_token' => 'existing-token',
            'created_at' => '2026-09-01 12:00:00',
            'updated_at' => '2026-09-01 12:00:00',
        ]);
        DB::table('games')->insert([
            'name' => 'Bestaand spel',
            'status' => 'active',
            'created_by' => $id,
        ]);
        $beforeUser = (array) DB::table('users')->where('id', $id)->first();
        $beforeGames = DB::table('games')->get()->toJson();

        $this->adminMigration()->up();

        $afterUser = (array) DB::table('users')->where('id', $id)->first();
        $this->assertSame(0, (int) $afterUser['is_admin']);
        unset($afterUser['is_admin']);
        $this->assertSame($beforeUser, $afterUser);
        $this->assertSame($beforeGames, DB::table('games')->get()->toJson());
    }

    public function test_guests_must_log_in_for_all_account_routes(): void
    {
        $this->get('/beheer/accounts')->assertRedirect(route('login'));
        $this->get('/beheer/accounts/nieuw')->assertRedirect(route('login'));
        $this->post('/beheer/accounts', $this->input())->assertRedirect(route('login'));
        $this->assertSame(0, User::count());
    }

    public function test_organizers_cannot_read_or_create_accounts_even_with_a_forged_role(): void
    {
        $organizer = $this->account();
        $this->actingAs($organizer);
        $this->get('/beheer/accounts')->assertForbidden();
        $this->get('/beheer/accounts/nieuw')->assertForbidden();
        $this->post('/beheer/accounts', $this->input() + ['is_admin' => true, 'role' => 'admin'])
            ->assertForbidden();
        $this->assertSame(1, User::count());
        $this->assertFalse($organizer->fresh()->is_admin);
    }

    public function test_administrator_can_open_the_account_pages_without_owning_a_game(): void
    {
        $admin = $this->account(true);
        $this->actingAs($admin);
        $this->get('/beheer/accounts')->assertOk()->assertSee($admin->email);
        $this->get('/beheer/accounts/nieuw')->assertOk()->assertSee('Nieuw organisatoraccount');
    }

    public function test_web_creation_hashes_passwords_and_never_grants_admin_rights(): void
    {
        $admin = $this->account(true);
        $payload = array_replace($this->input(), ['email' => 'NEW@example.test', 'is_admin' => true, 'role' => 'admin']);
        $this->actingAs($admin)->post('/beheer/accounts', $payload)
            ->assertRedirect(route('organizer.accounts.index'));

        $created = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertFalse($created->is_admin);
        $this->assertTrue(Hash::check($payload['password'], $created->password));
        $this->assertNotSame($payload['password'], $created->password);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_validation_rejects_duplicate_email_and_mismatched_passwords(): void
    {
        $admin = $this->account(true);
        $payload = array_replace($this->input(), [
            'email' => $admin->email,
            'password_confirmation' => 'Different-password-123',
        ]);
        $this->actingAs($admin)->from('/beheer/accounts/nieuw')->post('/beheer/accounts', $payload)
            ->assertRedirect('/beheer/accounts/nieuw')
            ->assertSessionHasErrors(['email', 'password'])
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation');
        $this->assertSame(1, User::count());
    }

    public function test_a_password_over_the_bcrypt_byte_limit_is_rejected_before_saving(): void
    {
        $password = str_repeat('é', 40);
        $payload = array_replace($this->input(), ['password' => $password, 'password_confirmation' => $password]);
        $this->actingAs($this->account(true))->post('/beheer/accounts', $payload)
            ->assertSessionHasErrors('password');
        $this->assertSame(1, User::count());
    }

    public function test_ordinary_accounts_keep_the_existing_login_and_dashboard_access(): void
    {
        $organizer = $this->account();
        $this->post('/beheer/inloggen', [
            'email' => $organizer->email,
            'password' => 'Existing-password-123',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($organizer);
        $this->get('/beheer')->assertOk()->assertDontSee(route('organizer.accounts.index'), false);
    }

    public function test_console_command_creates_a_new_administrator(): void
    {
        $this->artisan('qr-game:create-admin', ['email' => 'admin@example.test'])
            ->expectsQuestion('Naam van de beheerder', 'Beheerder')
            ->expectsQuestion('Wachtwoord (minimaal 12 tekens)', 'Admin-password-123')
            ->expectsQuestion('Herhaal het wachtwoord', 'Admin-password-123')
            ->assertSuccessful();
        $admin = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('Admin-password-123', $admin->password));
    }

    public function test_promoting_an_existing_account_keeps_its_password_id_and_games(): void
    {
        $organizer = $this->account();
        $game = $organizer->createdGames()->create(['name' => 'Bestaand spel', 'status' => 'active']);
        $game->refresh();
        $before = $organizer->getRawOriginal();
        $beforeGame = $game->getRawOriginal();

        $this->artisan('qr-game:create-admin', ['email' => $organizer->email])
            ->expectsConfirmation('Wil je dit bestaande account beheerder maken?', false)
            ->assertSuccessful();
        $this->assertSame($before, $organizer->fresh()->getRawOriginal());

        $this->artisan('qr-game:create-admin', ['email' => $organizer->email])
            ->expectsConfirmation('Wil je dit bestaande account beheerder maken?', true)
            ->assertSuccessful();
        $after = $organizer->fresh();
        $this->assertTrue($after->is_admin);
        foreach (['id', 'name', 'email', 'password', 'created_at', 'remember_token'] as $field) {
            $this->assertSame($before[$field], $after->getRawOriginal($field));
        }
        $this->assertSame(1, User::count());
        $this->assertSame($beforeGame, $game->fresh()->getRawOriginal());

        $beforeRepeat = $after->getRawOriginal();
        $this->artisan('qr-game:create-admin', ['email' => $organizer->email])->assertSuccessful();
        $this->assertSame($beforeRepeat, $after->fresh()->getRawOriginal());
    }
}
