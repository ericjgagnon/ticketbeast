<?php

use App\Concert;
use App\Ticket;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TicketTest extends TestCase
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
    function a_ticket_can_be_released()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(1);

        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();

        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
    }
}