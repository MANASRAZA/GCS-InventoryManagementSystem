<div>
    <!-- Back to purchases list -->
    <div class="mb-6 flex items-center justify-between">
        <a 
            href="{{ route('purchases.index') }}" 
            class="text-sm font-semibold text-slate-500 hover:text-slate-900 transition-all flex items-center gap-2 group"
        >
            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Back to Purchases</span>
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-slate-200 p-8 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 mb-2">
            {{ $isEdit ? 'Edit Purchase Entry' : 'New Purchase Entry' }}
        </h2>
        <p class="text-sm text-slate-500 mb-6">
            Fill in the details below to {{ $isEdit ? 'update the' : 'record a new' }} purchase entry. Live calculations and validations are executed as you type.
        </p>

        <form wire:submit.prevent="savePurchase">
            <!-- Alpine initialized container for dynamic UI reactivity -->
            <div x-data="{ rows: @entangle('rows') }">
                <div class="overflow-x-auto border border-slate-200 rounded-xl bg-slate-50/50 mb-6">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="p-4 w-1/3">Item Name</th>
                                <th class="p-4 w-1/3">Brand</th>
                                <th class="p-4 w-28 text-center">Qty</th>
                                <th class="p-4 w-36 text-right">Unit Price</th>
                                <th class="p-4 w-36 text-right">Subtotal</th>
                                <th class="p-4 w-16 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rows as $index => $row)
                                <tr class="hover:bg-slate-50/30 transition-colors" wire:key="row-{{ $index }}">
                                    <!-- Item Dropdown -->
                                    <td class="p-4">
                                        <div class="relative">
                                            <select 
                                                wire:model.live="rows.{{ $index }}.item_id" 
                                                class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-all cursor-pointer"
                                            >
                                                <option value="">-- Choose Item --</option>
                                                @foreach($itemsList as $item)
                                                    <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error("rows.{$index}.item_id") 
                                            <span class="text-xs text-rose-600 mt-1.5 block animate-fade-in font-medium">{{ $message }}</span> 
                                        @enderror
                                    </td>

                                    <!-- Brand Dropdown -->
                                    <td class="p-4">
                                        <div class="relative">
                                            <select 
                                                wire:model.live="rows.{{ $index }}.brand_id" 
                                                class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-all cursor-pointer"
                                            >
                                                <option value="">-- Choose Brand --</option>
                                                @foreach($brandsList as $brand)
                                                    <option value="{{ $brand['id'] }}">{{ $brand['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error("rows.{$index}.brand_id") 
                                            <span class="text-xs text-rose-600 mt-1.5 block animate-fade-in font-medium">{{ $message }}</span> 
                                        @enderror
                                    </td>

                                    <!-- Quantity Input -->
                                    <td class="p-4">
                                        <input 
                                            type="number" 
                                            wire:model.live.debounce.500ms="rows.{{ $index }}.qty" 
                                            class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 text-center focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-all" 
                                            min="1"
                                        >
                                        @error("rows.{$index}.qty") 
                                            <span class="text-xs text-rose-600 mt-1.5 block animate-fade-in font-medium text-center">{{ $message }}</span> 
                                        @enderror
                                    </td>

                                    <!-- Price Input -->
                                    <td class="p-4">
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm">$</span>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                wire:model.live.debounce.500ms="rows.{{ $index }}.price" 
                                                class="w-full p-2.5 pl-7 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 text-right focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-all" 
                                                min="0"
                                            >
                                        </div>
                                        @error("rows.{$index}.price") 
                                            <span class="text-xs text-rose-600 mt-1.5 block animate-fade-in font-medium text-right">{{ $message }}</span> 
                                        @enderror
                                    </td>

                                    <!-- Row Subtotal -->
                                    <td class="p-4 text-right font-semibold text-slate-700">
                                        ${{ number_format($row['subtotal'], 2) }}
                                    </td>

                                    <!-- Delete Row Button -->
                                    <td class="p-4 text-center">
                                        @if(count($rows) > 1)
                                            <button 
                                                type="button" 
                                                wire:click="removeRow({{ $index }})" 
                                                class="p-2 text-rose-600 hover:text-rose-700 rounded-lg hover:bg-rose-50 transition-all cursor-pointer"
                                                title="Remove Row"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @else
                                            <span class="p-2 text-slate-300 block opacity-50 cursor-not-allowed">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mt-6">
                    <button 
                        type="button" 
                        wire:click="addRow" 
                        class="px-4 py-2.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl transition-all duration-200 flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add Item Row</span>
                    </button>

                    <div class="text-lg font-semibold text-slate-600">
                        Grand Total: <span class="text-2xl font-extrabold text-emerald-600 ml-2">${{ number_format($grandTotal, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-end gap-3 border-t border-slate-200 pt-6">
                <a 
                    href="{{ route('purchases.index') }}" 
                    class="px-5 py-3 font-semibold text-sm text-slate-500 hover:text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-all duration-200 animate-fade-in"
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    @if($errors->any()) disabled @endif
                    class="px-5 py-3 font-semibold text-sm text-white bg-slate-900 rounded-xl transition-all duration-200 shadow-md {{ $errors->any() ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-800 cursor-pointer' }}"
                >
                    {{ $isEdit ? 'Update Purchase Entry' : 'Save Purchase Entry' }}
                </button>
            </div>
        </form>
    </div>
</div>
