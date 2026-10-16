<section class="site-container py-8 sm:py-12">
    <header class="mb-7">
        <p class="eyebrow">{{ __('biik.account') }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-forest-950">{{ __('biik.settings') }}</h1>
        <p class="mt-2 max-w-2xl text-forest-800/75">{{ __('biik.theme_description') }}</p>
    </header>

    @if (session('status'))
        <p role="status" class="mb-5 rounded-xl bg-green-50 p-3 text-sm text-green-900">{{ session('status') }}</p>
    @endif

    <form wire:submit="save" class="max-w-3xl rounded-2xl border border-forest-950/10 bg-white p-5 shadow-sm sm:p-7">
        <fieldset>
            <legend class="text-lg font-semibold text-forest-950">{{ __('biik.background_theme') }}</legend>
            <p class="mt-1 text-sm text-forest-800/75">{{ __('biik.background_theme_help') }}</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                @foreach (['soft-neutral', 'clean-white', 'cool-gray'] as $theme)
                    <label class="cursor-pointer rounded-xl border p-4 transition hover:border-forest-700 focus-within:ring-2 focus-within:ring-forest-700"
                           @class(['border-forest-700 bg-cream-50' => $background === $theme, 'border-forest-950/15' => $background !== $theme])>
                        <span class="flex items-center gap-2 font-semibold text-forest-950">
                            <input type="radio" wire:model="background" value="{{ $theme }}" class="form-checkbox">
                            {{ __('biik.themes.'.$theme) }}
                        </span>
                        <span class="mt-3 block h-12 rounded-lg border border-forest-950/10 {{ ['soft-neutral' => 'bg-slate-100', 'clean-white' => 'bg-white', 'cool-gray' => 'bg-slate-200'][$theme] }}" aria-hidden="true"></span>
                    </label>
                @endforeach
            </div>
            @error('background') <p role="alert" class="form-error">{{ $message }}</p> @enderror
        </fieldset>

        <fieldset class="mt-8">
            <legend class="text-lg font-semibold text-forest-950">{{ __('biik.accent_color') }}</legend>
            <p class="mt-1 text-sm text-forest-800/75">{{ __('biik.accent_color_help') }}</p>
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach (['blue', 'teal', 'purple'] as $color)
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-forest-950/15 px-4 py-2 font-semibold text-forest-950 focus-within:ring-2 focus-within:ring-forest-700">
                        <input type="radio" wire:model="accent" value="{{ $color }}" class="form-checkbox">
                        <span class="h-3 w-3 rounded-full {{ ['blue' => 'bg-blue-600', 'teal' => 'bg-teal-600', 'purple' => 'bg-purple-600'][$color] }}" aria-hidden="true"></span>
                        {{ __('biik.accents.'.$color) }}
                    </label>
                @endforeach
            </div>
            @error('accent') <p role="alert" class="form-error">{{ $message }}</p> @enderror
        </fieldset>

        <div class="mt-8 flex flex-wrap items-center gap-3">
            <button type="submit" wire:loading.attr="disabled" class="button button-primary">
                <span wire:loading.remove>{{ __('biik.save_theme') }}</span>
                <span wire:loading>{{ __('biik.saving') }}</span>
            </button>
        </div>
    </form>
</section>
