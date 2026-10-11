<?php

namespace App\Http\Controllers;

use App\Http\Requests\AskProductAssistantRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ProductAssistantController extends Controller
{
    public function store(AskProductAssistantRequest $request): JsonResponse
    {
        if (blank(config('anthropic.api_key'))) {
            return response()->json(['message' => 'The product assistant is being configured. Please browse the collection or contact support.'], 503);
        }

        $catalogue = Product::published()->get(['title', 'description', 'price', 'currency'])->map(fn (Product $product) => "{$product->title} | {$product->currency} {$product->price} | {$product->description}")->implode("\n");
        $response = Http::acceptJson()->withHeaders(['x-api-key' => config('anthropic.api_key'), 'anthropic-version' => '2023-06-01'])
            ->post('https://api.anthropic.com/v1/messages', ['model' => config('anthropic.model'), 'max_tokens' => 350, 'system' => "You are the helpful Swipewire digital-products assistant. Recommend only from this catalogue, be concise, and never invent products or prices.\n\n{$catalogue}", 'messages' => [['role' => 'user', 'content' => $request->validated('message')]]]);

        if ($response->failed()) {
            return response()->json(['message' => 'I could not answer right now. Please try again shortly.'], 503);
        }

        return response()->json(['message' => data_get($response->json(), 'content.0.text', 'I could not find an answer.')]);
    }
}
