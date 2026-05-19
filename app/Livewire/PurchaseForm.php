<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Brand;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class PurchaseForm extends Component
{
    public $purchaseId = null;
    public $isEdit = false;

    public $itemsList = [];
    public $brandsList = [];

    // Dynamic form rows array
    public $rows = [];
    public $grandTotal = 0;

    public function mount($purchase = null)
    {
        // Enforce Role Access check
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action. Only admins can access the purchase form.');
        }

        // Load data for the dropdowns
        $this->itemsList = Item::all()->toArray();
        $this->brandsList = Brand::all()->toArray();

        if ($purchase) {
            $purchaseModel = Purchase::with('items')->findOrFail($purchase);
            $this->purchaseId = $purchaseModel->id;
            $this->isEdit = true;

            foreach ($purchaseModel->items as $item) {
                $this->rows[] = [
                    'item_id' => $item->item_id,
                    'brand_id' => $item->brand_id,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'subtotal' => $item->qty * $item->price
                ];
            }
        } else {
            // Initialize with one empty row
            $this->addRow();
        }

        $this->calculateGrandTotal();
    }

    public function addRow()
    {
        $this->rows[] = [
            'item_id' => '',
            'brand_id' => '',
            'qty' => 1,
            'price' => 0,
            'subtotal' => 0
        ];
    }

    public function removeRow($index)
    {
        if (count($this->rows) > 1) {
            unset($this->rows[$index]);
            $this->rows = array_values($this->rows); // Reset keys
            $this->calculateGrandTotal();
        }
    }

    public function updatedRows($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $index = $parts[0];
            $field = $parts[1];

            // Perform instant calculations for row subtotal
            $qty = floatval($this->rows[$index]['qty'] ?? 0);
            $price = floatval($this->rows[$index]['price'] ?? 0);
            $this->rows[$index]['subtotal'] = $qty * $price;

            $this->calculateGrandTotal();
        }

        // Validate only the field that changed to avoid premature validation on untouched fields
        $rules = [
            'rows.*.item_id' => 'required|exists:items,id',
            'rows.*.brand_id' => 'required|exists:brands,id',
            'rows.*.qty' => 'required|integer|min:1',
            'rows.*.price' => 'required|numeric|min:0.01',
        ];

        $messages = [
            'rows.*.item_id.required' => 'Select an item.',
            'rows.*.brand_id.required' => 'Select a brand.',
            'rows.*.qty.min' => 'Qty must be at least 1.',
            'rows.*.price.min' => 'Price must be greater than 0.',
        ];

        try {
            $this->validateOnly($key, $rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Livewire handles this automatically, but we catch to proceed to duplicate check
        }

        // Clear and recalculate duplicate validation errors
        $errorBag = $this->getErrorBag();
        foreach ($this->rows as $idx => $row) {
            $brandKey = "rows.{$idx}.brand_id";
            if ($errorBag->has($brandKey)) {
                if (in_array("This Item & Brand combination is already listed in another row.", $errorBag->get($brandKey))) {
                    $errorBag->forget($brandKey);
                }
            }
        }

        $combinations = [];
        foreach ($this->rows as $idx => $row) {
            if (!empty($row['item_id']) && !empty($row['brand_id'])) {
                $comboKey = $row['item_id'] . '-' . $row['brand_id'];
                if (in_array($comboKey, $combinations)) {
                    $this->addError("rows.{$idx}.brand_id", "This Item & Brand combination is already listed in another row.");
                } else {
                    $combinations[] = $comboKey;
                }
            }
        }
    }

    public function calculateGrandTotal()
    {
        $this->grandTotal = collect($this->rows)->sum('subtotal');
    }

    protected function validateForm()
    {
        // Standalone row field validation
        $rules = [
            'rows.*.item_id' => 'required|exists:items,id',
            'rows.*.brand_id' => 'required|exists:brands,id',
            'rows.*.qty' => 'required|integer|min:1',
            'rows.*.price' => 'required|numeric|min:0.01',
        ];

        $messages = [
            'rows.*.item_id.required' => 'Select an item.',
            'rows.*.brand_id.required' => 'Select a brand.',
            'rows.*.qty.min' => 'Qty must be at least 1.',
            'rows.*.price.min' => 'Price must be greater than 0.',
        ];

        // 1. Run standard validation first. If it fails, it throws a ValidationException.
        $this->validate($rules, $messages);

        // 2. Clear old duplicate errors
        $errorBag = $this->getErrorBag();
        foreach ($this->rows as $index => $row) {
            $key = "rows.{$index}.brand_id";
            if ($errorBag->has($key)) {
                if (in_array("This Item & Brand combination is already listed in another row.", $errorBag->get($key))) {
                    $errorBag->forget($key);
                }
            }
        }

        // 3. Check for duplicates
        $combinations = [];
        $hasDuplicates = false;
        foreach ($this->rows as $index => $row) {
            if (!empty($row['item_id']) && !empty($row['brand_id'])) {
                $comboKey = $row['item_id'] . '-' . $row['brand_id'];
                if (in_array($comboKey, $combinations)) {
                    $this->addError("rows.{$index}.brand_id", "This Item & Brand combination is already listed in another row.");
                    $hasDuplicates = true;
                } else {
                    $combinations[] = $comboKey;
                }
            }
        }

        // 4. If duplicates are found, throw a ValidationException with the error bag messages
        if ($hasDuplicates) {
            throw \Illuminate\Validation\ValidationException::withMessages(
                $this->getErrorBag()->messages()
            );
        }
    }

    public function savePurchase()
    {
        // Recalculate subtotals and grand total to ensure consistency
        foreach ($this->rows as $index => $row) {
            $qty = floatval($row['qty'] ?? 0);
            $price = floatval($row['price'] ?? 0);
            $this->rows[$index]['subtotal'] = $qty * $price;
        }
        $this->calculateGrandTotal();

        $this->validateForm();

        DB::transaction(function () {
            if ($this->isEdit) {
                $purchase = Purchase::findOrFail($this->purchaseId);
                $purchase->update([
                    'total' => $this->grandTotal,
                ]);
                // Delete old items and insert updated ones
                $purchase->items()->delete();
            } else {
                // Create main purchase entry
                $purchase = Purchase::create([
                    'total' => $this->grandTotal,
                ]);
            }

            // Create individual line items
            foreach ($this->rows as $row) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $row['item_id'],
                    'brand_id' => $row['brand_id'],
                    'qty' => $row['qty'],
                    'price' => $row['price'],
                ]);
            }
        });

        session()->flash('message', $this->isEdit ? 'Purchase Entry updated successfully!' : 'Purchase Entry submitted successfully!');
        return redirect()->route('purchases.index');
    }

        public function render()
        {
            return view('livewire.purchase-form')
                ->layout('components.layouts.app');
        }
}
