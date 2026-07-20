<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $role = $this->getRoleNames()->first();
        $approvalStatus = match ($role) {
            'Vendor' => $this->vendor?->status,
            'Rider' => $this->rider?->verification_status,
            default => 'approved',
        };

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_photo' => $this->profile_photo ? url('storage/'.$this->profile_photo) : null,
            'role' => $role,
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'is_email_verified' => $this->hasVerifiedEmail(),
            'is_phone_verified' => $this->hasVerifiedPhone(),
            'is_approved' => $approvalStatus === 'approved' || $approvalStatus === 'verified',
            'approval_status' => $approvalStatus,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
