<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_can_be_created()
    {
        $branch = Branch::create([
            'branch_code' => 'BR001',
            'branch_name' => 'Main Branch',
            'branch_address' => '123 Main Street',
        ]);

        $this->assertDatabaseHas('branches', [
            'branch_code' => 'BR001',
            'branch_name' => 'Main Branch',
            'branch_address' => '123 Main Street',
        ]);
    }

    public function test_fillable_properties()
    {
        $branch = new Branch();

        $this->assertEquals([
            'branch_code',
            'branch_name',
            'branch_address',
        ], $branch->getFillable());
    }
}

// * Ringkasan Eksekusi PHPUnit:
// * 
// * PHPUnit berhasil dijalankan dan semua tes lulus:
// * - Jumlah tes: 2
// * - Jumlah assertion: 2