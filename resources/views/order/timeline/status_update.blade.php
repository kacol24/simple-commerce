@php
    $previousStatus = $item['log']->getExtraProperty('previous');
    $newStatus = $item['log']->getExtraProperty('new');
@endphp
<div class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">
    <div class="flex flex-col">
        Status updated
        <div class="flex items-center mt-1">
            <x-filament::badge :color="(new $previousStatus(\App\Models\Order::class))->color()">
                {{ (new $previousStatus(\App\Models\Order::class))->friendlyName() }}
            </x-filament::badge>

            @svg('heroicon-m-chevron-right', [
            'class' => 'w-4 mx-1'
            ])

            <x-filament::badge :color="(new $newStatus(\App\Models\Order::class))->color()">
                {{ (new $newStatus(\App\Models\Order::class))->friendlyName() }}
            </x-filament::badge>
        </div>
    </div>
</div>
