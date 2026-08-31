<?php

namespace App\Livewire\Admin\Expenses;

use App\Livewire\Concerns\WithCompany;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination, WithCompany;

    public const PAYMENT_METHODS = ['Naqd', 'Karta', 'Bank o\'tkazmasi', 'Boshqa'];

    public string $search = '';

    public string $selectedCategory = '';

    public string $selectedStatus = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $showForm = false;

    public ?int $expenseId = null;

    public string $title = '';

    public string $description = '';

    public $amount = '';

    public string $expense_date = '';

    public string $payment_method = 'Naqd';

    public $expense_category_id = '';

    public string $status = 'pending';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'selectedCategory', 'selectedStatus', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return ExpenseCategory::query()
            ->select(['id', 'name', 'color'])
            ->forCompany($this->companyId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function createExpense(): void
    {
        $this->resetForm();
        $this->expense_date = now()->format('Y-m-d');
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $expense = Expense::query()
            ->select(['id', 'title', 'description', 'amount', 'expense_date', 'payment_method', 'expense_category_id', 'status'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $expense) {
            return;
        }

        $this->expenseId = $expense->id;
        $this->title = (string) $expense->title;
        $this->description = (string) $expense->description;
        $this->amount = $expense->amount;
        // expense_date 'date' ga cast qilingan — inputga Y-m-d shaklida beriladi.
        $this->expense_date = $expense->expense_date?->format('Y-m-d') ?? '';
        $this->payment_method = (string) ($expense->payment_method ?: 'Naqd');
        $this->expense_category_id = $expense->expense_category_id;
        $this->status = (string) $expense->status;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'expense_category_id' => [
                'required',
                Rule::exists('expense_categories', 'id')->where('company_id', $this->companyId()),
            ],
            'status' => ['required', Rule::in(Expense::STATUSES)],
        ], [
            'title.required' => 'Nomini kiriting.',
            'amount.required' => 'Summani kiriting.',
            'amount.numeric' => 'Summa raqam bo\'lishi kerak.',
            'expense_date.required' => 'Sanani tanlang.',
            'payment_method.required' => 'To\'lov turini tanlang.',
            'expense_category_id.required' => 'Kategoriyani tanlang.',
        ]);

        if ($this->expenseId) {
            Expense::query()
                ->forCompany($this->companyId())
                ->whereKey($this->expenseId)
                ->update($data);
        } else {
            Expense::create($data + [
                'company_id' => $this->companyId(),
                'user_id' => auth()->id(),
            ]);
        }

        $this->closeForm();
        $this->dispatch('toast', type: 'success', message: 'Xarajat saqlandi.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Expense::query()
            ->forCompany($this->companyId())
            ->whereKey($id)
            ->delete();

        $this->dispatch('toast', type: 'success', message: 'Xarajat o\'chirildi.');
    }

    public function updateStatus(int $id, string $status): void
    {
        if (! in_array($status, Expense::STATUSES, true)) {
            return;
        }

        Expense::query()
            ->forCompany($this->companyId())
            ->whereKey($id)
            ->update(['status' => $status]);

        $this->dispatch('toast', type: 'success', message: 'Holat o\'zgartirildi.');
    }

    private function resetForm(): void
    {
        $this->reset(['expenseId', 'title', 'description', 'amount', 'expense_date', 'expense_category_id']);
        $this->payment_method = 'Naqd';
        $this->status = 'pending';
        $this->resetValidation();
    }

    public function render()
    {
        $base = fn () => Expense::query()
            ->forCompany($this->companyId())
            ->when($this->dateFrom, fn ($q) => $q->whereDate('expense_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('expense_date', '<=', $this->dateTo))
            ->when($this->selectedCategory, fn ($q) => $q->where('expense_category_id', $this->selectedCategory))
            ->search($this->search);

        // Uch xil summa uchun uchta so'rov emas, bitta guruhlangan so'rov.
        $totals = $base()
            ->selectRaw("COALESCE(SUM(amount), 0) as total")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) as approved")
            ->first();

        $expenses = $base()
            ->select(['id', 'title', 'description', 'amount', 'expense_date', 'payment_method', 'status', 'expense_category_id', 'user_id'])
            ->with(['category:id,name,color', 'user:id,name'])
            ->when($this->selectedStatus, fn ($q) => $q->where('status', $this->selectedStatus))
            ->latest('expense_date')
            ->latest('id')
            ->paginate(15);

        return view('livewire.admin.expenses.index-livewire', [
            'expenses' => $expenses,
            'totalAmount' => (float) $totals->total,
            'pendingAmount' => (float) $totals->pending,
            'approvedAmount' => (float) $totals->approved,
        ]);
    }
}
