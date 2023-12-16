<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class EmployeePerformanceChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static string $chartId = 'employeePerformanceChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'أعداد المهام لكل موظف مصنفة حسب تسليمها قبل، في أو بعد الوقت المحدد لها';

    protected int | string | array $columnSpan = 'full'; 

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $employees = User::query()
            ->when(auth()->user()->job_id !== null, fn ($query) => $query
                ->whereBelongsTo(auth()->user(), 'manager')
                ->orWhereIn('manager_id', User::whereBelongsTo(auth()->user(), 'manager')->get('id')->all()),
            )
            ->with('tasks')
            ->get();

        $names = $employees->pluck('name')->toArray();
        $early = $employees->map(fn ($employee) => $employee->tasks->filter(fn ($task) => $task->delivery_date && Carbon::parse($task->delivery_date)->isBefore($task->end_date))->count())->toArray();
        $onTime = $employees->map(fn ($employee) => $employee->tasks->filter(fn ($task) => $task->delivery_date && Carbon::parse($task->delivery_date)->isSameDay($task->end_date))->count())->toArray();
        $late = $employees->map(fn ($employee) => $employee->tasks->filter(fn ($task) => $task->delivery_date && Carbon::parse($task->delivery_date)->isAfter($task->end_date))->count())->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'المهام المسلمة قبل وقتها',
                    'data' => $early
                ],
                [
                    'name' => 'المهام المسلمة في وقتها',
                    'data' => $onTime
                ],
                [
                    'name' => 'المهام المسلمة بعد وقتها',
                    'data' => $late
                ],
            ],
            'xaxis' => [
                'categories' => $names,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#0ea5e9', '#22c55e', '#ef4444'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'verical' => true,
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->job_id === null;
    }
}
