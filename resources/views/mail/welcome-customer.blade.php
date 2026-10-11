<x-mail::message>
# Welcome to Swipewire, {{ $user->name }}

Your account is ready. Every digital product you purchase will stay in your personal download library, ready whenever you need it.

<x-mail::button :url="route('customer.dashboard')">
View my library
</x-mail::button>

Thank you for choosing Swipewire.<br>
Swipewire
</x-mail::message>
