<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-950">
        <div class="relative grid h-dvh flex-col items-center justify-center sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            {{-- Left Panel: Brand Area --}}
            <div class="relative hidden h-full flex-col overflow-hidden lg:flex dark:border-r dark:border-brand-800/50">
                {{-- Gradient background --}}
                <div class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-800 to-brand-950"></div>

                {{-- Animated gradient overlay --}}
                <div class="absolute inset-0 opacity-20"
                    style="background-image: radial-gradient(ellipse 80% 60% at 50% -20%, rgba(106, 214, 255, 0.4) 0%, transparent 60%),
                                  radial-gradient(ellipse 40% 40% at 80% 80%, rgba(26, 192, 255, 0.3) 0%, transparent 50%);">
                </div>

                {{-- Subtle medical cross pattern --}}
                <div class="absolute inset-0 opacity-[0.04]"
                    style="background-image:
                        url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M28 22h4v6h6v4h-6v6h-4v-6h-6v-4h6v-6z' fill='%23ffffff'/%3E%3C/svg%3E");
                    background-size: 60px 60px;">
                </div>

                {{-- Floating decorative blobs --}}
                <div class="absolute -top-20 -right-20 h-80 w-80 rounded-full bg-brand-400/10 blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 h-60 w-60 rounded-full bg-brand-300/10 blur-3xl"></div>

                {{-- Logo & App Name --}}
                <div class="relative z-20 flex items-center gap-3 px-10 pt-10">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm ring-1 ring-white/20">
                        <x-app-logo-icon class="h-6 w-6 fill-current text-white" />
                    </span>
                    <span class="text-lg font-semibold tracking-tight text-white/90">{{ config('app.name', 'Laravel') }}</span>
                </div>

                {{-- Center Illustration --}}
                <div class="relative z-20 flex flex-1 items-center justify-center px-10">
                    <div class="w-full max-w-sm">
                        <x-auth-illustration class="w-full h-auto drop-shadow-2xl" />
                    </div>
                </div>

                {{-- Bottom Tagline --}}
                <div class="relative z-20 mt-auto px-10 pb-10">
                    <blockquote class="space-y-2 border-l-2 border-brand-400/40 pl-4">
                        <p class="text-lg leading-relaxed text-white/85">&ldquo;Transformando la atencion medica a traves de encuestas inteligentes y datos confiables.&rdquo;</p>
                        <footer class="flex items-center gap-2 text-sm text-white/50">
                            <span class="h-px w-6 bg-white/30"></span>
                            Sistema de Encuestas de Salud
                        </footer>
                    </blockquote>
                </div>
            </div>

            {{-- Right Panel: Form Area --}}
            <div class="flex w-full items-center justify-center bg-white p-6 dark:bg-zinc-950 lg:p-10">
                <div class="w-full max-w-sm">
                    {{-- Mobile Logo --}}
                    <a href="{{ route('home') }}" class="z-20 mb-8 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 shadow-sm ring-1 ring-brand-100 dark:bg-brand-950 dark:ring-brand-800">
                            <x-app-logo-icon class="h-7 w-7 fill-current text-brand-600 dark:text-brand-400" />
                        </span>
                        <span class="text-xs font-semibold tracking-widest uppercase text-zinc-500 dark:text-zinc-400">{{ config('app.name', 'Laravel') }}</span>
                    </a>

                    {{-- Desktop Logo (visible on lg screens above the form) --}}
                    <div class="mb-8 hidden flex-col items-center lg:flex">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 shadow-sm ring-1 ring-brand-100 dark:bg-brand-950 dark:ring-brand-800">
                            <x-app-logo-icon class="h-7 w-7 fill-current text-brand-600 dark:text-brand-400" />
                        </span>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
