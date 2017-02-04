<?php

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderTest extends TestCase
{

    /**
     * Use migrations here so that each time we run the test
     * our migrations are ran. Ensure that we are using
     * DB_CONNECTION = sqlite and DB_DATABASE = :memory: in
     * the phpunit.xml that way test data is loaded and forgotten
     * with each test run.
     */
    use DatabaseMigrations;

    /** @test */
    function tickets_are_released_when_an_order_is_canceled()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(10);

        $order = $concert->orderTickets('jane@example.com', 5);

        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());

        $this->assertNull(Order::find($order->id));
    }
}