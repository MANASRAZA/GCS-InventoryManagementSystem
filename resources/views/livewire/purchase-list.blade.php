<div class="space-y-6">
    <!-- Session Messages -->
    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3 shadow-sm animate-fade-in">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm font-medium">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl flex items-center gap-3 shadow-sm animate-fade-in">
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white border border-slate-200/80 p-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Purchase Orders</h1>
            <p class="text-sm text-slate-500 mt-1">View and manage system purchase records and execute legacy migrations.</p>
        </div>

        @role('admin')
            <div class="flex items-center gap-3">
                <button
                    wire:click="runMigration"
                    wire:loading.attr="disabled"
                    wire:target="runMigration"
                    class="px-4 py-2.5 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100/80 border border-amber-200 rounded-xl transition-all duration-200 flex items-center gap-2 cursor-pointer"
                >
                    <svg wire:loading.remove wire:target="runMigration" class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 15H19.5"></path>
                    </svg>
                    <svg wire:loading wire:target="runMigration" class="animate-spin w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Run Legacy Migration</span>
                </button>

                <a
                    href="{{ route('purchases.create') }}"
                    class="px-4 py-2.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-all duration-200 flex items-center gap-2 shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>New Purchase Entry</span>
                </a>
            </div>
        @endrole
    </div>

    <!-- Purchases Table / List -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
        @if($purchases->isEmpty())
            <div class="p-12 text-center">
                <h3 class="text-base font-semibold text-slate-700">No purchases recorded</h3>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="p-4 w-12 text-center">#</th>
                            <th class="p-4">Purchase Date</th>
                            <th class="p-4 text-center">Total Items</th>
                            <th class="p-4 text-right">Grand Total</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    @foreach($purchases as $key => $purchase)
                        <tbody wire:key="purchase-group-{{ $purchase->id }}" x-data="{ open: false }" class="divide-y divide-slate-100 text-sm text-slate-600 border-b border-slate-100 last:border-b-0">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 text-center font-medium text-slate-400">
{{--                                    {{ $purchase->id }}--}}
                                    {{$key + 1}}
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-slate-900">{{ $purchase->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-slate-400">{{ $purchase->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $purchase->items->count() }} items
                                    </span>
                                </td>
                                <td class="p-4 text-right font-semibold text-emerald-600">
                                    ${{ number_format($purchase->total, 2) }}
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-3">
                                        <!-- View Details Toggle -->
                                        <button
                                            @click="open = !open"
                                            class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-all cursor-pointer"
                                            title="View Details"
                                        >
                                            <svg class="w-5 h-5 transform transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        @role('admin')
                                            <!-- Edit Route -->
                                            <a
                                                href="{{ route('purchases.edit', $purchase->id) }}"
                                                class="p-1.5 text-blue-600 hover:text-blue-500 rounded-lg hover:bg-blue-50 transition-all"
                                                title="Edit Purchase"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>

                                            <!-- Delete Action -->
                                            <button
                                                wire:click="deletePurchase({{ $purchase->id }})"
                                                wire:confirm="Are you sure you want to delete this purchase entry?"
                                                class="p-1.5 text-rose-600 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-all cursor-pointer"
                                                title="Delete Purchase"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                            <!-- Purchase Items Expandable Details Section -->
                            <tr x-show="open" x-transition.opacity style="display: none;">
                                <td colspan="5" class="bg-slate-50/55 p-4 border-t border-b border-slate-100">
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Purchase Line Items</h4>
                                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                                                        <th class="p-3 text-left">Item Name</th>
                                                        <th class="p-3 text-left">Brand</th>
                                                        <th class="p-3 text-center">Quantity</th>
                                                        <th class="p-3 text-right">Unit Price</th>
                                                        <th class="p-3 text-right">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                                    @foreach($purchase->items as $item)
                                                        <tr class="hover:bg-slate-50/30">
                                                            <td class="p-3 font-medium text-slate-900">{{ $item->item->name }}</td>
                                                            <td class="p-3 text-slate-500">{{ $item->brand->name }}</td>
                                                            <td class="p-3 text-center">{{ $item->qty }}</td>
                                                            <td class="p-3 text-right">${{ number_format($item->price, 2) }}</td>
                                                            <td class="p-3 text-right font-semibold text-emerald-600">${{ number_format($item->qty * $item->price, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 bg-slate-50/30">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
</div>
