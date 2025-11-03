<?php

namespace App\Http\Controllers\Api\Landlord;

use App\DTOs\Landlord\Whatsapp\WhatsappPhoneDTO;
use App\Enum\WhatsappPhoneStatusEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\Whatsapp\WhatsappReadyRequest;
use App\Services\Landlord\Actions\whatsapp\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function __construct(protected readonly WhatsappService $whatsappService) {}

    /**
     * Initialize WhatsApp connection for current tenant
     */
    public function initialize()
    {
        $tenant = auth()->user()->tenant;
        $whatsappPhoneDTO = new WhatsappPhoneDTO(
            tenant_id: $tenant->id,
            status: WhatsappPhoneStatusEnum::INITIALIZING->value,
        );
        // validate tenant account
        //        if (!$tenant->canUseFeature('whatsapp_account_limit', 1)) {
        //            return ApiResponse::badRequest(message: 'WhatsApp account limit reached ! upgrade your plan to add more accounts');
        //        }

        $tenantWhatsappPhone = $this->whatsappService->createOrUpdateWhatsappPhone(whatsappPhoneDTO: $whatsappPhoneDTO);

        try {
            // Call Node.js service to initialize
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.config('services.whatsapp.api_secret_token'),
            ])->post(config('services.whatsapp.node_service_url').'/initialize', [
                'tenant_id' => $tenant->id,
                'account_id' => $tenantWhatsappPhone->id, // unique ID for this connection
            ]);

            if ($response->successful()) {
                return ApiResponse::success(data: [
                    'tenant_id' => $tenant->id,
                    'account_id' => $tenantWhatsappPhone->id,
                ], message: $response->json('message'));
            }

            return ApiResponse::error(message: 'Failed to initialize WhatsApp connection', errors: $response->json('error'));

        } catch (\Exception $e) {
            logger('Error initializing WhatsApp', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Receive QR Code from Node.js service
     */
    //    public function qrReceived(WhatsappQrcodeRecivedRequest $request)
    //    {
    //        // Update or create connection status
    //        $this->whatsappService->receiveQrcode(account_id: $request->account_id, qrcode: $request->qr);
    // //
    // //        // Broadcast to frontend
    // //        broadcast(new WhatsappStatusChanged([
    // //            'status' => 'qr_pending',
    // //            'qr_code' => $request->qr
    // //        ]));
    //
    //        return ApiResponse::success(message: 'QR code received successfully');
    //    }

    public function ready(WhatsappReadyRequest $request)
    {
        $whatsappPhoneDTO = new WhatsappPhoneDTO(
            phone_number: $request->phone_number,
            phone_label: $request->label_name,
            status: WhatsappPhoneStatusEnum::CONNECTED->value,
            connected_at: now(),
        );

        $this->whatsappService->updateWhatsappAccountData(account_id: $request->account_id, whatsappPhoneDTO: $whatsappPhoneDTO);

        // todo brodacset whatsapp status changed event

        return ApiResponse::success(message: 'WhatsApp connection ready');
    }

    public function disconnected(Request $request)
    {
        $whatsappPhoneDTO = new WhatsappPhoneDTO(
            status: WhatsappPhoneStatusEnum::DISCONNECTED->value,
            error_message: $request->reason,
        );

        $this->whatsappService->updateWhatsappAccountData(account_id: $request->account_id, whatsappPhoneDTO: $whatsappPhoneDTO);

        // todo brodacset whatsapp status changed event

        return ApiResponse::success(message: 'WhatsApp disconnected !');
    }
}
