<?php

namespace Tests\Feature\Dashboard;

use App\Models\SellerSubmission;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Approve/Reject were originally inside a kebab dropdown (this test used to
 * guard a hardcoded inline "text-align: left" style specific to that
 * dropdown's "Approve" item). Per explicit request, matching a design
 * reference, both were pulled out into standalone icon buttons alongside
 * view/edit — no kebab dropdown at all anymore, so the alignment bug this
 * test originally guarded can no longer occur by construction.
 */
class SellerSubmissionActionsAlignmentTest extends TestCase
{
    public function test_no_hardcoded_left_text_alignment_style_remains()
    {
        view()->share('errors', new ViewErrorBag());

        $item = new SellerSubmission(['status' => 'pending']);
        $item->id = 1;

        $html = view('dashboard.pages.seller-submissions.actions', ['item' => $item])->render();

        $this->assertStringNotContainsString('text-align: left', $html);
        $this->assertStringNotContainsString('dropdown-menu', $html);
    }
}
