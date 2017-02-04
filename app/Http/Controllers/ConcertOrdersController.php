<?php

namespace App\Http\Controllers;

use App\Billing\NotEnoughTicketsException;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{

    public $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email'           => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1']
        ]);

        try {

            $order = $concert->orderTickets(request('email'), request('ticket_quantity'));

            $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

            return response()->json([], 201);
        } catch (PaymentFailedException $exception) {
            $order->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $exception) {
            return response()->json([], 422);
        }
    }
}
