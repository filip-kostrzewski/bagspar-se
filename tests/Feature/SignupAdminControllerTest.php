<?php

namespace Tests\Feature;

use App\Models\PilotLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SignupAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_are_not_found_when_the_token_is_unset(): void
    {
        config(['bagspar.admin_token' => null]);

        $this->get(route('admin.signups'))->assertNotFound();
        $this->get(route('admin.signups.export'))->assertNotFound();
        $this->withBasicAuth('bagspar', 'pilot-secret')
            ->get(route('admin.signups'))
            ->assertNotFound();
        $this->withToken('pilot-secret')
            ->get(route('admin.signups.export'))
            ->assertNotFound();
    }

    public function test_admin_pages_are_not_found_when_the_token_is_blank(): void
    {
        config(['bagspar.admin_token' => '']);

        $this->withToken('pilot-secret')->get(route('admin.signups'))->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee('admin/anmalningar', false);
    }

    public function test_missing_or_wrong_credentials_are_rejected(): void
    {
        config(['bagspar.admin_token' => 'pilot-secret']);

        $this->get(route('admin.signups'))
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Basic realm="Bågspår", charset="UTF-8"')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->withBasicAuth('pilot-secret', 'wrong')
            ->get(route('admin.signups'))
            ->assertUnauthorized();

        $this->withToken('wrong')
            ->get(route('admin.signups.export'))
            ->assertUnauthorized();

        $this->get(route('admin.signups').'?token=pilot-secret')
            ->assertUnauthorized();
    }

    public function test_wrong_credentials_are_throttled_before_the_token_check(): void
    {
        config(['bagspar.admin_token' => 'pilot-secret']);

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->withToken('wrong')->get(route('admin.signups'))->assertUnauthorized();
        }

        $this->withToken('wrong')->get(route('admin.signups'))->assertStatus(429);
        $this->withToken('pilot-secret')->get(route('admin.signups'))->assertStatus(429);
    }

    public function test_basic_auth_and_bearer_token_list_every_saved_signup(): void
    {
        config(['bagspar.admin_token' => 'pilot-secret']);
        $this->travelTo('2026-10-15 12:00:00');

        PilotLead::factory()->create([
            'company' => 'Äldre verkstad',
            'email' => 'aldre@example.test',
            'created_at' => now()->subHour(),
        ]);
        PilotLead::factory()->create([
            'company' => 'Båghantverket',
            'email' => 'verkstad@example.test',
            'created_at' => now(),
        ]);

        $basic = $this->withBasicAuth('någon', 'pilot-secret')->get(route('admin.signups'));
        $bearer = $this->withToken('pilot-secret')->get(route('admin.signups'));

        foreach ([$basic, $bearer] as $response) {
            $response->assertOk();
            $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
            $response->assertHeader('Cache-Control', 'no-store, private');
            $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
            $response->assertSeeInOrder([
                'Båghantverket',
                'verkstad@example.test',
                '2026-10-15 14:00',
                'Äldre verkstad',
                'aldre@example.test',
                '2026-10-15 13:00',
            ], false);
            $response->assertSee('href="'.route('admin.signups.export').'"', false);
            $response->assertSee('2 anmälningar', false);
        }
    }

    public function test_signup_list_escapes_company_names(): void
    {
        config(['bagspar.admin_token' => 'pilot-secret']);
        PilotLead::factory()->create([
            'company' => 'Båge <script>alert(1)</script>',
            'email' => 'verkstad@example.test',
        ]);

        $response = $this->withToken('pilot-secret')->get(route('admin.signups'));

        $response->assertOk();
        $response->assertSee('Båge &lt;script&gt;alert(1)&lt;/script&gt;', false);
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_empty_signup_list_says_there_are_no_signups(): void
    {
        config(['bagspar.admin_token' => 'pilot-secret']);

        $this->withToken('pilot-secret')
            ->get(route('admin.signups'))
            ->assertOk()
            ->assertSee('Inga anmälningar ännu.', false)
            ->assertSee('0 anmälningar', false);
    }

    #[TestWith(['=SUM(1+1)', "'=SUM(1+1)"])]
    #[TestWith(['+plus', "'+plus"])]
    #[TestWith(['-minus', "'-minus"])]
    #[TestWith(['@at', "'@at"])]
    #[TestWith(["\tcmd", "'\tcmd"])]
    public function test_csv_export_neutralizes_formulas_and_uses_stockholm_time(string $company, string $exportedCompany): void
    {
        config(['bagspar.admin_token' => 'pilot-secret']);
        $this->travelTo('2026-10-15 12:00:00');
        PilotLead::factory()->create([
            'company' => $company,
            'email' => 'verkstad@example.test',
            'created_at' => now(),
        ]);

        $response = $this->withBasicAuth('bagspar', 'pilot-secret')
            ->get(route('admin.signups.export'));

        $response->assertOk();
        $response->assertDownload('anmalningar.csv');
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $csv = $response->streamedContent();
        $this->assertSame("\xEF\xBB\xBF", substr($csv, 0, 3));

        $rows = array_map(str_getcsv(...), explode("\n", trim(substr($csv, 3))));
        $this->assertSame(['Företag', 'E-post', 'Datum'], $rows[0]);
        $this->assertSame([$exportedCompany, 'verkstad@example.test', '2026-10-15 14:00'], $rows[1]);
    }

    public function test_admin_list_stays_out_of_the_sitemap(): void
    {
        config(['bagspar.admin_token' => 'pilot-secret']);

        $this->get('/sitemap.xml')->assertDontSee('admin/anmalningar', false);
        $this->get('/')->assertDontSee('/admin/anmalningar', false);
    }
}
