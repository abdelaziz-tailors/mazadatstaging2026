<?php

namespace Tests\Feature\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Dashboard\PartnerFinanceController;
use App\Models\Admin;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * The wallet balance card on /admin/partner-finance/wallet was redesigned to
 * match a reference design (dark green card, wallet icon, large balance
 * figure) — per explicit instruction, using only real, already-stored data
 * ($partnerUser->wallet_balance). A "pending" breakdown was requested
 * initially but dropped after confirming (via a full codebase search) that
 * no withdrawal/transfer-request concept with a pending/completed status
 * exists anywhere in this app — wallet_transactions has no status column at
 * all, so there was nothing real to show. An "available" breakdown was also
 * dropped since it would have just duplicated the same balance figure.
 */
class PartnerWalletCardTest extends TestCase
{
    use DatabaseTransactions;

    private function createPartnerAdmin(float $walletBalance): Admin
    {
        $user = User::create([
            'name' => 'Partner User',
            'phone' => '01' . random_int(100000000, 999999999),
            'password' => bcrypt('secret123'),
            'user_type' => 'vendor',
            'gender' => 'male',
            'wallet_balance' => $walletBalance,
        ]);

        return Admin::create([
            'name' => 'Partner Admin',
            'email' => 'partner' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
            'user_id' => $user->id,
        ]);
    }

    public function test_wallet_card_renders_the_real_balance_from_the_linked_user()
    {
        Auth::guard('admin')->setUser($this->createPartnerAdmin(1234.5));
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerFinanceController())->wallet()->render();

        $this->assertStringContainsString('1,234.50', $html);
        $this->assertStringContainsString(TranslationHelper::translate('current_wallet_balance'), $html);
        $this->assertStringContainsString(TranslationHelper::translate('sar_abbr'), $html);
        $this->assertStringContainsString('md-wallet-card', $html);
    }

    public function test_wallet_card_renders_zero_balance_honestly_when_the_partner_has_none()
    {
        Auth::guard('admin')->setUser($this->createPartnerAdmin(0));
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerFinanceController())->wallet()->render();

        $this->assertStringContainsString('0.00', $html);
    }

    /**
     * Regression guard: no fabricated "pending"/"available" figures — this
     * page must only ever show the one real, stored balance number.
     */
    public function test_wallet_card_does_not_show_a_fabricated_pending_or_available_breakdown()
    {
        Auth::guard('admin')->setUser($this->createPartnerAdmin(500));
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerFinanceController())->wallet()->render();

        $this->assertStringNotContainsString('md-wallet-card-bottom', $html);
        $this->assertStringNotContainsString('md-wallet-stat', $html);
    }

    public function test_wallet_card_falls_back_to_zero_when_the_admin_has_no_linked_user()
    {
        $admin = Admin::create([
            'name' => 'Orphan Partner Admin',
            'email' => 'orphan' . random_int(100000, 999999) . '@example.com',
            'password' => bcrypt('secret123'),
            'type' => 'partner',
        ]);
        Auth::guard('admin')->setUser($admin);
        view()->share('errors', new ViewErrorBag());

        $html = (new PartnerFinanceController())->wallet()->render();

        $this->assertStringContainsString('0.00', $html);
    }
}
