<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Integrations\Turbo\Actions\AddShipmentAction;
use App\Integrations\Turbo\Actions\CancelRequestAction;
use App\Integrations\Turbo\Actions\DeleteShipmentAction;
use App\Integrations\Turbo\Actions\GetAreasAction;
use App\Integrations\Turbo\Actions\GetGovernmentsAction;
use App\Integrations\Turbo\Actions\GetStatusAction;
use App\Integrations\Turbo\Actions\ResendRequestAction;
use App\Integrations\Turbo\Actions\SearchShipmentAction;
use App\Integrations\Turbo\Actions\UpdateShipmentAction;
use App\Integrations\Turbo\Requests\AddOrderRequest;
use App\Integrations\Turbo\Requests\SearchOrderRequest;
use App\Integrations\Turbo\Requests\UpdateOrderRequest;
use App\Integrations\Turbo\Resources\ShipmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

final class TurboController extends Controller
{
    public function __construct(
        private readonly AddShipmentAction $addShipmentAction,
        private readonly UpdateShipmentAction $updateShipmentAction,
        private readonly DeleteShipmentAction $deleteShipmentAction,
        private readonly SearchShipmentAction $searchShipmentAction,
        private readonly GetStatusAction $getStatusAction,
        private readonly GetGovernmentsAction $getGovernmentsAction,
        private readonly GetAreasAction $getAreasAction,
        private readonly ResendRequestAction $resendRequestAction,
        private readonly CancelRequestAction $cancelRequestAction,
    ) {
    }

    public function addOrder(AddOrderRequest $request): JsonResponse
    {
        $shipment = $this->addShipmentAction->execute($request->toDto());

        return Response::json([
            'success' => true,
            'data' => new ShipmentResource($shipment),
        ]);
    }

    public function updateOrder(UpdateOrderRequest $request): JsonResponse
    {
        $shipment = $this->updateShipmentAction->execute($request->toDto());

        return Response::json([
            'success' => true,
            'data' => new ShipmentResource($shipment),
        ]);
    }

    public function deleteOrder(Request $request): JsonResponse
    {
        $remoteOrderId = (string) $request->input('remote_order_id', $request->route('remote_order_id'));

        $deleted = $this->deleteShipmentAction->execute($remoteOrderId);

        return Response::json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }

    public function search(SearchOrderRequest $request): JsonResponse
    {
        $response = $this->searchShipmentAction->execute($request->toDto());

        return Response::json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function getStatus(Request $request, ?string $remoteOrderId = null): JsonResponse
    {
        $remoteOrderId = (string) ($remoteOrderId ?? $request->input('remote_order_id'));
        $response = $this->getStatusAction->execute($remoteOrderId);

        return Response::json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function getGovernments(): JsonResponse
    {
        $response = $this->getGovernmentsAction->execute();

        return Response::json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function getAreas(string $government): JsonResponse
    {
        $response = $this->getAreasAction->execute($government);

        return Response::json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function resend(Request $request, ?string $remoteOrderId = null): JsonResponse
    {
        $remoteOrderId = (string) ($remoteOrderId ?? $request->input('remote_order_id'));
        $response = $this->resendRequestAction->execute($remoteOrderId);

        return Response::json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function cancel(Request $request, ?string $remoteOrderId = null): JsonResponse
    {
        $remoteOrderId = (string) ($remoteOrderId ?? $request->input('remote_order_id'));
        $response = $this->cancelRequestAction->execute($remoteOrderId);

        return Response::json([
            'success' => true,
            'data' => $response,
        ]);
    }
}
