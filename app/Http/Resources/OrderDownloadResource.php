<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDownloadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'filename' => $this->filename,
            'download_limit' => (int) $this->download_limit,
            'download_count' => (int) $this->download_count,
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'download_url' => route('order.downloads.download', $this->id),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
