<div>
    @if ($editMode)
        <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('Edit employee') }}</h2>
    @else
        <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('New employee') }}</h2>
    @endif
    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl w-full max-w-2xl p-6 relative">
        <form wire:submit.prevent="{{ $editMode ? 'update' : 'store' }}">
            <div class="mb-3">
                <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Name') }}</label>
                <input autofocus type="text" wire:model="name" maxlength="100"
                    class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-3">
                <label class="block text-zinc-700 dark:text-zinc-200 mb-1 mt-5">{{ __('Email') }}</label>
                <input type="text" wire:model="email" maxlength="100"
                    class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 mb-5" />
                @error('email')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            @if ($createMode)
                <!-- Password -->
                <flux:input wire:model="password" :label="__('Password')" type="password" required
                    autocomplete="new-password" :placeholder="__('Password')" viewable class="mt-5 mb-5" />

                <!-- Confirm Password -->
                <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" required
                    autocomplete="new-password" :placeholder="__('Confirm password')" viewable class="mt-5 mb-5" />
            @endif

            <label class="block text-zinc-700 dark:text-zinc-200 mb-1 mt-5">{{ __('Role') }}</label>
            <div class="mb-3 flex flex-row gap-6">
                @foreach ($roles as $rol)
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="role" value="{{ $rol->value }}"
                            class="form-radio text-green-600 dark:bg-zinc-800" />
                        <span class="ml-2 text-zinc-700 dark:text-zinc-200">{{ $rol->label() }}</span>
                    </label>
                @endforeach
                @error('role')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex gap-2 mt-10 ">
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">{{ __('Save') }}
                </button>
                <button type="button" wire:click="closeModal"
                    class="px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">{{ __('Back') }}</button>
            </div>
        </form>
    </div>
</div>
