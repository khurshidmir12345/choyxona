<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Dashboard extends Component
{
    public $startDate;
    public $endDate;
    public $selectedPeriod = 'today';
    public $chartData = [];
    public $totalRevenue = 0;
    public $totalProfit = 0;
    public $totalOrders = 0;
    public $totalApprovedExpenses = 0;
    public $topProducts = [];
    public $topCategories = [];
    public $profitMargin = 0;
    public $averageProfit = 0;
    public $dailyAverage = 0;

    public function mount()
    {
        $this->startDate = Carbon::today()->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
        $this->loadDashboardData();
    }

    public function updatedSelectedPeriod()
    {
        $this->setDateRange();
        $this->loadDashboardData();
        $this->dispatch('chart-updated');
    }

    public function updatedStartDate()
    {
        $this->selectedPeriod = 'custom';
        $this->loadDashboardData();
        $this->dispatch('chart-updated');
    }

    public function updatedEndDate()
    {
        $this->selectedPeriod = 'custom';
        $this->loadDashboardData();
        $this->dispatch('chart-updated');
    }

    private function setDateRange()
    {
        switch ($this->selectedPeriod) {
            case 'today':
                $this->startDate = Carbon::today()->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = Carbon::yesterday()->format('Y-m-d');
                $this->endDate = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    public function loadDashboardData()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        $companyId = auth()->user()->getCompany()->id;

        // Get orders for the selected period
        $orders = Order::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'done')
            ->with('orderDetails.product')
            ->get();

        // Get approved expenses for the selected period
        $approvedExpenses = Expense::where('company_id', $companyId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->get();

        // Calculate total revenue
        $this->totalRevenue = $orders->sum('total_amount');
        
        // Calculate total orders
        $this->totalOrders = $orders->count();
        
        // Calculate total approved expenses
        $this->totalApprovedExpenses = $approvedExpenses->sum('amount');

        // Calculate profit
        $this->calculateProfit($orders);

        // Calculate additional metrics
        $this->calculateAdditionalMetrics($orders);

        // Generate chart data (monthly view)
        $this->generateMonthlyChartData($startDate, $endDate, $companyId);

        // Get top products
        $this->getTopProducts($orders);

        // Get top categories
        $this->getTopCategories($orders);

        // Emit event to update chart
        $this->dispatch('chart-updated');
    }

    private function calculateProfit($orders)
    {
        $totalProfit = 0;
        
        foreach ($orders as $order) {
            foreach ($order->orderDetails as $detail) {
                if ($detail->product) {
                    $profitPerUnit = $detail->product->sell_price - $detail->product->price;
                    $totalProfit += $profitPerUnit * $detail->quantity;
                }
            }
        }
        
        $this->totalProfit = $totalProfit;
    }

    private function calculateAdditionalMetrics($orders)
    {
        // Calculate profit margin
        $this->profitMargin = $this->totalRevenue > 0 ? round(($this->totalProfit / $this->totalRevenue) * 100, 1) : 0;
        
        // Calculate average profit per order
        $this->averageProfit = $this->totalOrders > 0 ? round($this->totalProfit / $this->totalOrders) : 0;
        
        // Calculate daily average
        $daysDiff = Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1;
        $this->dailyAverage = $daysDiff > 0 ? round($this->totalRevenue / $daysDiff) : 0;
    }

    private function generateMonthlyChartData($startDate, $endDate, $companyId)
    {
        $chartData = [];
        $daysDiff = $startDate->diffInDays($endDate) + 1;

        if ($daysDiff <= 31) {
            // Kunlik chart
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dayOrders = Order::where('company_id', $companyId)
                    ->whereDate('created_at', $currentDate)
                    ->where('status', 'done')
                    ->sum('total_amount');
                $chartData[] = [
                    'date' => $currentDate->format('d.m.Y'),
                    'amount' => $dayOrders
                ];
                $currentDate->addDay();
            }
        } else {
            // Oylik chart
            $currentDate = $startDate->copy()->startOfMonth();
            $endOfPeriod = $endDate->copy()->endOfMonth();
            while ($currentDate <= $endOfPeriod) {
                $monthStart = $currentDate->copy()->startOfMonth();
                $monthEnd = $currentDate->copy()->endOfMonth();
                if ($monthStart < $startDate) {
                    $monthStart = $startDate->copy();
                }
                if ($monthEnd > $endDate) {
                    $monthEnd = $endDate->copy();
                }
                $monthOrders = Order::where('company_id', $companyId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('status', 'done')
                    ->sum('total_amount');
                $chartData[] = [
                    'date' => $currentDate->format('M Y'),
                    'amount' => $monthOrders
                ];
                $currentDate->addMonth();
            }
        }

        // Agar butun davrda hech qanday buyurtma bo'lmasa, bitta 0 nuqta chiqsin
        if (empty($chartData) || array_sum(array_column($chartData, 'amount')) === 0) {
            $chartData = [
                [
                    'date' => $startDate->format($daysDiff <= 31 ? 'd.m.Y' : 'M Y'),
                    'amount' => 0
                ]
            ];
        }

        $this->chartData = $chartData;
    }

    private function getTopProducts($orders)
    {
        $productStats = [];

        foreach ($orders as $order) {
            foreach ($order->orderDetails as $detail) {
                if ($detail->product) {
                    $productId = $detail->product->id;
                    $productName = $detail->product->name;
                    
                    if (!isset($productStats[$productId])) {
                        $productStats[$productId] = [
                            'name' => $productName,
                            'quantity' => 0,
                            'revenue' => 0
                        ];
                    }
                    
                    $productStats[$productId]['quantity'] += $detail->quantity;
                    $productStats[$productId]['revenue'] += $detail->quantity * $detail->product->sell_price;
                }
            }
        }

        // Sort by revenue and take top 5
        uasort($productStats, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        $this->topProducts = array_slice($productStats, 0, 5, true);
    }

    private function getTopCategories($orders)
    {
        $categoryStats = [];

        foreach ($orders as $order) {
            foreach ($order->orderDetails as $detail) {
                if ($detail->product && $detail->product->category) {
                    $categoryId = $detail->product->category->id;
                    $categoryName = $detail->product->category->name;
                    
                    if (!isset($categoryStats[$categoryId])) {
                        $categoryStats[$categoryId] = [
                            'name' => $categoryName,
                            'quantity' => 0,
                            'revenue' => 0
                        ];
                    }
                    
                    $categoryStats[$categoryId]['quantity'] += $detail->quantity;
                    $categoryStats[$categoryId]['revenue'] += $detail->quantity * $detail->product->sell_price;
                }
            }
        }

        // Sort by revenue and take top 5
        uasort($categoryStats, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        $this->topCategories = array_slice($categoryStats, 0, 5, true);
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
