<div>
    @php($runningTotal = 0)
    <table class="w-full table-auto" style="border-spacing: var(--tw-border-spacing-x) 1rem;">
        <tr>
            <td class="text-left pb-4 align-top">
                <a href="#relationManager0" wire:click="$set('activeRelationManager', '0')">
                    Subtotal:
                </a>
            </td>
            <td class="text-right">
                @php($runningTotal += $getRecord()->sub_total)
                {{ $getRecord()->formatted_sub_total }}
                <small class="block text-xs text-gray-400 select-none">
                    &nbsp;
                </small>
            </td>
        </tr>
        <tr>
            <td class="text-left pb-4 align-top">
                Shipping total:
            </td>
            <td class="text-right">
                @php($runningTotal += $getRecord()->shipping_total)
                {{ $getRecord()->formatted_shipping_total }}
                <small class="block text-xs text-gray-400 select-none">
                    Rp{{ number_format($runningTotal, 0, ',', '.') }}
                </small>
            </td>
        </tr>
        <tr>
            <td class="text-left pb-4 align-top">
                <a href="#relationManager1" wire:click="$set('activeRelationManager', '1')">
                    Discount total:
                </a>
            </td>
            <td class="text-right" style="color: var(--color-cm-red)">
                @php($runningTotal -= $getRecord()->discount_total)
                ({{ $getRecord()->formatted_discount_total }})
                <small class="block text-xs text-gray-400 select-none">
                    Rp{{ number_format($runningTotal, 0, ',', '.') }}
                </small>
            </td>
        </tr>
        <tr>
            <td class="text-left pb-4 align-top">
                <a href="#relationManager2" wire:click="$set('activeRelationManager', '2')">
                    Fees total:
                </a>
            </td>
            <td class="text-right pb-4">
                @php($runningTotal += $getRecord()->fees_total)
                {{ $getRecord()->formatted_fees_total }}
                <small class="block text-xs text-gray-400 select-none">
                    Rp{{ number_format($runningTotal, 0, ',', '.') }}
                </small>
            </td>
        </tr>
        @if($getRecord()->sub_total > 0)
            <tr>
                <td colspan="2" class="pb-4">
                    <hr>
                </td>
            </tr>
            <tr>
                <th class="text-left pb-4 align-top">
                    Grand total:
                </th>
                <th class="text-right pb-4">
                    <u>
                        {{ $getRecord()->formatted_grand_total }}
                    </u>
                </th>
            </tr>
            <tr>
                <td class="text-left pb-4 align-top">
                    <a href="#relationManager3" wire:click="$set('activeRelationManager', '3')">
                        Paid total:
                    </a>
                </td>
                <td class="text-right pb-4">
                    ({{ $getRecord()->formatted_paid_total }})
                </td>
            </tr>
            @if($getRecord()->grand_total > 0)
                <tr>
                    <td class="text-left">
                        Amount Due:
                    </td>
                    <td class="text-right">
                        @if($getRecord()->amount_due > 0)
                            <ins>
                                {{ $getRecord()->formatted_amount_due }}
                            </ins>
                        @else
                            PAID
                        @endif
                    </td>
                </tr>
            @endif
        @endif
    </table>
</div>
