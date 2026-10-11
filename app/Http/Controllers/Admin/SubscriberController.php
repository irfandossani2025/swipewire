<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    public function index(): View
    {
        return view('admin.subscribers.index', ['subscribers' => Subscriber::query()->latest()->paginate(50)]);
    }
}
