<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FakePaymentGatewayTest extends TestCase
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
    function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway();

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail()
    {
        try {
            $paymentGateway = new FakePaymentGateway();

            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $exception) {
            return;
        }

        $this->fail();
    }


}