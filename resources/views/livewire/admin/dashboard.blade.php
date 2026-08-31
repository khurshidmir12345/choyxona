<div>
    <x-ui.page-header title="Bosh sahifa" subtitle="Savdo va foyda ko'rsatkichlari">
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="selectedPeriod" class="select w-40">
                @foreach(\App\Livewire\Admin\Dashboard::PERIODS as $value => $label)
                    <option value="{{ $value }}" @selected($selectedPeriod === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="startDate" value="{{ $startDate }}" class="input w-40">
            <input type="date" wire:model.live="endDate" value="{{ $endDate }}" class="input w-40">
        </div>
    </x-ui.page-header>

    {{-- ----------------------------------------------------- asosiy raqamlar --}}
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Tushum" :value="number_format($revenue, 0, ',', ' ')" suffix="so'm"
                   icon="coins" tone="green"
                   :hint="'Kuniga o\'rtacha '.number_format($dailyAverage, 0, ',', ' ').' so\'m'"/>

        <x-ui.stat label="Yalpi foyda" :value="number_format($profit, 0, ',', ' ')" suffix="so'm"
                   icon="trend-up" tone="brand"
                   :hint="'Rentabellik '.$profitMargin.'%'"/>

        <x-ui.stat label="Xarajatlar" :value="number_format($expenses, 0, ',', ' ')" suffix="so'm"
                   icon="wallet" tone="amber" hint="Faqat tasdiqlangan"/>

        <x-ui.stat label="Sof foyda" :value="number_format($netProfit, 0, ',', ' ')" suffix="so'm"
                   :icon="$netProfit >= 0 ? 'trend-up' : 'trend-down'"
                   :tone="$netProfit >= 0 ? 'green' : 'red'"
                   hint="Foyda − xarajat"/>
    </div>

    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <x-ui.stat label="Buyurtmalar" :value="number_format($ordersCount, 0, ',', ' ')"
                   icon="receipt" tone="blue"/>
        <x-ui.stat label="O'rtacha chek" :value="number_format($averageCheck, 0, ',', ' ')" suffix="so'm"
                   icon="cart" tone="gray"/>

        @foreach($ordersByType as $row)
            @php
                $typeLabel = match($row->type?->value ?? $row->type) {
                    'delivery' => 'Yetkazib berish',
                    'takeaway' => 'Olib ketish',
                    'cafe' => 'Zalda',
                    default => 'Boshqa',
                };
            @endphp
            <x-ui.stat :label="$typeLabel" :value="number_format((int) $row->revenue, 0, ',', ' ')" suffix="so'm"
                       icon="store" tone="gray"
                       :hint="$row->orders_count.' ta buyurtma'"/>
        @endforeach
    </div>

    {{-- --------------------------------------------------------------- grafik --}}
    <div class="mt-4 card">
        <div class="card-head">
            <h2 class="card-title">Sotuv va xarajat dinamikasi</h2>
            <div class="flex items-center gap-4 text-xs font-medium text-ink-500">
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-brand-500"></span> Sotuv
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span> Xarajat
                </span>
            </div>
        </div>
        <div class="card-body">
            @if(empty($chartData))
                <x-ui.empty icon="chart" title="Ma'lumot yo'q"/>
            @else
                @php
                    $peak = max(1, max(array_merge(
                        array_column($chartData, 'sales'),
                        array_column($chartData, 'expenses')
                    )));
                @endphp
                {{-- CSS ustunli diagramma: chart kutubxonasi yuklanmaydi. --}}
                <div class="flex h-56 items-stretch gap-1 overflow-x-auto pb-1">
                    @foreach($chartData as $point)
                        <div class="group flex min-w-[1.75rem] flex-1 flex-col items-center gap-1">
                            <div class="relative flex min-h-0 flex-1 w-full items-end justify-center gap-0.5">
                                <div class="w-1/2 rounded-t bg-brand-500 transition-all group-hover:bg-brand-600"
                                     style="height: {{ max(1, round($point['sales'] / $peak * 100)) }}%"></div>
                                <div class="w-1/2 rounded-t bg-amber-400 transition-all group-hover:bg-amber-500"
                                     style="height: {{ max(1, round($point['expenses'] / $peak * 100)) }}%"></div>

                                <div class="pointer-events-none absolute -top-1 left-1/2 z-10 hidden -translate-x-1/2
                                            -translate-y-full whitespace-nowrap rounded-lg bg-ink-950 px-2.5 py-1.5
                                            text-xs text-white shadow-pop group-hover:block">
                                    <span class="block font-semibold">{{ $point['label'] }}</span>
                                    <span class="tabular block">Sotuv: {{ number_format($point['sales'], 0, ',', ' ') }}</span>
                                    <span class="tabular block">Xarajat: {{ number_format($point['expenses'], 0, ',', ' ') }}</span>
                                </div>
                            </div>
                            <span class="whitespace-nowrap text-[10px] text-ink-400">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ---------------------------------------------------------------- top --}}
    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        @foreach([
            ['Eng ko\'p sotilgan mahsulotlar', $topProducts, 'box'],
            ['Eng daromadli kategoriyalar', $topCategories, 'tag'],
        ] as [$cardTitle, $rows, $cardIcon])
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title flex items-center gap-2">
                        <x-icon :name="$cardIcon" class="h-4 w-4 text-ink-400"/>
                        {{ $cardTitle }}
                    </h2>
                </div>
                @if(empty($rows))
                    <x-ui.empty :icon="$cardIcon" title="Ma'lumot yo'q"/>
                @else
                    @php $best = max(1, (float) $rows[0]->revenue); @endphp
                    <div class="divide-y divide-ink-100">
                        @foreach($rows as $index => $row)
                            <div class="px-5 py-3">
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="flex min-w-0 items-baseline gap-2">
                                        <span class="tabular text-xs font-bold text-ink-400">{{ $index + 1 }}</span>
                                        <span class="truncate text-sm font-semibold text-ink-900">{{ $row->name }}</span>
                                    </span>
                                    <span class="tabular shrink-0 text-sm font-bold text-ink-900">
                                        {{ number_format((float) $row->revenue, 0, ',', ' ') }}
                                    </span>
                                </div>
                                <div class="mt-1.5 flex items-center gap-2">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-ink-100">
                                        <div class="h-full rounded-full bg-brand-500"
                                             style="width: {{ round((float) $row->revenue / $best * 100) }}%"></div>
                                    </div>
                                    <span class="tabular w-16 text-right text-xs text-ink-500">
                                        {{ (int) $row->quantity }} dona
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
