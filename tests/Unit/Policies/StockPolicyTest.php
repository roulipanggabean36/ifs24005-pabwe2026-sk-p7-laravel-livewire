<?php

namespace Tests\Unit\Policies;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Policies\StockPolicy;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class StockPolicyTest extends TestCase
{
    private function user(int $id, string $role): User
    {
        $u = new User(['role' => $role]);
        $u->id = $id;

        return $u;
    }

    public function test_policy_rules(): void
    {
        $policy = new StockPolicy();
        $stock  = new Stock(['user_id' => 1]);
        $owner  = $this->user(1, 'member');
        $other  = $this->user(2, 'member');
        $admin  = $this->user(3, 'admin');

        $this->assertTrue($policy->view($owner, $stock));
        $this->assertFalse($policy->view($other, $stock));
        $this->assertTrue($policy->view($admin, $stock));

        $this->assertTrue($policy->create($owner));
        $this->assertFalse($policy->create($admin));

        $this->assertTrue($policy->update($owner, $stock));
        $this->assertFalse($policy->update($other, $stock));
        $this->assertFalse($policy->update($admin, $stock));

        $this->assertTrue($policy->delete($owner, $stock));
        $this->assertFalse($policy->delete($admin, $stock));
    }
}