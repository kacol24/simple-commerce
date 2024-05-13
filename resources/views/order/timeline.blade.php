<div class="relative pt-8 -ml-[5px] z-10">
    <span class="absolute inset-y-0 left-5 w-[2px] bg-gray-200 dark:bg-gray-600 rounded-full"></span>

    <div class="flow-root">
        <ul class="-my-8 divide-y-2 divide-gray-200 dark:divide-gray-600"
            role="list">
            @foreach ($activityLog as $log)
                <li class="relative ml-5 {{ ! $loop->first ? 'mt-6' : '' }}">
                    <p class="ml-8 font-bold text-gray-950 dark:text-gray-200">
                        {{ $log['date']->format('F jS, Y') }}
                    </p>
                    <ul class="mt-3 space-y-3">
                        @foreach ($log['items'] as $item)
                            @php
                                $logUserName = $item['log']->causer ? ($item['log']->causer->fullName ?: $item['log']->causer->name) : null;
                            @endphp

                            <li class="relative pl-8">
                                <div @class([
                                        'flex justify-between',
                                        'pt-[5px]' => $item['log']->causer,
                                        'pt-[1px]' => !$item['log']->causer,
                                    ])>
                                    <div>
                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                            @if (!$item['log']->causer)
                                                System
                                            @else
                                                {{ $logUserName }}
                                            @endif
                                        </div>

                                        @switch($item['log']->event)
                                            @case('created')
                                                Created order
                                                @break;
                                            @default
                                                @include('order.timeline.status_update', $item)
                                        @endswitch
                                    </div>

                                    <time
                                        class="flex-shrink-0 ml-4 text-xs mt-0.5 text-gray-500 dark:text-gray-400 font-medium">
                                        {{ $item['log']->created_at->format('H:i') }}
                                    </time>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    </div>
</div>
