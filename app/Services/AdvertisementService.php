<?php

namespace App\Services;

use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class AdvertisementService
{
    public function create(array $data, User $user, UploadedFile $image): Advertisement
    {
        $data['created_by'] = $user->id;
        $data['image_path'] = $image->store('advertisements', 'public');
        $data['placement'] = $this->legacyPlacement($data['position']);
        unset($data['image']);

        return Advertisement::create($data);
    }

    public function update(Advertisement $advertisement, array $data, ?UploadedFile $image = null): Advertisement
    {
        if ($image) {
            $data['image_path'] = $image->store('advertisements', 'public');
        }

        if (isset($data['position'])) {
            $data['placement'] = $this->legacyPlacement($data['position']);
        }

        unset($data['image']);
        $advertisement->update($data);

        return $advertisement->refresh();
    }

    public function active(?string $position = null)
    {
        return Advertisement::active()
            ->when($position, fn ($query) => $query->where('position', $position))
            ->latest()
            ->get();
    }

    private function legacyPlacement(string $position): string
    {
        return match ($position) {
            'homepage_slider', 'homepage_banner' => 'home_banner',
            'category_banner' => 'category_banner',
            'sidebar' => 'sidebar',
            default => 'product_page',
        };
    }
}
