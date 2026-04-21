<?php

namespace HubSoluciones\LivewireFluxTables\Tests\Feature;

use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire\UsersTable;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Models\FixtureUser;
use HubSoluciones\LivewireFluxTables\Tests\TestCase;
use HubSoluciones\LivewireFluxTables\Query\QueryPipeline;
use HubSoluciones\LivewireFluxTables\Rendering\CellRenderer;
use HubSoluciones\LivewireFluxTables\Rendering\StickyColumnManager;
use Livewire\Livewire;

class FluxTableComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        FixtureUser::query()->create([
            'name' => 'Ana Gomez',
            'email' => 'ana@example.test',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => '2026-01-03 10:00:00',
            'updated_at' => '2026-01-03 10:00:00',
        ]);

        FixtureUser::query()->create([
            'name' => 'Carlos Ruiz',
            'email' => 'carlos@example.test',
            'role' => 'user',
            'status' => 'inactive',
            'created_at' => '2026-01-15 10:00:00',
            'updated_at' => '2026-01-15 10:00:00',
        ]);

        FixtureUser::query()->create([
            'name' => 'Maria Diaz',
            'email' => 'maria@example.test',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => '2026-02-10 10:00:00',
            'updated_at' => '2026-02-10 10:00:00',
        ]);

        FixtureUser::query()->create([
            'name' => 'Pedro Leon',
            'email' => 'pedro@example.test',
            'role' => 'user',
            'status' => 'inactive',
            'created_at' => '2026-02-20 10:00:00',
            'updated_at' => '2026-02-20 10:00:00',
        ]);
    }

    public function test_global_search_filters_only_searchable_columns(): void
    {
        Livewire::test(UsersTable::class)
            ->set('search', 'Maria')
            ->assertSee('Maria Diaz')
            ->assertDontSee('Ana Gomez');
    }

    public function test_text_filter_applies_to_the_query(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableFilters.name', 'Ana')
            ->assertSee('Ana Gomez')
            ->assertDontSee('Carlos Ruiz');
    }

    public function test_select_filter_applies_to_the_query(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableFilters.role', 'admin')
            ->assertSee('Ana Gomez')
            ->assertSee('Maria Diaz')
            ->assertDontSee('Carlos Ruiz');
    }

    public function test_date_filter_applies_to_the_query(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableFilters.created_at', '2026-01-15')
            ->assertSee('Carlos Ruiz')
            ->assertDontSee('Ana Gomez');
    }

    public function test_date_range_filter_applies_to_the_query(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableFilters.created_between.from', '2026-02-01')
            ->set('tableFilters.created_between.to', '2026-02-28')
            ->assertSee('Maria Diaz')
            ->assertSee('Pedro Leon')
            ->assertDontSee('Ana Gomez');
    }

    public function test_sorting_toggles_between_ascending_and_descending(): void
    {
        Livewire::test(UsersTable::class)
            ->call('sortBy', 'name')
            ->assertSeeInOrder(['Ana Gomez', 'Carlos Ruiz'])
            ->call('sortBy', 'name')
            ->assertSeeInOrder(['Pedro Leon', 'Maria Diaz']);
    }

    public function test_pagination_moves_between_pages(): void
    {
        Livewire::test(UsersTable::class)
            ->assertSee('Ana Gomez')
            ->assertDontSee('Maria Diaz')
            ->call('gotoPage', 2)
            ->assertSee('Maria Diaz')
            ->assertDontSee('Ana Gomez');
    }

    public function test_query_string_state_is_hydrated(): void
    {
        Livewire::withQueryParams([
            'search' => 'Carlos',
            'role' => 'user',
            'sort' => 'name',
            'direction' => 'desc',
            'perPage' => 25,
        ])->test(UsersTable::class)
            ->assertSet('search', 'Carlos')
            ->assertSet('tableFilters.role', 'user')
            ->assertSet('sort', 'name')
            ->assertSet('direction', 'desc')
            ->assertSet('perPage', 25);
    }

    public function test_custom_cell_views_render_inside_table_cells(): void
    {
        Livewire::test(UsersTable::class)
            ->assertSee('Estado: active');
    }

    public function test_sticky_columns_generate_metadata(): void
    {
        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $metadata = $component->stickyMetadata();

        $this->assertStringContainsString('position: sticky', $metadata[1]['style']);
        $this->assertStringContainsString('left: 0px', $metadata[1]['style']);
    }
}
