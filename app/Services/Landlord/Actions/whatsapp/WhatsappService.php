<?php

namespace App\Services\Landlord\Actions\whatsapp;

use App\DTOs\Landlord\Whatsapp\WhatsappPhoneDTO;
use App\Enum\WhatsappPhoneStatusEnum;
use App\Models\Landlord\WhatsappPhone;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WhatsappService extends BaseService
{
    /**
     * Return the filter class for users.
     */
    protected function getFilterClass(): ?string
    {
        return null;
    }

    /**
     * Return the base query for users.
     */
    protected function baseQuery(): Builder
    {
        return WhatsappPhone::query();
    }

    public function createOrUpdateWhatsappPhone(WhatsappPhoneDTO $whatsappPhoneDTO): Model
    {
        return $this->getQuery()->updateOrCreate(
            ['tenant_id' => $whatsappPhoneDTO->tenant_id, 'phone_number' => $whatsappPhoneDTO->phone_number],
            $whatsappPhoneDTO->toArray()
        );
    }

    public function receiveQrcode($account_id, $qrcode)
    {
        $whatsappAccount = $this->findById($account_id);

        return $whatsappAccount->update(['qr_code' => $qrcode, 'last_update' => now(), 'status' => WhatsappPhoneStatusEnum::QR_PENDING->value]);
    }

    public function updateWhatsappAccountData($account_id, WhatsappPhoneDTO $whatsappPhoneDTO): ?Model
    {

        // 1️⃣ Try to find existing account for same tenant & phone
        $existing = $this->getQuery()
            ->where('tenant_id', $whatsappPhoneDTO->tenant_id)
            ->where('phone_number', $whatsappPhoneDTO->phone_number)
            ->first();

        // 2️⃣ If it exists but not same account, reuse existing
        if ($existing && $existing->id !== $account_id) {
            $existing->update($whatsappPhoneDTO->toFilteredArray());

            return $existing;
        }

        $account = $this->findById($account_id);
        $account->update($whatsappPhoneDTO->toFilteredArray());

        return $account;
    }
}
