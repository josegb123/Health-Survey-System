<x-layouts::auth.split :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-1.5 text-center">
            <flux:heading size="xl">{{ __('Welcome back') }}</flux:heading>
            <flux:subheading>{{ __('Enter your credentials to access the system') }}</flux:subheading>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input name="email" :label="__('Email')" :value="old('email')" type="email" required
                autofocus autocomplete="email" :placeholder="__('email@example.com')" />

            <div class="relative">
                <flux:input name="password" :label="__('Password')" type="password" required
                    autocomplete="current-password" :placeholder="__('Password')" viewable />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <flux:checkbox name="remember" :label="__('Remember me')" />
            </div>

            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Log in') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth.split>
