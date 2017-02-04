<?php

use App\Billing\NotEnoughTicketsException;
use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function can_get_formatted_date()
    {
        // Create a concert with a known date
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm')
        ]);

        //Retrieve the formatted date
        $date = $concert->formatted_date;

        //Verify the date is formatted as expected
        $this->assertEquals('December 1, 2016', $date);
    }

    /** @test */
    function can_get_formatted_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:30:00')
        ]);

        $startTime = $concert->formatted_start_time;

        $this->assertEquals('5:30pm', $startTime);
    }

    /** @test */
    function can_get_ticket_price_in_dollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => '5450'
        ]);

        $ticketPrice = $concert->ticket_price_in_dollars;

        $this->assertEquals('54.50', $ticketPrice);
    }

    /** @test */
    function concerts_with_a_publish_at_date_are_published()
    {
        $publishedConcertA = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week'),
        ]);

        $publishedConcertB = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week'),
        ]);

        $unpublishedConcert = factory(Concert::class)->create([
            'published_at' => null,
        ]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    function can_order_concert_tickets()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(3);

        $order = $concert->orderTickets('jane@example.com', 3);

        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    function can_add_tickets()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(50);

        $concert->orderTickets('john@example.com', 30);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(10);
        try {
            $concert->orderTickets('john@example.com', 11);
        } catch (NotEnoughTicketsException $exception) {
            $order = $concert->orders()->where('email', 'john@example.com')->first();
            $this->assertNull($order);
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining');
    }

    function cannot_order_tickets_that_have_already_been_purchased()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        $concert->orderTickets('john@example.com', 8);

        try {
            $concert->orderTickets('jane@example.com', 3);
        } catch (NotEnoughTicketsException $exception) {
            $janesOrder = $concert->orders()->where('email', 'jane@example.com')->first();
            $this->assertNull($janesOrder);
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining');
    }

}
