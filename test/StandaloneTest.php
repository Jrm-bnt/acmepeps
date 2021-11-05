<?php

declare(strict_types=1);

namespace test;

use entities\Product;
use entities\User;
use PHPUnit\Framework\TestCase;

final class StandaloneTest extends TestCase
{
    public function setUp(): void
    {
        require 'index.php';
        $user = new User(1);
        $user->hydrate();
        $user->pwd = 'max';
        $user->login();
    }

    public function testFirstElementOfProductsIsAnArrayOfProduct(): void
    {
        $this->assertTrue(true);
        $this->assertInstanceOf(Product::class, Product::findAllBy()[0]);
        $this->assertEquals('Max', User::getUserSession()->firstName);
    }
}