<?php

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewConcertListingTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing()
    {
        //Arrange
        //Create a concert
        $concert = factory(Concert::class)->states('published')->create([
            'title'                  => 'Cold World',
            'subtitle'               => 'Guns Up!',
            'date'                   => Carbon::parse('January 15, 2017 8:00pm'),
            'ticket_price'           => 3200,
            'venue'                  => 'The Mosh Pit',
            'venue_address'          => '123 Example Lane',
            'city'                   => 'Baton Rouge',
            'state'                  => 'LA',
            'zip'                    => '70806',
            'additional_information' => 'For tickets, call (555) 555-5555',
        ]);
        //Act
        //View concert listing
        $this->visit('/concerts/' . $concert->id);

        //Assert
        //Check that its there
        $this->see('Cold World');
        $this->see('Guns Up!');
        $this->see('January 15, 2017');
        $this->see('8:00pm');
        $this->see('32.00');
        $this->see('The Mosh Pit');
        $this->see('123 Example Lane');
        $this->see('Baton Rouge, LA 70806');
        $this->see('For tickets, call (555) 555-5555');
    }

    /** @test */
    function user_cannot_view_unpublished_concert_listings()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();

        $this->get('/concerts/' . $concert->id);

        $this->assertResponseStatus(404);
    }

}
