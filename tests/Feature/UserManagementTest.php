<?php

namespace Tests\Feature;

use App\Auth\AvarewaseUserProvisioner;
use App\Enums\EftkadType;
use App\Enums\UserRole;
use App\Enums\UserType;
use App\Exceptions\AvarewaseAccountNotFoundException;
use App\Models\Eftkad;
use App\Models\User;
use Avarewase\SsoClient\DataObjects\AvarewaseUserInfo;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase wipes the database; refuse to run against anything but the test one.
        $this->assertSame('eftkad_test', DB::connection()->getDatabaseName());

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeUser(string $code, ?int $type = UserType::SERVANT, string $role = UserRole::USER): User
    {
        $user = User::create([
            'name' => 'User '.$code,
            'membership_code' => $code,
            'type' => $type,
            'password' => bcrypt(Str::random(10)),
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function as(User $user): array
    {
        return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken, 'Accept' => 'application/json'];
    }

    private function makeVisit(string $servantCode): Eftkad
    {
        return Eftkad::create([
            'membership_code' => 'E1C1F001NR1',
            'date' => now(),
            'mass_attendence' => 1,
            'need_eftkad_by_father' => 0,
            'servant_membership_code' => $servantCode,
            'type' => EftkadType::HOME,
        ]);
    }

    // ---- delete ----

    public function test_admin_soft_deletes_a_servant_and_keeps_their_visits(): void
    {
        $admin = $this->makeUser('ADM1', UserType::SERVANT, UserRole::ADMIN);
        $servant = $this->makeUser('SRV1');
        $visit = $this->makeVisit('SRV1');

        $this->deleteJson('/api/users/'.$servant->id, [], $this->as($admin))->assertOk();

        $this->assertSoftDeleted('users', ['id' => $servant->id]);
        $this->assertDatabaseHas('eftkads', ['id' => $visit->id, 'servant_membership_code' => 'SRV1']);
    }

    public function test_deleted_user_leaves_pickers_but_keeps_name_for_history(): void
    {
        $admin = $this->makeUser('ADM1', UserType::SERVANT, UserRole::ADMIN);
        $servant = $this->makeUser('SRV1');
        $headers = $this->as($admin);

        $before = $this->getJson('/api/settings/filters', $headers)->assertOk()->json('data');
        $this->assertContains('SRV1', array_column($before['servants'], 'membership_code'));

        $this->deleteJson('/api/users/'.$servant->id, [], $headers)->assertOk();

        $filters = $this->getJson('/api/settings/filters', $headers)->assertOk()->json('data');
        $this->assertNotContains('SRV1', array_column($filters['servants'], 'membership_code'));
        $this->assertContains(['name' => 'User SRV1', 'membership_code' => 'SRV1'], $filters['former']);
    }

    public function test_deleting_signs_the_user_out(): void
    {
        $admin = $this->makeUser('ADM1', UserType::SERVANT, UserRole::ADMIN);
        $servant = $this->makeUser('SRV1');
        $servantHeaders = $this->as($servant);

        $this->getJson('/api/user', $servantHeaders)->assertOk();
        $this->deleteJson('/api/users/'.$servant->id, [], $this->as($admin))->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $servant->id]);
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/user', $servantHeaders)->assertUnauthorized();
    }

    public function test_nobody_deletes_themselves(): void
    {
        $owner = $this->makeUser('OWN1', UserType::SERVANT, UserRole::OWNER);

        $this->deleteJson('/api/users/'.$owner->id, [], $this->as($owner))->assertForbidden();
        $this->assertNotSoftDeleted('users', ['id' => $owner->id]);
    }

    public function test_admin_cannot_delete_a_higher_or_equal_rank(): void
    {
        $admin = $this->makeUser('ADM1', UserType::SERVANT, UserRole::ADMIN);
        $otherAdmin = $this->makeUser('ADM2', UserType::SERVANT, UserRole::ADMIN);
        $superadmin = $this->makeUser('SUP1', UserType::SERVANT, UserRole::SUPERADMIN);
        $headers = $this->as($admin);

        $this->deleteJson('/api/users/'.$otherAdmin->id, [], $headers)->assertForbidden();
        $this->deleteJson('/api/users/'.$superadmin->id, [], $headers)->assertForbidden();
    }

    public function test_regular_user_cannot_delete(): void
    {
        $user = $this->makeUser('SRV1');
        $other = $this->makeUser('SRV2');

        $this->deleteJson('/api/users/'.$other->id, [], $this->as($user))->assertForbidden();
        $this->assertNotSoftDeleted('users', ['id' => $other->id]);
    }

    public function test_deleted_user_cannot_be_put_on_a_new_visit(): void
    {
        $admin = $this->makeUser('ADM1', UserType::SERVANT, UserRole::ADMIN);
        $this->makeUser('SRV1')->delete();

        $this->postJson('/api/eftkads', [
            'membership_code' => 'E1C1F001NR1',
            'date' => now()->format('d-m-Y'),
            'type' => EftkadType::HOME,
            'servant_membership_code' => 'SRV1',
        ], $this->as($admin))->assertUnprocessable()->assertJsonValidationErrors('servant_membership_code');
    }

    // ---- SSO ----

    public function test_sso_refuses_a_deleted_user_instead_of_recreating_them(): void
    {
        $this->makeUser('SRV1')->delete();

        try {
            app(AvarewaseUserProvisioner::class)->resolve(new AvarewaseUserInfo(
                sub: 'sso-123', name: 'New Name', emailVerified: true, picture: null, membershipCode: 'SRV1',
            ));
            $this->fail('A deleted user must not be able to sign in.');
        } catch (AvarewaseAccountNotFoundException) {
        }

        $this->assertSame(1, User::withTrashed()->where('membership_code', 'SRV1')->count());
        $this->assertNull(User::withTrashed()->where('membership_code', 'SRV1')->value('avarewase_sub'));
    }

    public function test_sso_first_login_links_admin_created_user_and_refreshes_their_data(): void
    {
        $admin = $this->makeUser('ADM1', UserType::SERVANT, UserRole::ADMIN);

        $created = $this->postJson('/api/users', [
            'name' => 'Typed by admin',
            'membership_code' => 'e1c1f777nr1',
            'type' => UserType::FATHER,
            'phone' => '0100000000',
        ], $this->as($admin))->assertOk()->json('data');

        $this->assertSame('E1C1F777NR1', $created['membership_code']);

        $user = app(AvarewaseUserProvisioner::class)->resolve(new AvarewaseUserInfo(
            sub: 'sso-777', name: 'Name From SSO', emailVerified: true, picture: 'https://example.test/a.png',
            email: 'father@example.test', dateOfBirth: '1980-01-02', membershipCode: 'E1C1F777NR1',
        ));

        $this->assertSame($created['id'], $user->id);
        $user->refresh();
        $this->assertSame('sso-777', $user->avarewase_sub);
        $this->assertSame('Name From SSO', $user->name);
        $this->assertSame('father@example.test', $user->email);
        $this->assertNotNull($user->email_verified_at);
        // SSO has no type or phone: what the admin entered is kept.
        $this->assertSame(UserType::FATHER, (int) $user->type);
        $this->assertSame('0100000000', $user->phone);
        $this->assertSame(1, User::where('membership_code', 'E1C1F777NR1')->count());
    }

    public function test_cannot_add_a_user_with_a_deleted_users_code(): void
    {
        $admin = $this->makeUser('ADM1', UserType::SERVANT, UserRole::ADMIN);
        $this->makeUser('SRV1')->delete();

        $this->postJson('/api/users', ['name' => 'Again', 'membership_code' => 'SRV1', 'type' => UserType::SERVANT], $this->as($admin))
            ->assertUnprocessable()->assertJsonValidationErrors('membership_code');
    }
}
