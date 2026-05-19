<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Purchase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Artisan;

class PurchaseList extends Component
{
    use WithPagination;

    public function render()
    {
        $purchases = Purchase::with(['items.item', 'items.brand'])->latest()->paginate(10);

        return view('livewire.purchase-list', [
            'purchases' => $purchases,
        ])->layout('components.layouts.app');
    }

    public function deletePurchase($id)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action. Only admins can perform this action.');
        }

        $purchase = Purchase::findOrFail($id);
        $purchase->delete();

        session()->flash('message', 'Purchase deleted successfully.');
    }

    public function runMigration()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action. Only admins can perform this action.');
        }

        try {
            Artisan::call('migrate:legacy-purchases');
            session()->flash('message', 'Legacy migration completed successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Migration failed: ' . $e->getMessage());
        }
    }
}
