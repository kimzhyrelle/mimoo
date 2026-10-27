<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ViewModalComponentTest extends TestCase
{
    public function test_shared_view_modal_and_row_view_button_render_the_expected_contract(): void
    {
        $html = Blade::render(<<<'BLADE'
<div>
  <x-row-view-button modal="view-parcel-1044" label="View parcel #1044" />
  <x-view-modal id="view-parcel-1044" title="Parcel #1044" subtitle="12 Mabini St"
                primary-label="Mark delivered" primary-form="view-parcel-1044-form">
    <form id="view-parcel-1044-form" data-view-confirm="Mark this parcel as delivered?">
      <dl class="view-modal-details"><div><dt>Destination</dt><dd>Lumban</dd></div></dl>
    </form>
  </x-view-modal>
</div>
BLADE);

        $this->assertStringContainsString('data-modal-open="view-parcel-1044"', $html);
        $this->assertStringContainsString('aria-controls="view-parcel-1044"', $html);
        $this->assertStringContainsString('class="confirm-modal-backdrop hidden"', $html);
        $this->assertStringContainsString('data-reset-on-close', $html);
        $this->assertStringNotContainsString('pill-blue', $html);
        $this->assertStringNotContainsString('In transit', $html);
        $this->assertStringContainsString('form="view-parcel-1044-form"', $html);
        $this->assertStringContainsString('data-view-primary', $html);
        $this->assertStringContainsString('data-view-confirm="Mark this parcel as delivered?"', $html);
    }
}
