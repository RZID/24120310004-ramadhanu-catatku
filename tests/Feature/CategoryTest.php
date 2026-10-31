<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('categories.index'))->assertRedirect(route('login'));
        $this->post(route('categories.store'), ['name' => 'Gaji', 'type' => 'income'])
            ->assertRedirect(route('login'));
    }

    public function test_the_index_lists_only_the_authenticated_users_categories(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);
        $other->categories()->create(['name' => 'Kategori Orang Lain', 'type' => TransactionType::Income]);

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Makan')
            ->assertSee('Pengeluaran')
            ->assertDontSee('Kategori Orang Lain');
    }

    public function test_the_index_shows_an_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Belum ada kategori');
    }

    public function test_a_user_can_create_a_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('categories.store'), ['name' => 'Gaji', 'type' => 'income'])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('status', 'Kategori berhasil ditambahkan.');

        $category = $user->categories()->firstWhere('name', 'Gaji');

        $this->assertNotNull($category);
        $this->assertSame(TransactionType::Income, $category->type);
    }

    public function test_the_name_is_required_and_the_type_must_be_known(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('categories.store'), ['name' => '', 'type' => 'income'])
            ->assertSessionHasErrors('name');

        $this->actingAs($user)
            ->post(route('categories.store'), ['name' => 'Gaji', 'type' => 'transfer'])
            ->assertSessionHasErrors('type');

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_a_name_can_appear_once_per_type_but_not_twice_in_the_same_type(): void
    {
        $user = User::factory()->create();
        $user->categories()->create(['name' => 'Lainnya', 'type' => TransactionType::Income]);

        $this->actingAs($user)
            ->post(route('categories.store'), ['name' => 'Lainnya', 'type' => 'income'])
            ->assertSessionHasErrors('name');

        $this->actingAs($user)
            ->post(route('categories.store'), ['name' => 'Lainnya', 'type' => 'expense'])
            ->assertRedirect(route('categories.index'));

        $this->assertSame(2, $user->categories()->count());
    }

    public function test_two_users_may_use_the_same_category_name(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $other->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);

        $this->actingAs($user)
            ->post(route('categories.store'), ['name' => 'Makan', 'type' => 'expense'])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Makan', 'user_id' => $user->id]);
    }

    public function test_a_user_can_update_their_own_category(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);

        $this->actingAs($user)
            ->get(route('categories.edit', $category))
            ->assertOk()
            ->assertSee('Makan');

        $this->actingAs($user)
            ->put(route('categories.update', $category), ['name' => 'Makan Harian', 'type' => 'expense'])
            ->assertRedirect(route('categories.index'));

        $this->assertSame('Makan Harian', $category->fresh()?->name);
    }

    public function test_a_category_without_transactions_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->create(['name' => 'Hiburan', 'type' => TransactionType::Expense]);

        $this->actingAs($user)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('status', 'Kategori berhasil dihapus.');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_a_category_used_by_a_transaction_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($user)->get(route('categories.index'))->assertOk();

        $this->actingAs($user)
            ->from(route('categories.index'))
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertSee('Kategori ini masih dipakai transaksi. Hapus atau ubah transaksinya lebih dulu.');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_a_user_cannot_read_update_or_delete_another_users_category(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $category = $other->categories()->create(['name' => 'Rahasia', 'type' => TransactionType::Income]);

        $this->actingAs($user)->get(route('categories.edit', $category))->assertForbidden();
        $this->actingAs($user)
            ->put(route('categories.update', $category), ['name' => 'Bajakan', 'type' => 'income'])
            ->assertForbidden();
        $this->actingAs($user)->delete(route('categories.destroy', $category))->assertForbidden();

        $this->assertSame('Rahasia', $category->fresh()?->name);
    }
}
