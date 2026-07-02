<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Vote;

class VoteStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public ?int $pollId = null;

    protected function getStats(): array
    {
        $query = Vote::query();
        if(!auth()->user()->hasRole('super_admin')){
            $query->whereHas('poll', function ($query) {
                $query->where('user_id', auth()->user()->id);
            });
        }
        if ($this->pollId) {
            $query->where('poll_id', $this->pollId);
        }
        $total        = $query->count();
        $todayVotes   = (clone $query)->whereDate('created_at', today())->count();
        $weeklyVotes  = (clone $query)->where('created_at', '>=', now()->subWeek())->count();
        $monthlyVotes = (clone $query)->where('created_at', '>=', now()->subMonth())->count();

        return [
            Stat::make('Total votes', number_format($total))
                ->description("+{$todayVotes} today")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Weekly votes', number_format($weeklyVotes))
                ->description('Past 7 days')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Monthly votes', number_format($monthlyVotes))
                ->description('Past 30 days')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
        ];
    }
}