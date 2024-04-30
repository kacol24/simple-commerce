<div>
    <table class="w-full table-auto" style="border-spacing: var(--tw-border-spacing-x) 1rem;">
        <tr>
            <td class="text-left pb-4">
                <a href="#relationManager0" wire:click="$set('activeRelationManager', '0')">
                    Subtotal:
                </a>
            </td>
            <td class="text-right pb-4">
                {{ $getRecord()->formatted_sub_total }}
            </td>
        </tr>
        <tr>
            <td class="text-left pb-4">
                Shipping total:
            </td>
            <td class="text-right pb-4">
                {{ $getRecord()->formatted_shipping_total }}
            </td>
        </tr>
        <tr>
            <td class="text-left pb-4">
                <a href="#relationManager1" wire:click="$set('activeRelationManager', '1')">
                    Discount total:
                </a>
            </td>
            <td class="text-right pb-4" style="color: var(--color-cm-red)">
                ({{ $getRecord()->formatted_discount_total }})
            </td>
        </tr>
        <tr>
            <td class="text-left pb-4">
                <a href="#relationManager2" wire:click="$set('activeRelationManager', '2')">
                    Fees total:
                </a>
            </td>
            <td class="text-right pb-4">
                {{ $getRecord()->formatted_fees_total }}
            </td>
        </tr>
        <tr>
            <th class="text-left pb-4">
                Grand total:
            </th>
            <th class="text-right pb-4">
                <u>
                    {{ $getRecord()->formatted_grand_total }}
                </u>
            </th>
        </tr>
        <tr>
            <td class="text-left pb-4">
                <a href="#relationManager3" wire:click="$set('activeRelationManager', '3')">
                    Paid total:
                </a>
            </td>
            <td class="text-right pb-4">
                {{ $getRecord()->formatted_paid_total }}
            </td>
        </tr>
        @if($getRecord()->amount_due)
            <tr>
                <td class="text-left">
                    Amount Due:
                </td>
                <td class="text-right">
                    {{ $getRecord()->formatted_amount_due }}
                </td>
            </tr>
        @endif
    </table>
</div>
