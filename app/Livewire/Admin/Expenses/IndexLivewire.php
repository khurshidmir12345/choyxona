<?php

namespace App\Livewire\Admin\Expenses;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $showModal = false;
    public $editingExpense = null;
    public $title = '';
    public $description = '';
    public $amount = '';
    public $expense_date = '';
    public $payment_method = '';
    public $expense_category_id = '';
    public $status = 'pending';
    public $selectedCategory = '';
    public $selectedStatus = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'amount' => 'required|numeric|min:0',
        'expense_date' => 'required|date',
        'payment_method' => 'required|string|max:255',
        'expense_category_id' => 'required|exists:expense_categories,id',
        'status' => 'required|in:pending,approved,rejected',
    ];

    public function render()
    {
        $companyId = auth()->user()->getCompany()->id;
        
        // Get overall statistics (unfiltered) - separate queries for each statistic
        $totalAmount = Expense::where('company_id', $companyId)->sum('amount');
        $pendingAmount = Expense::where('company_id', $companyId)->where('status', 'pending')->sum('amount');
        $approvedAmount = Expense::where('company_id', $companyId)->where('status', 'approved')->sum('amount');
        
        // Get filtered expenses for the table
        $expenses = Expense::with(['category', 'user'])
            ->where('company_id', $companyId)
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('expense_category_id', $this->selectedCategory);
            })
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('expense_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('expense_date', '<=', $this->dateTo);
            })
            ->orderBy('expense_date', 'desc')
            ->paginate(15);

        $categories = ExpenseCategory::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.expenses.index-livewire', compact('expenses', 'categories', 'totalAmount', 'pendingAmount', 'approvedAmount'));
    }

    public function createExpense()
    {
        $this->resetForm();
        $this->expense_date = date('Y-m-d');
        $this->showModal = true;
    }

    public function editExpense($id)
    {
        $expense = Expense::find($id);
        if ($expense && $expense->company_id === auth()->user()->getCompany()->id) {
            $this->editingExpense = $expense;
            $this->title = $expense->title;
            $this->description = $expense->description;
            $this->amount = $expense->amount;
            $this->expense_date = $expense->expense_date;
            $this->payment_method = $expense->payment_method;
            $this->expense_category_id = $expense->expense_category_id;
            $this->status = $expense->status;
            $this->showModal = true;
        }
    }

    public function saveExpense()
    {
        $this->validate();

        if ($this->editingExpense) {
            // Update existing expense
            $this->editingExpense->update([
                'title' => $this->title,
                'description' => $this->description,
                'amount' => $this->amount,
                'expense_date' => $this->expense_date,
                'payment_method' => $this->payment_method,
                'expense_category_id' => $this->expense_category_id,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Xarajat muvaffaqiyatli yangilandi!');
        } else {
            // Create new expense
            Expense::create([
                'company_id' => auth()->user()->getCompany()->id,
                'user_id' => auth()->id(),
                'title' => $this->title,
                'description' => $this->description,
                'amount' => $this->amount,
                'expense_date' => $this->expense_date,
                'payment_method' => $this->payment_method,
                'expense_category_id' => $this->expense_category_id,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Xarajat muvaffaqiyatli yaratildi!');
        }

        $this->closeModal();
        $this->dispatch('$refresh');
    }

    public function deleteExpense($id)
    {
        try {
            $expense = Expense::find($id);
            
            if (!$expense) {
                session()->flash('error', 'Xarajat topilmadi!');
                return;
            }
            
            if ($expense->company_id !== auth()->user()->getCompany()->id) {
                session()->flash('error', 'Bu xarajatni o\'chirish huquqingiz yo\'q!');
                return;
            }
            
            $expenseTitle = $expense->title;
            $expense->delete();
            
            session()->flash('message', "Xarajat '{$expenseTitle}' muvaffaqiyatli o'chirildi!");
            $this->dispatch('$refresh');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    public function updateStatus($id, $status)
    {
        $expense = Expense::find($id);
        if ($expense && $expense->company_id === auth()->user()->getCompany()->id) {
            $expense->update(['status' => $status]);
            session()->flash('message', 'Xarajat holati o\'zgartirildi!');
            $this->dispatch('$refresh');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->editingExpense = null;
        $this->title = '';
        $this->description = '';
        $this->amount = '';
        $this->expense_date = '';
        $this->payment_method = '';
        $this->expense_category_id = '';
        $this->status = 'pending';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedCategory = '';
        $this->selectedStatus = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }
}
