<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportRequest;
use App\Mail\SupportRequestMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SupportRequestController extends Controller
{
    public function store(StoreSupportRequest $request): RedirectResponse
    {
        try {
            Mail::to(config('swipewire.mail.support_to'))->send(new SupportRequestMail($request->validated()));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['support' => 'We could not send your message right now. Please try again shortly.']);
        }

        return back()->with('support-status', 'Thank you — our support team has received your message.');
    }
}
