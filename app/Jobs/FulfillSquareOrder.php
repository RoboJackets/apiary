<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Payment;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Square\Orders\Requests\GetOrdersRequest;
use Square\Orders\Requests\UpdateOrderRequest;
use Square\SquareClient;
use Square\Types\Fulfillment;
use Square\Types\FulfillmentState;
use Square\Types\Order;
use Square\Types\OrderState;

class FulfillSquareOrder implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of attempts for this job.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly string $order_id)
    {
        $this->queue = 'square';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $square = new SquareClient(
            token: config('square.access_token'),
            options: [
                'baseUrl' => config('square.base_url'),
            ]
        );

        $getOrderResponse = $square->orders->get(new GetOrdersRequest(['orderId' => $this->order_id]));

        if ($getOrderResponse->getOrder() === null) {
            throw new Exception(
                'Error retrieving order: '.json_encode($getOrderResponse->getErrors())
            );
        }

        $retrievedOrder = $getOrderResponse->getOrder();

        $updateOrderResponse = $square->orders->update(new UpdateOrderRequest([
            'orderId' => $this->order_id,
            'order' => new Order([
                'locationId' => config('square.location_id'),
                'state' => OrderState::Completed,
                'fulfillments' => [
                    new Fulfillment([
                        'uid' => $retrievedOrder->getFulfillments()[0]->getUuid(),
                        'state' => FulfillmentState::Completed,
                    ]),
                ],
                'version' => $retrievedOrder->getVersion(),
            ]),
            'idempotencyKey' => Payment::generateUniqueId(),
        ]));

        if ($updateOrderResponse->getOrder() === null) {
            throw new Exception(
                'Error updating order: '.json_encode($getOrderResponse->getErrors())
            );
        }
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->order_id;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<string>
     */
    public function tags(): array
    {
        return ['order:'.$this->order_id];
    }
}
