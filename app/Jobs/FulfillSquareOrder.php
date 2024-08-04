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
use Square\Models\Fulfillment;
use Square\Models\Order;
use Square\Models\OrderFulfillmentState;
use Square\Models\OrderState;
use Square\Models\UpdateOrderRequest;
use Square\SquareClient;

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
        $square = new SquareClient([
            'accessToken' => config('square.access_token'),
            'environment' => config('square.environment'),
        ]);

        $ordersApi = $square->getOrdersApi();

        $retrieveOrderResponse = $ordersApi->retrieveOrder($this->order_id);

        if (! $retrieveOrderResponse->isSuccess()) {
            throw new Exception(
                'Error retrieving order: '.json_encode($retrieveOrderResponse->getErrors())
            );
        }

        $retrievedOrder = $retrieveOrderResponse->getResult()->getOrder();

        $updateFulfillment = new Fulfillment();
        $updateFulfillment->setUid($retrievedOrder->getFulfillments()[0]->getUid());
        $updateFulfillment->setState(OrderFulfillmentState::COMPLETED);

        $updateOrder = new Order(config('square.location_id'));
        $updateOrder->setState(OrderState::COMPLETED);
        $updateOrder->setFulfillments([$updateFulfillment]);
        $updateOrder->setVersion($retrievedOrder->getVersion());

        $updateOrderRequest = new UpdateOrderRequest();
        $updateOrderRequest->setOrder($updateOrder);
        $updateOrderRequest->setIdempotencyKey(Payment::generateUniqueId());

        $updateOrderResponse = $ordersApi->updateOrder($this->order_id, $updateOrderRequest);

        if (! $updateOrderResponse->isSuccess()) {
            throw new Exception(
                'Error updating order: '.json_encode($retrieveOrderResponse->getErrors())
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
